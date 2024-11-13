<?php

namespace App\Http\Resources;

use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortalOuterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array

    {
        $client_id =  $this->getClientId();
        $pay_load = [
            'key' => $client_id
        ];
        $access = $this->encryptData($pay_load, $this->domain);
        $resultsmarts = [];
        foreach ($this->smarts as $smrt) {
            $smart = new SmartResource($smrt);
            array_push($resultsmarts, $smart);
        }

        $bitrixLists = [];
        foreach ($this->lists as $list) {
            $bitrixList = new BitrixlistResource($list);
            array_push($bitrixLists, $bitrixList);
        }

        return [
            'id' => $this->id,
            'departament' => $this->getSalesDepartamentId(),
            // 'bitrixList' => $this->getSalesBitrixListId(),
            'lists' => $bitrixLists,
            'calling_tasks_group' => $this->getSalesCallingGroupId(),
            // 'bitrixSmart' => $this->getSalesSmart(),
            'deal' => $this->deal(),
            'smarts' => $resultsmarts,
            // 'smart' => $this->getSalesSmart(),
            // 'deals' => $this->deals,
            'rpas' => $this->rpas,
            'company' => $this->company(),
            'lead' => $this->lead(),
            // 'domain' => $this->domain,
            // 'client_id' => $client_id,
            'access_key' =>  $access,
            // 'measures' =>  $this->measures,
        ];
    }

    // protected function encryptData($data, $encryptionKey)
    // {
    //     $cipher = 'aes-256-cbc';
    //     $iv = random_bytes(openssl_cipher_iv_length($cipher)); // Генерация случайного IV
    //     $encrypted = openssl_encrypt($data, $cipher, $encryptionKey, 0, $iv);

    //     // Кодируем результат в base64, чтобы передать вместе с IV
    //     return base64_encode($iv . $encrypted);
    // }

    protected function encryptData($payload, $secretKey) {
        $token = JWT::encode($payload, $secretKey, 'HS256'); // Создание токена с алгоритмом HS256
        return $token;
    }
    
   
    
}
