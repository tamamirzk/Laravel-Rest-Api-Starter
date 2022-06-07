<?php
namespace App\Lib;
use App\Models\ConfigMarketplace;

class Shopee {
    public function __construct()
    {
        $config = ConfigMarketplace::where('marketplace_id', 2)->first();
        $this->marketplace_id = 1;
        $this->url_api = $config->marketplace_api_auth;
        $this->path_api = $config->path_api;
        $this->partner_id = $config->app_id;
        $this->partner_key = $config->api_key;
    }

	public function refreshToken($userData=null) {
        if($userData) {
            $times = time();
            $path = "auth/access_token/get";
            $url = $this->generateUrlRequestAuth($times, $path);
                        
            $model['partner_id'] = intval($this->partner_id);
            $model['shop_id'] = intval($userData['shop_id']);
            $model['refresh_token'] = $userData['refresh_token'];
            
            $response = json_decode($this->sendHttpRequest($url, json_encode($model), "POST"));
            return $response;
            
            // if($response->error) {
            //     $res = array(
            //         'user_id' => $data['user_id'],
            //         'shop_id' => $shop_id,
            //         'marketplace_id' => $this->marketplace_id
            //     );
            //     return [ "message"=> 'error', "data" => null ];
            // } else {
            //     $access_token = $response->access_token;
            //     $refresh_token = $response->refresh_token;
            //     $expire_in = $response->expire_in;
            //     $expired_at = date("Y-m-d H:i:s", strtotime('+1 days', time()));
                
            //     $res = array(
            //         'access_token' => $access_token,
            //         'refresh_token' => $refresh_token,
            //         'expired_in' => $expire_in,
            //         'expired_at' => $expired_at,
            //         'modified_date' => date('Y-m-d H:i:s')
            //     );
            //     return [ "message" => 'success', "data" => null ];
            // }
            // return [
            //     "marketplace_id" => $this->marketplace_id,
            //     "url_api" => $this->url_api
            // ];
        }else{
            return "error";
        }
        
    }

    public function sendHttpRequest($url, $body, $method) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function generateUrlRequestAuth($times, $path, $redirect_url = "") {
        $data['marketplace_id'] = $this->marketplace_id;
        $data['partner_id'] = $this->partner_id;
        $data['partner_key'] = $this->partner_key;
        $data['marketplace_api_auth'] = $this->url_api;
        $data['path_api'] = $this->path_api;

        $host = $data['marketplace_api_auth'];
        $partner_id = $data['partner_id'];
        $partner_key = $data['partner_key'];
        $path_api = $data['path_api'];

        $full_path_api = $path_api . $path;
        $base_string = $partner_id . $full_path_api . $times;
        $sign = hash_hmac('sha256', $base_string, $partner_key);

        $url = $host . $full_path_api . "?timestamp=" . $times . "&partner_id=" . $partner_id . "&sign=" . $sign;

        if (!empty($redirect_url)) {
            $url += $url . "&redirect=" . $redirect_url;
        }
        return $url;
    }
}