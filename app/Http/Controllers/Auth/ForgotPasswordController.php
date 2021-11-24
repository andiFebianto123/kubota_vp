<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\TwoFactor;
use App\Mail\ResetPasswordMail;
use App\Mail\TwoFactorMail;
use App\Models\User;
use App\Models\UserForgotPassword;
use App\Models\UserOtp;
use App\Notifications\TwoFactorCode;
use Backpack\CRUD\app\Library\Auth\AuthenticatesUsers as AuthAuthenticatesUsers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    

    public function forgotPassword()
    {
        return view('vendor.backpack.base.auth.forgot-password');

    }

    public function resetPassword() 
    {
        if (request("t") && 
            UserForgotPassword::where("token", request("t"))
            ->where('expired_at', '>', Carbon::now())
            ->exists()) {
            return view('vendor.backpack.base.auth.reset-password');
        }else{
            abort(404);
        }
    }


    public function sendLink(Request $request)
    {
        $email = $request->email;

        $token = md5(date("Ymd His"));

        if (!User::where('email', $email)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Email Tidak Terdaftar!'
                ], 200);
        }

        $insert_otp = new UserForgotPassword();
        $insert_otp->email = $email;
        $insert_otp->token = $token;
        $insert_otp->expired_at = Carbon::now()->addMinutes(5);
        $insert_otp->save();

        $details = [
            'title' => 'Mail from Kubota.com',
            'message' => 'Gunakan Link di bawah ini untuk mereset password',
            'type' => 'forgot_password',
            'fp_url' => route("reset-password")."?t=".$token
        ];

        Mail::to($email)->send(new ResetPasswordMail($details));
        return response()->json([
            'status' => true,
            'alert' => 'success',
            'message' => 'Sukses Mengirim Email',
        ], 200);
    }

    public function update(Request $request)
    {
        $password = $request->password;
        $token = $request->token;

        $rules = [
            'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'min:6'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $message_errors = $this->modify($validator, $rules);

            return response()->json([
                'status' => false,
                'alert' => 'danger',
                'message' => 'Required Form',
                'validation_errors' => $message_errors,
            ], 200);
        }

        $user_fp = UserForgotPassword::where('token', $token)
                    ->where('expired_at', '>', Carbon::now())
                    ->first();

        if (isset($user_fp)) {
            $user = User::where('email', $user_fp->email)->first();
            $user->password = bcrypt($password);
            $user->save();
        }else{
            return response()->json([
                'status' => false,
                'alert' => 'danger',
                'message' => 'Token tidak valid!',
            ], 200);
        }

        return response()->json([
                'status' => true,
                'alert' => 'success',
                'message' => 'Sukses Update Password, silahkan <a href="'.route('rectmedia.auth.login').'" class="text-warning font-weight-bold"> Login </a> menggunakan password baru anda',
            ], 200);
    }

    public function modify($validator,$rules)
    {
        $message_errors = [];
            $obj_validators     = $validator->errors();
            foreach(array_keys($rules) as $key => $field){
                if ($obj_validators->has($field)) {
                    $message_errors[] = ['id' => $field , 'message'=> $obj_validators->first($field)];
                }
            }
        return $message_errors;
    }

}