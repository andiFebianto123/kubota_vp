<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\AccountAttempt;
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
use App\Mail\vendorNewPo;
use App\Models\TempCountFailure;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthAuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
  
    public function __construct()
    {
        $guard = backpack_guard_name();
        $this->middleware("guest:$guard", ['except' => 'logout']);
    }

    public function index()
    {
        return view('vendor.backpack.base.auth.login');
    }

    public function forgotPassword()
    {
        return view('vendor.backpack.base.auth.forgot-password');

    }


    public function sendLinkforgotPassword(Request $request)
    {
        $email = $request->email;

        $token = md5(date("Ymd His"));

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

        return view('vendor.backpack.base.auth.forgot-password');
    }

    public function authenticate(Request $request)
    {
        $input = $request->all();
     
        $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $check_lock = (new AccountAttempt())->checkLock($input['username'], 'login');

        if ($check_lock['status'] == false) {
            return response()->json([
                'status' => $check_lock['status'],
                'message' => $check_lock['message']
            ], 200);
        }


        if(Auth::guard(backpack_guard_name())->attempt(array($fieldType => $input['username'], 'password' => $input['password']))
        ) 
        {
            $two_factor_code = strtoupper(substr(md5(date("Ymd His")), 0, 8));
            $two_factor_url = md5($two_factor_code);

            $details = [
                'title' => 'Mail from Kubota.com',
                'message' => 'Kode OTP anda adalah',
                'type' => 'otp',
                'otp_code' => "<span style='font-size:30px;'>".$two_factor_code. "</span>",
                'otp_url' => route("twofactor")."?t=".$two_factor_url
            ];

            $user = User::where("id", backpack_auth()->user()->id)->first();
            $user->two_factor_code = $two_factor_code;
            $user->two_factor_url = $two_factor_url;
            $user->two_factor_expires_at = Carbon::now()->addMinutes(5);
            $user->save();

            $insert_otp = new UserOtp(); 
            $insert_otp->user_id = backpack_auth()->user()->id;
            $insert_otp->two_factor_code = $two_factor_code;
            $insert_otp->two_factor_url = $two_factor_url;
            $insert_otp->expired_at = Carbon::now()->addMinutes(5);
            $insert_otp->save();

            TempCountFailure::where('account', $input['username'])->where('type', 'login')->delete();

            Mail::to($user->email)->send(new TwoFactorMail($details));

            // Mail::to($user->email)->send(new vendorNewPo($details));

            return response()->json([
                'status' => true,
                'alert' => 'success',
                'message' => 'Sukses Login',
                'redirect_to' => url('two-factor')."?t=".$two_factor_url,
                'validation_errors' => []
            ], 200);
        }else{
            $username = $input['username'];
            $at = (new AccountAttempt())->insert($username, 'login');
            
            return response()->json([
                'status' => false,
                'message' => (isset($at['message']))?$at['message']:'Username atau Password Salah'
                ], 200);
            
        }
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required'],
        ]);
    }

    public function logout () {
        session()->put('prev_url', url()->previous());

        if (backpack_auth()->check()) {
            $user = User::where("id", backpack_auth()->user()->id)->first();
            $user->two_factor_code = null;
            $user->two_factor_expires_at = null;
            $user->two_factor_url = null;
            $user->save();
        }
        
        backpack_auth()->logout();
        return redirect()->route("rectmedia.auth.login");
    }

    protected function guard()
    {
        return backpack_auth();
    }
}