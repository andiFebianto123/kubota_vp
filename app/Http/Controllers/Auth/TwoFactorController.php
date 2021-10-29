<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{

    public function index() 
    {
        if (request("t") && User::where("two_factor_url", request("t"))->where('id', backpack_auth()->user()->id)->exists()) {
            return view('vendor.backpack.base.auth.two-factor');
        }else{
            return redirect()->route("rectmedia.auth.login");
        }
    }

    public function update(Request $request) 
    {
        $two_factor_code = $request->two_factor_code;

        if (User::where("id", backpack_auth()->user()->id)
            ->where("two_factor_code", $two_factor_code)
            ->where("two_factor_expires_at", '>', Carbon::now())
            ->exists()) 
            {
            $user = User::where("id", backpack_auth()->user()->id)->first();
            $user->two_factor_code = $two_factor_code;
            $user->two_factor_expires_at = Carbon::now()->addDay(1);
            $user->two_factor_url = null;
            $user->save();
        }else{
            return response()->json([
                'status' => false,
                'message' => 'OTP Tidak Valid!'
                ], 200);
        }

        return response()->json([
            'status' => true,
            'alert' => 'success',
            'message' => 'Sukses OTP',
            'redirect_to' => url('admin/dashboard'),
            'validation_errors' => []
        ], 200);
    }

}