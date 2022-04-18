<?php
namespace App\Helpers;

use App\Models\Delivery;
use App\Models\PurchaseOrder;
use Carbon\Carbon;

class Constant
{
 
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

  public function codeDs($poNum, $poLine, $delivery_date){
    $code = "";
    $user = backpack_auth()->user()->roles->pluck('name')->first();
    if(strpos(strtoupper($user), 'PTKI')){
      $code = '02';
    }else{
      $code = '00';
    }
    // switch (backpack_auth()->user()->roles->pluck('name')->first()) {
    //     case 'Admin PTKI':
    //         $code = "02";
    //         break;
    //     case 'User PTKI':
    //         $code = "02";
    //         break;
    //     default:
    //         $code = "00";
    //         break;
    // }

    $po = PurchaseOrder::where('po_num', $poNum)->first();
    $dsNumMid = $po->vend_num.date('ymd', strtotime($delivery_date));
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
}