<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeneralMessage;

class DashboardController extends Controller
{
    public function index()
    {
        $general_messages = GeneralMessage::get();
        $data['general_messages'] = $general_messages;

        return view('vendor.backpack.base.dashboard', $data);

    }

}