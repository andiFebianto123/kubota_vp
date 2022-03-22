<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryStatus;
use App\Models\GeneralMessage;
use App\Models\PurchaseOrder;
use App\Models\Comment;
use App\Models\PurchaseOrderLine;
use App\Helpers\Constant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if(backpack_user()->last_update_password === NULL){
            return redirect(url('admin/edit-account-info'));
        }

        if(!Constant::checkPermission('Read dashboard')){
            abort(403);
        }

        $generalMessageHelp = GeneralMessage::where('category', 'help')->get();
        $generalMessageInfo = GeneralMessage::where('category', 'information')->get();
        $countPoAll = $this->countPurchaseOrder();
        $countPoLineUnreads = $this->countPurchaseOrderLineUnread();
        $countDelivery = $this->countDelivery();
        $countDeliveryStatus = $this->countDeliveryStatus();
        $user = User::where('id', backpack_user()->id);
        $user->select(DB::raw("datediff(current_date(), DATE(last_update_password)) as selisih_pertahun"));
        
        $listDsUnRead = [];
        $unReadComments = Comment::where('status',1)->groupBy('tax_invoice_id')->orderBy('created_at','Desc')->get();
        foreach($unReadComments as $comment){
            $deliveryStatusData =  DeliveryStatus::where('id',$comment['tax_invoice_id'])->select('ds_num', 'ds_line')->first();
            if($deliveryStatusData != null){
                $listDsUnRead[] = [
                    'dsNumber' => $deliveryStatusData['ds_num'],
                    'dsLine' => $deliveryStatusData['ds_line']
                ];
            }
        }


        
        $count = [
            'delivery' => $countDelivery,
            'delivery_status' => $countDeliveryStatus,
            'po_all' => $countPoAll,
            'po_line_unread' => $countPoLineUnreads,
        ];

        $generalMessage = [
            'help' => $generalMessageHelp,
            'info' => $generalMessageInfo,
        ];

        $data['count'] = $count;
        $data['generalMessage'] = $generalMessage;
        $data['list_unread_comment'] = $listDsUnRead;
        $data['user_check_password_range'] = $user->get()->first();

        return view('vendor.backpack.base.dashboard', $data);
    }


    private function countPurchaseOrder(){
        if(Constant::getRole() == 'Admin PTKI'){
            return PurchaseOrder::count();
        }else{
            return PurchaseOrder::where('vend_num', backpack_user()->vendor->vend_num)->count();
        }
    }


    private function countPurchaseOrderLineUnread(){
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

}