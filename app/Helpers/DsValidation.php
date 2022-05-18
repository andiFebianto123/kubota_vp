<?php

namespace App\Helpers;

use App\Models\Delivery;
use App\Models\MaterialOuthouse;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\TempUploadDelivery;
use Illuminate\Support\Facades\DB;

class DsValidation
{

  public function currentMaxQty($args)
  {
    $poNum = $args['po_num'];
    $poLine = $args['po_line'];

    $qtyInitial = PurchaseOrderLine::where("po_num", $poNum)
      ->where("po_line", $poLine)
      ->orderBy('po_change', 'desc')
      ->first()
      ->order_qty;

    $realtimeDsQty = Delivery::where("po_num", $poNum)
      ->where("po_line", $poLine)
      ->whereIn("ds_type", ['00', '01', '02'])
      ->sum('shipped_qty');

    $realtimeQty = $qtyInitial - $realtimeDsQty;

    return [
      'datas'  => $realtimeQty,
      'mode'   => 'danger',
      'message' => 'Maksimum Qty ' . $realtimeQty
    ];
  }


  public function currentMaxQtyOuthouse($args)
  {
    $poNum = $args['po_num'];
    $poLine = $args['po_line'];

    $realtimeDsQty = MaterialOuthouse::where("po_num", $poNum)
      ->where("po_line", $poLine)
      ->get();

    $arrMinQty = [];
    $currentQty = 0;
    foreach ($realtimeDsQty as $key => $rdq) {
      $arrMinQty[] = ($rdq) ? $rdq->remaining_qty / $rdq->qty_per : 0;
    }
    if (sizeof($arrMinQty) > 0) {
      $currentQty = min($arrMinQty);
    }

    return [
      'datas'  => $currentQty,
      'mode'   => 'danger',
      'message' => 'Maksimum Qty ' . $currentQty
    ];
  }


  public function unfinishedPoLine($args)
  {
    $due_date = $args['due_date'];
    $poNum = $args['po_num'];
    $poLine = $args['po_line'];
    $filters = (isset($args['filters'])) ? $args['filters'] : [];

    $po = PurchaseOrder::where('po_num', $poNum)->first();
    $currentPoLine = PurchaseOrderLine::where('po_num', $poNum)->where('po_line', $poLine)->first();
    $oldPo = PurchaseOrderLine::join('po', 'po.po_num', 'po_line.po_num')
      ->where('po_line.status', 'O')
      ->where('po_line.outhouse_flag', 0)
      ->where('po_line.item', '=', $currentPoLine->item)
      ->whereNotNull('po_line.item')
      ->where('po.vend_num', '=', $po->vend_num)
      ->where('po.po_num', '<=', $currentPoLine->po_num)
      ->whereDate('po_line.due_date', '<=', date('Y-m-d', strtotime($due_date)))
      ->where($filters)
   //   ->orderBy('po_line.due_date', 'asc')
    //  ->orderBy('po_line.po_line', 'asc')
      ->orderBy('po_line.po_num', 'asc')
      ->get(['po_line.po_num', 'po_line.po_line', 'po_line.item', 'po_line.description', 'po_line.due_date', 'po_line.order_qty']);

    $arrOldPo = [];
    foreach ($oldPo as $key => $op) {
      $show = false;
      if ($op->total_shipped_qty < $op->order_qty) {
        $show = true;
      }
      if ($poNum == $op->po_num) {
        $show = true;
        if ($poLine <= $op->po_line) {
          $show = false;
        }
        if ($op->total_shipped_qty == $op->order_qty) {
          $show = false;
        }
      }

      if (trim($op->item) == "") {
        $show = false;
      }
      if ($show) {
        $arrOldPo[] = $op;
      }
    }
    $arrOldPo = collect($arrOldPo)->take(1);

    return [
      'datas'  => $arrOldPo,
      'mode'   => 'danger',
      'message' => 'Selesaikan terlebih dahulu PO yang lama!'
    ];
  }


  public function unfinishedPoLineMass($args)
  {
    $due_date = $args['due_date'];
    $poNum = $args['po_num'];
    $poLine = $args['po_line'];
    $filters = (isset($args['filters'])) ? $args['filters'] : [];

    $po = PurchaseOrder::where('po_num', $poNum)->first();
    $currentPoLine = PurchaseOrderLine::where('po_num', $poNum)->where('po_line', $poLine)->first();
    $oldPo = PurchaseOrderLine::join('po', 'po.po_num', 'po_line.po_num')
      ->where('po_line.status', 'O')
      ->where('po_line.outhouse_flag', 0)
      ->where('po_line.item', '=', $currentPoLine->item)
      ->whereNotNull('po_line.item')
      ->where('po.vend_num', '=', $po->vend_num)
      ->where('po.po_num', '<=', $currentPoLine->po_num)
      ->whereDate('po_line.due_date', '<=', date('Y-m-d', strtotime($due_date)))
      ->where($filters)
      // ->orderBy('po_line.due_date', 'asc')
      // ->orderBy('po_line.po_line', 'asc')
      ->orderBy('po_line.po_num', 'asc')
      ->get(['po_line.po_num', 'po_line.po_line', 'po_line.item', 'po_line.description', 'po_line.due_date', 'po_line.order_qty']);

    $arrOldPo = [];
    foreach ($oldPo as $key => $op) {
      $show = false;
      if ($op->total_shipped_qty < $op->order_qty) {
        $show = true;
        $tempPo = TempUploadDelivery::where('po_num', $op->po_num)->where('po_line', $op->po_line)->first();
        if (isset($tempPo)) {
          if ($tempPo->shipped_qty + $op->total_shipped_qty == $op->order_qty) {
            $show = false;
          }
        }
      }
      if ($poNum == $op->po_num) {
        $show = true;
        if ($poLine <= $op->po_line) {
          $show = false;
        }
        if ($op->total_shipped_qty == $op->order_qty) {
          $show = false;
        }
      }
      if (trim($op->item) == "") {
        $show = false;
      }

      if ($show) {
        $arrOldPo[] = $op;
      }
    }
    $arrOldPo = collect($arrOldPo)->take(1);

    return [
      'datas'  => $arrOldPo,
      'mode'   => 'danger',
      'message' => 'Selesaikan terlebih dahulu PO yang lama!'
    ];
  }
}