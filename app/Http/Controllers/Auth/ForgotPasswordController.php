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
use App\Rules\IsValidPassword;
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
        return view('vendor.backpack.base.auth.forgot_password');
    }

    public function resetPassword()
    {
        if (request("t") &&
            UserForgotPassword::where("token", request("t"))
            ->where('expired_at', '>', Carbon::now())
            ->exists()) {
                if (backpack_auth()->check()) {
                    backpack_auth()->logout();
                }
                
            return view('vendor.backpack.base.auth.reset_password');
        }else{
            abort(404);
        }
    }


    public function sendLink(Request $request)
    {
        $email = $request->email;
        $token = md5(date("Ymd His"));
        $expiredAt = Carbon::now()->addDays(1);

        if (!User::where('email', $email)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Email Tidak Terdaftar!'
                ], 200);
        }

        $insert_otp = new UserForgotPassword();
        $insert_otp->email = $email;
        $insert_otp->token = $token;
        $insert_otp->expired_at = $expiredAt;
        $insert_otp->save();

        $notesReset = "<br>Batas maksimal reset password hingga : ".$expiredAt."<p>
                        <small>
                            <i>
                            Kata sandi harus berisi minimal 8 karakter, satu karakter huruf besar, satu karakter huruf kecil, satu angka, dan satu karakter khusus
                            </i>
                        </small>
                        </p>";

        $details = [
            'title' => 'Mail from '.env('APP_EMAIL', 'ptkubota.co.id'),
            'message' => 'Gunakan Link di bawah ini untuk mereset password '.$notesReset,
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
            // 'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
            // 'password_confirmation' => 'min:6'
            'password'     => ['required', new IsValidPassword()],
            'password_confirmation' => ['required','same:password',new IsValidPassword()],
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
            $user->last_update_password = now();
            $user->save();

            UserForgotPassword::where('token', $token)
            ->update(['expired_at' => now()]);
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
                'redirect_to' => route('rectmedia.auth.login'),
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
