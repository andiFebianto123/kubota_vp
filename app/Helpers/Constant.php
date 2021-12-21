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

  public function codeDs($po_num, $po_line, $delivery_date){
    $code = "";
    switch (backpack_auth()->user()->roles->pluck('name')->first()) {
        case 'Admin PTKI':
            $code = "02";
            break;
        // case 'vendor':
        //     $code = "00";
        //     break;
        default:
            $code = "00";
            break;
    }
    $po = PurchaseOrder::where('po_num', $po_num)->first();
    $ds_num_mid = $po->vend_num.date('ymd', strtotime($delivery_date));
    $ds = Delivery::where('po_num', $po_num)->where('po_line', $po_line)->orderBy('id', 'desc')->first();
    $ds_line = (isset($ds))?$ds->ds_line+1:1;

    $ds_num['single'] = $ds_num_mid.$code;
    $ds_num['group'] = 'GD'.$ds_num_mid;
    $ds_num['line'] = $ds_line;

    return $ds_num;
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

  
}