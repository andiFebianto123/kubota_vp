<?php
namespace App\Helpers;

use App\Models\Delivery;
use App\Models\PurchaseOrderLine;

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
    $filters = (isset($args['filters'])) ? $args['filters'] : [];
    $old_po = PurchaseOrderLine::leftJoin('delivery', function($join) {
                      $join->on('po_line.po_num', '=', 'delivery.po_num')
                          ->on('po_line.po_line', '=', 'delivery.po_line');
                  })
                  ->where('status', 'O')
                  ->where('accept_flag', 1)
                  ->whereDate('po_line.due_date', '<', date('Y-m-d',strtotime($due_date)))
                  ->where($filters)
                  ->orderBy('po_line.due_date','desc')
                  ->selectRaw('po_line.po_num, po_line.po_line, po_line.item, po_line.description, po_line.due_date, ROUND(sum(shipped_qty), 2) as total_shipped_qty, po_line.order_qty')
                  ->paginate(100);

      return [
        'datas'  => $old_po,
        'mode'   => 'danger',
        'message' => 'Selesaikan terlebih dahulu PO yang lama!'
      ];
  }

  
}