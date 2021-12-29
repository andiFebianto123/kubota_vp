<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryStatus;
use App\Models\GeneralMessage;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Helpers\Constant;

class DashboardController extends Controller
{
    private function countPurchaseOrder(){
        if(Constant::getRole() == 'Admin PTKI'){
            return PurchaseOrder::count();
        }else{
            return PurchaseOrder::where('vend_num', backpack_user()->vendor->vend_num)->count();
        }
    }
    private function countPurchaseOrderLine(){
        if(Constant::getRole() == 'Admin PTKI'){
            return PurchaseOrderLine::where('read_at', null)
            ->where('accept_flag', 0)
            ->count();
        }else{
            return PurchaseOrderLine::whereRaw('po_num in(SELECT po_num FROM po WHERE vend_num = ?)', [backpack_user()->vendor->vend_num])
            ->where('read_at', null)
            ->where('accept_flag', 0)
            ->count();
        }
    }
    private function countDelivery(){
        if(Constant::getRole() == 'Admin PTKI'){
            return Delivery::count();
        }else{
            return Delivery::whereRaw('po_num in(SELECT po_num FROM po WHERE vend_num = ?)', [backpack_user()->vendor->vend_num])
            ->count();
        }
    }
    private function countDeliveryStatus(){
        if(Constant::getRole() == 'Admin PTKI'){
            return DeliveryStatus::count();
        }else{
            return DeliveryStatus::whereRaw('po_num in(SELECT po_num FROM po WHERE vend_num = ?)', [backpack_user()->vendor->vend_num])
            ->count();
        }
    }
    public function index()
    {
        $general_message_help = GeneralMessage::where('category', 'help')->get();
        $general_message_info = GeneralMessage::where('category', 'information')->get();
        $count_po_all = $this->countPurchaseOrder();
        $count_po_line_unreads = $this->countPurchaseOrderLine();
        $count_delivery = $this->countDelivery();
        $count_delivery_status = $this->countDeliveryStatus();

        $data['count_delivery_status'] = $count_delivery_status;
        $data['count_delivery'] = $count_delivery;
        $data['count_po_all'] = $count_po_all;
        $data['count_po_line_unreads'] = $count_po_line_unreads;
        $data['general_message_help'] = $general_message_help;
        $data['general_message_info'] = $general_message_info;

        if(!Constant::checkPermission('Read dashboard')){
            abort(403);
        }
        return view('vendor.backpack.base.dashboard', $data);
        // return $this->justTest();
    }

    private function justTest()
    {
        if (!extension_loaded('imagick')){
            return 'imagick not installed';
        }
    }
}