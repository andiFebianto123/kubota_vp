<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Auth;

class TwoFactor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $can_login = false;
        if (Auth::guard('backpack')->check()) {
            $auth = Auth::guard('backpack')->user();
            if($auth->two_factor_code != null 
            && $auth->two_factor_expires_at > Carbon::now()
            && $auth->two_factor_url == null)
            {
                $can_login = true;
            }
        }

        if ($can_login) {
            return $next($request);
        }else{
            session()->flash('message', 'Kode OTP sudah kadaluarsa');
    
            return redirect()->guest(backpack_url('logout')); 
        }

    }

}