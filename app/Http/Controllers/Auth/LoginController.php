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
use App\Helpers\EmailLogWriter;
use Illuminate\Support\Facades\DB;
use Exception;

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

        $insertOtp = new UserForgotPassword();
        $insertOtp->email = $email;
        $insertOtp->token = $token;
        $insertOtp->expired_at = Carbon::now()->addMinutes(5);
        $insertOtp->save();

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

        $checkLock = (new AccountAttempt())->checkLock($input['username'], 'login');

        if ($checkLock['status'] == false) {
            return response()->json([
                'status' => $checkLock['status'],
                'message' => $checkLock['message']
            ], 200);
        }


        if(Auth::guard(backpack_guard_name())->attempt(array($fieldType => $input['username'], 'password' => $input['password']))) 
        {
            $twoFactorCode = strtoupper(substr(md5(date("Ymd His")), 0, 8));
            $two_factor_url = md5($twoFactorCode);

            $details = [
                'title' => 'Mail from Kubota.com',
                'message' => 'Kode OTP anda adalah',
                'type' => 'otp',
                'otp_code' => "<span style='font-size:30px;'>".$twoFactorCode. "</span>",
                'otp_url' => route("twofactor")."?t=".$two_factor_url
            ];

            $user = User::where("id", backpack_auth()->user()->id)->first();
            $user->two_factor_code = $twoFactorCode;
            $user->two_factor_url = $two_factor_url;
            $user->two_factor_expires_at = Carbon::now()->addMinutes(5);
            $user->save();

            $insertOtp = new UserOtp(); 
            $insertOtp->user_id = backpack_auth()->user()->id;
            $insertOtp->two_factor_code = $twoFactorCode;
            $insertOtp->two_factor_url = $two_factor_url;
            $insertOtp->expired_at = Carbon::now()->addMinutes(5);
            $insertOtp->save();

            TempCountFailure::where('account', $input['username'])->where('type', 'login')->delete();
            
            try{
                Mail::to($user->email)->send(new TwoFactorMail($details));
            }
            catch(Exception $e){
                DB::beginTransaction();
                $subject = "Mail from Kubota.com";
                (new EmailLogWriter())->create($subject, $user->email, $e->getMessage());
                DB::commit();

                return response()->json([
                    'status' => false,
                    'alert' => 'error',
                    'message' => 'Mail not sent. Please check error logs for further information',
                ], 500);
            }

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
        $urlParent = parse_url(url()->previous());

        if (array_key_exists("query", $urlParent)) {
            parse_str($urlParent['query'], $param_url);

            if (isset($param_url['prev_session'])) {
                session()->put('prev_url', url()->previous());
            }
        }

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