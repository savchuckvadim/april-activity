<?php

namespace Antsupovsa\Bitrix24;

use App\Models\Portal;
use Bitrix24\Exceptions\Bitrix24TokenIsInvalidException;
use Bitrix24api\Facades\Bitrix24;
use GuzzleHttp\Handler\StreamHandler;
use Illuminate\Database\QueryException;
use Illuminate\Log\Logger;
use Illuminate\Support\ServiceProvider;
use Vipblogger\LaravelBitrix24\Bitrix;

class Bitrix24ServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerBitrix24Service();

        if ($this->app->runningInConsole()) {
            $this->registerResources();
        }
    }

    /**
     * Register currency provider.
     *
     * @return void
     */
    public function registerBitrix24Service()
    {
        $this->app->singleton('bitrix24', function ($app) {
            return new Bitrix24($app->config->get('bitrix24', []));
        });
    }

    /**
     * Register currency resources.
     *
     * @return void
     */
    public function registerResources()
    {
        if ($this->isLumen() === false) {
            $this->publishes([
                __DIR__ . '/../config/bitrix24.php' => config_path('bitrix24.php'),
            ], 'config');
        }
    }
    public function boot(Bitrix $obB24App)
    {
        $this->publishes([
            __DIR__ . '/../config/laravel-bitrix24.php' => config_path('laravel-bitrix24.php'),
        ]);

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Bitrix settings
        // create a log channel
        // $log = new Logger('laravel-bitrix24');
        // $log->pushHandler(new StreamHandler(storage_path() . '/logs/laravel-bitrix24.log', Logger::DEBUG));

        $arParams = config('laravel-bitrix24');

        if (!$arParams) {
            return false;
        }




        $domain = request()->get('DOMAIN');  // Получаем идентификатор портала из запроса.
        $portalConfig = $this->getPortalConfig($domain);  // Загружаем конфигурацию для данного портала.

        $obB24App->setApplicationScope($portalConfig['B24_APPLICATION_SCOPE']);
        $obB24App->setApplicationId($portalConfig['B24_APPLICATION_ID']);
        $obB24App->setApplicationSecret($portalConfig['B24_APPLICATION_SECRET']);
        $obB24App->setDomain($portalConfig['DOMAIN']);
        $obB24App->setRedirectUri(url($portalConfig['REDIRECT_URI']));

        // $obB24App->setApplicationScope($arParams['B24_APPLICATION_SCOPE']);
        // $obB24App->setApplicationId($arParams['B24_APPLICATION_ID']);
        // $obB24App->setApplicationSecret($arParams['B24_APPLICATION_SECRET']);
        // $obB24App->setDomain($arParams['DOMAIN']);
        // $obB24App->setRedirectUri(url($arParams['REDIRECT_URI']));

        try {
            $refreshToken = settings('b24_refresh_token');

            if ($refreshToken) {
                // access key expired
                if (time() >= (settings('b24_expires', 0) - 300)) {
                    $obB24App->setRefreshToken($refreshToken);
                    $result = $obB24App->getNewAccessToken();
                    $obB24App->setAccessToken($result['access_token']);

                    // save new settings
                    Bitrix::saveSettings($result);
                }

                // from DB
                $obB24App->setMemberId(settings('b24_member_id'));
                $obB24App->setAccessToken(settings('b24_access_token'));
            }
        } catch (QueryException $e) {
            echo 'QueryException settings table';
        } catch (Bitrix24TokenIsInvalidException $e) {
            echo 'Bitrix24TokenIsInvalidException!';
        }
    }

    protected function getPortalConfig($domain)
    {
        // Здесь вы можете выполнить запрос к базе данных или другому источнику,
        // чтобы получить параметры для данного портала.
        $portal =  Portal::where('domain', $domain)->first();
        if ($portal) {

            return [
                'B24_APPLICATION_SCOPE' => 'task, user, crm',
                'B24_APPLICATION_ID' => $portal->clientId,
                'B24_APPLICATION_SECRET' =>  $portal->secret,
                'DOMAIN' => $portal->domain,
                'REDIRECT_URI' => '/',
                // 'C_REST_CLIENT_ID' => $portal->clientId,
                // 'C_REST_CLIENT_SECRET' => $portal->secret,
                // 'C_REST_WEB_HOOK_URL' => $portal->hook,
            ];
        } else {
            return null;
        }
    }



    /**
     * Check if package is running under Lumen app
     *
     * @return bool
     */
    protected function isLumen()
    {
        return str_contains($this->app->version(), 'Lumen') === true;
    }
}
