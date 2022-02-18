<?php
namespace App\Helpers;

use App\Models\Delivery;
use App\Models\MaterialOuthouse;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use Illuminate\Support\Facades\DB;

class DsValidation
{

  public function currentMaxQty($args){
    $po_num = $args['po_num'];
    $po_line = $args['po_line'];
    $order_qty = $args['order_qty'];

    $qty_initial = PurchaseOrderLine::where("po_num", $po_num)
                   ->where("po_line", $po_line)
                   ->sum('order_qty');

    $realtime_ds_qty = Delivery::where("po_num", $po_num)
                      ->where("po_line", $po_line)
                      ->sum('shipped_qty');

    $realtime_qty = $qty_initial - $realtime_ds_qty;
    $current_qty = ($order_qty < $realtime_qty)? $realtime_qty -  $order_qty : $realtime_qty;
    
    return [
      'datas'  => $current_qty,
      'mode'   => 'danger',
      'message' => 'Maksimum Qty '. $realtime_ds_qty
    ];
  }

  public function currentMaxQtyOuthouse($args){
    $po_num = $args['po_num'];
    $po_line = $args['po_line'];
    $order_qty = $args['order_qty'];

    $realtime_ds_qty = MaterialOuthouse::where("po_num", $po_num)
                      ->where("po_line", $po_line)
                      ->get();

    $arr_min_qty = [0];
    foreach ($realtime_ds_qty as $key => $rdq) {
      $arr_min_qty[] = ($rdq)? $rdq->remaining_qty/$rdq->qty_per : 0;
    }
    $current_qty = min($arr_min_qty); 
    
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

    $po = PurchaseOrder::where('po_num', $po_num)->first();
    
    $old_po = PurchaseOrderLine::join('po', 'po.po_num', 'po_line.po_num')
                  ->where('po_line.status', 'O')
                  ->where('po_line.outhouse_flag', 0)
                  ->where('po_line.po_num', '<=', $po_num)
                  ->where('po.vend_num', '<=', $po->vend_num)
                  ->whereDate('po_line.due_date', '<=', date('Y-m-d',strtotime($due_date)))
                  ->where($filters)         
                  ->orderBy('po_line.po_line','asc')
                  ->orderBy('po_line.po_num','asc')
                  ->get(['po_line.po_num', 'po_line.po_line', 'po_line.item', 'po_line.description', 'po_line.due_date', 'po_line.order_qty']);

      $arr_old_po = [];
      foreach ($old_po as $key => $op) {
        $show = false;
        if ($op->total_shipped_qty < $op->order_qty) {
          $show = true;
        }
        if ($po_num == $op->po_num) {
          if ($po_line <= $op->po_line) {
            $show = false;
          }
        }
        
        if ($show) {
          $arr_old_po[] = $op;
        }
      }
      $arr_old_po = collect($arr_old_po)->sortBy('num_line')->take(1);

      return [
        'datas'  => $arr_old_po,
        'mode'   => 'danger',
        'message' => 'Selesaikan terlebih dahulu PO yang lama!'
      ];
  }

  
}