<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIpAndApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Допустимые IP-адреса
        // $allowedIps = explode(',', env('ALLOWED_ORIGINS_IPS'));

        // // Получаем IP-адрес клиента
        // $clientIp = $request->ip();

        // // Проверка IP-адреса
        // if (!in_array($clientIp, $allowedIps)) {
        //     return response()->json(['error' => 'Unauthorized IP'], 403);
        // }

        // Проверка API-ключа (API-ключ передаётся в заголовке "api-key")
        $apiKey = $request->header('X-OUTER-API-KEY');
        $validApiKey = env('API_OUTER_KEY');
        if ($apiKey !== $validApiKey) {
            return response()->json(['error' => 'Invalid API Key'], 403);
        }

        // Если всё ок, пропускаем запрос дальше
        
        return $next($request);
    }
}
