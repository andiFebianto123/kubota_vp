<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryStatus;
use App\Models\GeneralMessage;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Helpers\Constant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
        if(backpack_user()->last_update_password === NULL){
            return redirect(url('admin/edit-account-info'));
        }

        $generalMessageHelp = GeneralMessage::where('category', 'help')->get();
        $generalMessageInfo = GeneralMessage::where('category', 'information')->get();
        $countPoAll = $this->countPurchaseOrder();
        $countPoLineUnreads = $this->countPurchaseOrderLine();
        $count_delivery = $this->countDelivery();
        $count_delivery_status = $this->countDeliveryStatus();
        $user = User::where('id', backpack_user()->id);
        $user->select(DB::raw("datediff(current_date(), DATE(last_update_password)) as selisih_pertahun"));

        $data['count_delivery_status'] = $count_delivery_status;
        $data['count_delivery'] = $count_delivery;
        $data['count_po_all'] = $countPoAll;
        $data['count_po_line_unreads'] = $countPoLineUnreads;
        $data['general_message_help'] = $generalMessageHelp;
        $data['general_message_info'] = $generalMessageInfo;
        $data['user_check_password_range'] = $user->get()->first();

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