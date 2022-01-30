<?php
namespace App\Helpers;

use App\Models\Delivery;
use App\Models\PurchaseOrderLine;
use Illuminate\Support\Facades\DB;

class DsValidation
{

  public function currentMaxQty($args){
    $po_num = $args['po_num'];
    $po_line = $args['po_line'];
    $order_qty = $args['order_qty'];

    $realtime_ds_qty = Delivery::where("po_num", $po_num)->where("po_line", $po_line)->sum('shipped_qty');
    $current_qty = ($order_qty < $realtime_ds_qty)? 0 : $order_qty -  $realtime_ds_qty;
    
    return [
      'datas'  => $current_qty,
      'mode'   => 'warning',
      'message' => 'Maksimum Qty '. $current_qty
    ];
  }

  public function unfinishedPoLine($args)
  {
    
    $due_date = $args['due_date'];
    // $po_num = $args['po_num'];
    $filters = (isset($args['filters'])) ? $args['filters'] : [];

    // $query = "select * from "
    // $old_po = DB::statement('your raw query here')
    
    $old_po = PurchaseOrderLine::where('status', 'O')
                  ->where('accept_flag', 1)
                  ->whereDate('due_date', '<=', date('Y-m-d',strtotime($due_date)))
                  ->where($filters)                                         
                  ->orderBy('po_line.po_line','asc')
                  // ->orderBy('po_line.due_date','asc')
                  ->selectRaw("po_num, po_line, item, description, due_date, order_qty, 'total_shipped_qty'")
                  // ->take(1)
                  ->get();

      $arr_old_po = [];
      foreach ($old_po as $key => $op) {
        if ($op->total_shipped_qty < $op->order_qty) {
          $arr_old_po[] = $op;
        }
      }

      return [
        'datas'  => $arr_old_po,
        'mode'   => 'danger',
        'message' => 'Selesaikan terlebih dahulu PO yang lama!'
      ];
  }

  
}