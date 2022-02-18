<?php
namespace App\Helpers;

use App\Models\Delivery;
use App\Models\MaterialOuthouse;
use App\Models\PurchaseOrderLine;
use Illuminate\Support\Facades\DB;

class DsValidation
{

  public function currentMaxQty($args){
    $po_num = $args['po_num'];
    $po_line = $args['po_line'];
    $order_qty = $args['order_qty'];

    $realtime_ds_qty = Delivery::where("po_num", $po_num)
                      ->where("po_line", $po_line)
                      // ->where("outhouse_flag", 0)
                      ->sum('shipped_qty');
    $current_qty = ($order_qty < $realtime_ds_qty)? 0 : $order_qty -  $realtime_ds_qty;
    
    return [
      'datas'  => $current_qty,
      'mode'   => 'warning',
      'message' => 'Maksimum Qty '. $current_qty
    ];
  }

  public function currentMaxQtyOuthouse($args){
    $po_num = $args['po_num'];
    $po_line = $args['po_line'];
    $order_qty = $args['order_qty'];

    $realtime_ds_qty = MaterialOuthouse::where("po_num", $po_num)
                      ->where("po_line", $po_line)
                      ->orderBy("qty_per", 'desc')
                      ->first();
    $current_qty = ($realtime_ds_qty)? $realtime_ds_qty->lot_qty/$realtime_ds_qty->qty_per : 0;
    
    return [
      'datas'  => $current_qty,
      'mode'   => 'danger',
      'message' => 'Maksimum Qty '. $current_qty
    ];
  }

  public function unfinishedPoLine($args)
  {
    
    $due_date = $args['due_date'];
    $po_num = $args['po_num'];
    $po_line = $args['po_line'];
    $filters = (isset($args['filters'])) ? $args['filters'] : [];

    // $query = "select * from "
    // $old_po = DB::statement('your raw query here')
    
    $old_po = PurchaseOrderLine::where('status', 'O')
                  ->where('outhouse_flag', 0)
                  // ->where('accept_flag', 1)
                  ->where('po_num', '<=', $po_num)
                  ->where('po_line', '<=', $po_line)
                  ->whereDate('due_date', '<=', date('Y-m-d',strtotime($due_date)))
                  ->where($filters)                                         
                  ->orderBy('po_line','asc')
                  ->orderBy('po_num','asc')
                  // ->selectRaw("po_num, po_line, item, description, due_date, order_qty, 'total_shipped_qty'")
                  // ->take(1)
                  ->get(['po_num', 'po_line', 'item', 'description', 'due_date', 'order_qty']);

      $arr_old_po = [];
      foreach ($old_po as $key => $op) {
        $show = false;
        if ($op->total_shipped_qty < $op->order_qty) {
          $show = true;
        }
        if ($po_num == $op->po_num && $po_line == $op->po_line) {
          $show = false;
        }
        
        if ($show) {
          $arr_old_po[] = $op;
        }
      }
      $arr_old_po = collect($arr_old_po)->sortBy('po_num')->take(1);

      return [
        'datas'  => $arr_old_po,
        'mode'   => 'danger',
        'message' => 'Selesaikan terlebih dahulu PO yang lama!'
      ];
  }

  
}