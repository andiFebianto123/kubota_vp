<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryStatus;
use App\Models\GeneralMessage;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;

class DashboardController extends Controller
{
    public function index()
    {
        $general_message_help = GeneralMessage::where('category', 'help')->get();
        $general_message_info = GeneralMessage::where('category', 'information')->get();
        $count_po_all = PurchaseOrder::count();
        $count_po_line_unreads = PurchaseOrderLine::where('read_at', null)
                                ->where('accept_flag', 0)
                                ->count();
        $count_delivery = Delivery::count();
        $count_delivery_status = DeliveryStatus::count();


        $data['count_delivery_status'] = $count_delivery_status;
        $data['count_delivery'] = $count_delivery;
        $data['count_po_all'] = $count_po_all;
        $data['count_po_line_unreads'] = $count_po_line_unreads;
        $data['general_message_help'] = $general_message_help;
        $data['general_message_info'] = $general_message_info;

        return view('vendor.backpack.base.dashboard', $data);

    }

}