<?php
namespace App\Lib;
use App\Models\ConfigMarketplace;

class Tokopedia {
    public function __construct()
    {
        $config = ConfigMarketplace::where('marketplace_id', 1)->first();
        $this->marketplace_id = 1;
        $this->url_api_auth = $config->marketplace_api_auth;
        $this->url_api = $config->marketplace_api;
        $this->client_id = $config->client_id;
        $this->client_secret = $config->client_secret;
        $this->app_id = $config->app_id;
        
        // if($data['user_type']==2) {
        //     $get_user_marketplace = $CI->general_model->getTable('user_marketplace',array('user_id'=>$this->user_id,'user_guid'=>$this->user_guid,'marketplace_id'=>$this->marketplace_id),'user_id','ASC');
        // } else {
        //     $get_user_id = $CI->general_model->getTable('seller',array('seller_id'=>$this->user_id),'seller_id','ASC');
        //     foreach($get_user_id->result() AS $item) {
        //         $this->user_id = $item->user_id;
        //         $this->user_guid = $item->user_guid;
        //     }
        //     $get_user_marketplace = $CI->general_model->getTable('user_marketplace',array('user_id'=>$this->user_id,'marketplace_id'=>$this->marketplace_id),'user_id','ASC');
        // }
        // if($get_user_marketplace->num_rows()>0) {
        //     foreach($get_user_marketplace->result() AS $item) {
        //         $this->shop_id = $item->shop_id;
        //         $this->etalase_id = $item->etalase_id;
        //     }
        // }else{
        //     $this->shop_id = '';
        //     $this->etalase_id = '';
        // }
    }

	public function test() {
        return [
            "marketplace_id" => $this->marketplace_id,
            "url_api_auth" => $this->url_api_auth
        ];
    }
}