<?php
namespace App\Helpers;

use App\Models\Delivery;
use App\Models\PurchaseOrder;
use Carbon\Carbon;

class Constant
{
 
  public function statusARO(){
    $status = [];

    $status[0] = ["text" => "OPEN", "accept_flag" => 0];
    $status[1] = ["text" => "ACCEPT", "accept_flag" => 1];
    $status[2] = ["text" => "REJECT", "accept_flag" => 2];

    return $status;
  }

  public function statusOFC(){

    $status = [];   
    foreach(range('A','Z') as $alpha){
        $status[$alpha] = ['text' => 'Ordered', 'color' => ''];
    }

    $status['O'] = ['text' => 'Ordered', 'color' => ''];
    $status['F'] = ['text' => 'Filled', 'color' => 'text-primary'];
    $status['C'] = ['text' => 'Complete', 'color' => 'text-success'];

    return $status;
  }

  public function arrStatus(){

    $status = [
      ['text' => 'Ordered', 'value' => 'O'],
      ['text' => 'Filled', 'value' => 'F'],
      ['text' => 'Complete', 'value' => 'C'],
    ];
    return $status;
  }

  public function codeDs($poNum, $deliveryDate, $category = 'general'){
    $code = '00';
    $role = strtoupper(Constant::getRole());
    if ($category == 'general') {
      if(strpos($role, 'PTKI')){
        $code = '01';
        if ($role == "ADMIN PTKI") {
          $code = '02';
        }
      }elseif (strpos($role, 'VENDOR')) {
        $code = '00';
      }
    }elseif ($category == 'return') {
      if(strpos($role, 'PTKI')){
        $code = '1P';
      }elseif (strpos($role, 'VENDOR')) {
        $code = '0P';
      }
    }elseif ($category == 'closed') {
      if(strpos($role, 'PTKI')){
        $code = 'R1';
      }elseif (strpos($role, 'VENDOR')) {
          $code = 'R0';
      }
    }

    $po = PurchaseOrder::where('po_num', $poNum)->first();
    $dsNumMid = $po->vend_num.date('ymd', strtotime($deliveryDate));
    $ds = Delivery::where('ds_num', $dsNumMid.$code)->orderBy('ds_line', 'desc')->first();
    $dsLine = (isset($ds))?$ds->ds_line+1:1;

    $dsNum['type'] = $code;
    $dsNum['single'] = $dsNumMid.$code;
    $dsNum['group'] = 'GD'.$dsNumMid;
    $dsNum['line'] = $dsLine;

    return $dsNum;
  }


  public static function getPrice($nominal){
   return number_format($nominal, 0, ',', '.');
  }


  public static function formatDateComment($date){
      $arrDate = explode(' ', $date);
      $created = Carbon::parse($arrDate[0]);
      $now = Carbon::now();
      $difference = ($created->diff($now)->days < 1)
          ? 'today'
          : $created->diffForHumans($now);
      if($difference == 'today'){
        $dateTime = Carbon::parse($date);
        return $difference.' '.$dateTime->format('H:i');
      }else{
        return Carbon::parse($date)->format('d M Y H:i');
      }
  }


  public static function checkPermission($permission){
    return backpack_user()->roles->first()->hasPermissionTo($permission);
  }

  
  public static function getRole(){
    return backpack_user()->roles->pluck('name')[0];
  }

  
  public function getUserIp()
  {
      if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
                $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
                $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
      }
      $client  = @$_SERVER['HTTP_CLIENT_IP'];
      $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
      $remote  = $_SERVER['REMOTE_ADDR'];

      if(filter_var($client, FILTER_VALIDATE_IP))
      {
        $ip = $client;
      } elseif(filter_var($forward, FILTER_VALIDATE_IP))
      {
          $ip = $forward;
      } else
      {
          $ip = $remote;
      }
      return $ip;
  }
  

  public static function getColumnHeaderDays($columnHeader, $dateKey, $date){  
    $collect = $columnHeader; 
    $filtered = $collect->where('key', $dateKey);
    $arrDate = collect($filtered->first()['data']);
    $search = $arrDate->search($date);

    return [
      'total' => $arrDate->count() - 1,
      'search' => $search
    ];
  }

  
  public static function getNameFromNumber($num) {
    $numeric = ($num - 1) % 26;
    $letter = chr(65 + $numeric);
    $num2 = intval(($num - 1) / 26);
    if ($num2 > 0) {
        return self::getNameFromNumber($num2) . $letter;
    } else {
        return $letter;
    }
  }


  public function emailHandler($emails = '', $mode = 'array'){
    $arrMails = [];
    $emails = str_replace(" ", "",str_replace(",", ";", $emails));
    $strMails = "";
    if (str_contains($emails, ";")) {
      foreach (explode(";",$emails) as $key => $email) {
        if ($email != "") {
          $arrMails[] = $email;
          $strMails .=  $email.";";
        }
      }
    }else{
        $arrMails = [$emails];
        $strMails = $emails;
    }

    $strMails = rtrim($strMails, ";");

    $mailFormat = [
      'array' => $arrMails,
      'string' => $strMails,
    ];

    return $mailFormat[$mode];
  }
}