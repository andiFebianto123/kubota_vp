<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\AccountAttempt;
use App\Http\Controllers\Controller;
use App\Mail\TwoFactorMail;
use App\Models\Configuration;
use App\Models\TempCountFailure;
use App\Models\User;
use App\Models\UserOtp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{

    public function index() 
    {
        $uid = (backpack_auth()->user()) ? backpack_auth()->user()->id : null;
        if (request("t") && User::where("two_factor_url", request("t"))->where('id', $uid)->exists()) {
            return view('vendor.backpack.base.auth.two_factor');
        }else{
            return redirect()->to("/");
        }
    }

    public function update(Request $request) 
    {
        $twoFactorCode = $request->two_factor_code;
        $confExpOtp = Configuration::where('name', 'expired_otp')->first();
        $expiredOtp = ($confExpOtp) ? $confExpOtp->value:1; // in day
        $username = backpack_auth()->user()->username;
        $checkLock = (new AccountAttempt())->checkLock($username, 'otp');
        $redirectTo = (session()->has('prev_url'))? session()->get('prev_url'): url('admin/dashboard');
        $checkExistOtp = User::where("id", backpack_auth()->user()->id)
                            ->where("two_factor_code", $twoFactorCode)
                            ->where("two_factor_expires_at", '>', Carbon::now())
                            ->exists();

        if ($checkLock['status'] == false) {
            return response()->json([
                'status' => $checkLock['status'],
                'message' => $checkLock['message']
            ], 200);
        }

        if ($checkExistOtp){
            $user = User::where("id", backpack_auth()->user()->id)->first();
            $user->two_factor_code = $twoFactorCode;
            $user->two_factor_expires_at = Carbon::now()->addDay($expiredOtp);
            $user->two_factor_url = null;
            $user->last_login = now();
            $user->ip = $this->getClientIp();
            $user->user_agent = $_SERVER['HTTP_USER_AGENT'];
            $user->save();

            $updateOtp = UserOtp::where("user_id", backpack_auth()->user()->id)->first();
            $updateOtp->two_factor_code = $twoFactorCode;
            $updateOtp->expired_at = Carbon::now()->addDay($expiredOtp);
            $updateOtp->save();

            TempCountFailure::where('account', $username)->where('type', 'otp')->delete();
        }else{
            $at = (new AccountAttempt())->insert($username, 'otp');
            
            return response()->json([
                'status' => false,
                'message' => (isset($at['message']))?$at['message']:'OTP Tidak Valid!'
                ], 200);
        }
                
        return response()->json([
            'status' => true,
            'alert' => 'success',
            'message' => 'Sukses OTP',
            'redirect_to' => $redirectTo,
            'validation_errors' => []
        ], 200);
    }


    private function getClientIp() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
           $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

}