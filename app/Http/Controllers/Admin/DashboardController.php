<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeneralMessage;

class DashboardController extends Controller
{
    public function index()
    {
        $general_message_help = GeneralMessage::where('category', 'help')->get();
        $general_message_info = GeneralMessage::where('category', 'information')->get();
        $data['general_message_help'] = $general_message_help;
        $data['general_message_info'] = $general_message_info;

        return view('vendor.backpack.base.dashboard', $data);

    }

}