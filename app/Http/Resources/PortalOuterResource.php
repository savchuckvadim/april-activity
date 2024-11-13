<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortalOuterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($domain): array

    {
        $client_id =  $this->getClientId();
        $access = $this->encryptData($client_id, $domain);
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
            'access_key' => $access,
            'client_id' => $client_id
            // 'contracts' =>  $this->contracts,
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

    protected function encryptData($plaintext, $key) {
        // Генерируем случайный вектор инициализации (IV)
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
    
        // Расширяем ключ до 32 байт
        $key = str_pad(substr($key, 0, 32), 32, " ");
    
        // Шифруем данные
        $ciphertext = openssl_encrypt($plaintext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    
        // Кодируем в Base64 и добавляем IV в начало
        return base64_encode($iv . $ciphertext);
    }
    
   
    
}
