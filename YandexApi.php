<?php
/**
* Description:       Библиотека дя взаимодействия с АПИ Яндекс ID.
* Version:           2.0
* Author:            Outcode
* Author URI:        https://outcode.ru/
* License:           GPL v2 or later
* License URI:       https://www.gnu.org/licenses/gpl-2.0.html
*/ 
 

class YandexApi{
  
  private $oauth_url = 'https://oauth.yandex.ru';
  private $login_url = 'https://login.yandex.ru/info?format=json';

  public function __construct(){    
    //throw new Exception('...'); 
  }

  /**
    * Запрашиваем данные пользователя
   *
  */  
  public function getUserInfo($oauth_token){

    if ($curl = curl_init()) {
      curl_setopt($curl, CURLOPT_URL, $this->login_url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Authorization:'.'oAuth '.$oauth_token
      ]);

      $out = curl_exec($curl);
      curl_close($curl);
      
      $result = json_decode($out);
      
      if(isset($result->default_email)){
        return $result;
      }
      
      return null;

    }else{
      throw new Exception('cURL - не установлен');
    }  
  }    

}