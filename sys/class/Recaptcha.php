<?php
class Recaptcha
{
   private $secret;

   public function __construct($secret)
   {
      $this->secret = $secret;
   }

   public function checkCode($code)
   {
      if (empty($code)) {
         return false;
      }

      $url = "https://www.google.com/recaptcha/api/siteverify?secret=$this->secret&response=$code";
      $response = file_get_contents($url);

      if (empty($response) || is_null($response)) {
         return false;
      }

      $json = json_decode($response);
      return $json->success;
   }
}
