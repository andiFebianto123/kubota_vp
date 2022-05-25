<?php

namespace App\Helpers;

use App\Models\DeliveryRepair;
use App\Models\DeliveryReturn;
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

    $queryCount = "SELECT 
    (pl.order_qty - 
       (IFNULL((
            SELECT sum(shipped_qty) 
                 from delivery dlv 
                 WHERE dlv.po_num = pl.po_num  
                 AND dlv.po_line = pl.po_line
                 AND dlv.ds_type IN ('00', '01', '02')
                 ),0)
        )
    ) as maximum_qty
    FROM 
    po_line pl
    where po_num = '".$poNum."' and po_line = '".$poLine."'
    GROUP by po_num, po_line, po_change";

    $selectMaxQty =  DB::select($queryCount);

    $realtimeQty = 0;
    if (sizeof($selectMaxQty) > 0) {
      $realtimeQty = $selectMaxQty[0]->maximum_qty;
    }

    return [
      'datas'  => round($realtimeQty,8),
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
      'datas'  => round($currentQty,8),
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
      ->whereRaw(DB::raw("po_line.po_change =
                                (
                                  select Max(pl.po_change)
                                  from po_line as pl 
                                  where pl.po_num = po_line.po_num
                                  and pl.po_line = po_line.po_line
                )"))
   //   ->orderBy('po_line.due_date', 'asc')
    //  ->orderBy('po_line.po_line', 'asc')
      ->orderBy('po_line.po_num', 'asc')
      ->groupBy('po_line.po_num', 'po_line.po_line', 'po_line.po_change')
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
      ->whereRaw(DB::raw("po_line.po_change =
                                (
                                  select Max(pl.po_change)
                                  from po_line as pl 
                                  where pl.po_num = po_line.po_num
                                  and pl.po_line = po_line.po_line
                )"))
      ->where($filters)
      // ->orderBy('po_line.due_date', 'asc')
      // ->orderBy('po_line.po_line', 'asc')
      ->orderBy('po_line.po_num', 'asc')
      ->groupBy('po_line.po_num', 'po_line.po_line', 'po_line.po_change')
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


  public function availableQtyReturn($dsNum, $dsLine)
  {
    $totalDeliveryCreated = DeliveryReturn::join('delivery as dlv', function($join){
          $join->on('dlv.ds_num', '=', 'delivery_return.ds_num');
          $join->on('dlv.ds_line', '=', 'delivery_return.ds_line');
      })
      ->where('ref_ds_num', $dsNum)
      ->where('ref_ds_line', $dsLine)
      ->whereIn('delivery_return.ds_type', ['0P', '1P'])
      ->sum('qty');

    $totalDeliveryClosed = DeliveryReturn::join('delivery as dlv', function($join){
        $join->on('dlv.ds_num', '=', 'delivery_return.ds_num');
        $join->on('dlv.ds_line', '=', 'delivery_return.ds_line');
    })
    ->where('ref_ds_num', $dsNum)
    ->where('ref_ds_line', $dsLine)
    ->whereIn('delivery_return.ds_type', ['R0', 'R1'])
    ->sum('qty');

    $totalDeliveryReturn = DeliveryRepair::where('ds_num_reject', $dsNum)
          ->where('ds_line_reject', $dsLine)
          ->where('repair_type', 'RETURN')
          ->sum('repair_qty');

    $availableQty = $totalDeliveryReturn - $totalDeliveryCreated - $totalDeliveryClosed;

    return round($availableQty, 8);
  }
}