<?php
namespace App\Helpers;

use App\Models\Configuration;
use App\Models\Delivery;
use App\Models\LockedAccount;
use App\Models\PurchaseOrder;
use App\Models\TempCountFailure;
use App\Models\User;
use Carbon\Carbon;

class AccountAttempt
{

  public function insert($username, $type){
    $attemp_failure = Configuration::where('name', 'attemp_failure_'.$type)->first()->value;
    $locked_account_on_failure = Configuration::where('name', 'locked_account_on_failure_'.$type)->first()->value;
    $exist_user = User::where('username', $username)->exists();
    $response['status'] = false;
    $response['message'] = "User is not exist";
    if ($exist_user) {
        $max_attempt = $attemp_failure;
        $minutes_passed = $locked_account_on_failure;
        $count_failure = TempCountFailure::where('account', $username)->where('type', $type)->count();
        $current_ip = (new Constant())->getUserIp();
        $la = $this->checkLock($username, $type);
            if ($la['status'] == false) {
              $response['status'] = $la['status'];
              $response['message'] = $la['message'];
            }else{
              if ($count_failure >= $max_attempt) {
                $insert_la = new LockedAccount();
                $insert_la->account = $username;
                $insert_la->type = $type;
                $insert_la->ip = $current_ip;
                $insert_la->ua = $_SERVER['HTTP_USER_AGENT'];
                $insert_la->detail = null;
                $insert_la->lock_start = now();
                $insert_la->lock_end = Carbon::now()->addMinutes($minutes_passed);
                $insert_la->save();

                TempCountFailure::where('account', $username)->where('type', $type)->delete();
    
                $response['status'] = false;
                $response['message'] = 'Max Attempt '.$max_attempt.' Please try again '.$minutes_passed.' minutes later';
              }else{
                  $insert_failure = new TempCountFailure();
                  $insert_failure->account = $username;
                  $insert_failure->type = $type;
                  $insert_failure->ip = $current_ip;
                  $insert_failure->ua = $_SERVER['HTTP_USER_AGENT'];
                  $insert_failure->detail = null;
                  $insert_failure->save();
                  $response['status'] = true;
              }
            }
        
    }

    return $response;
  }

  public function checkLock($username, $type)
  {
    $la = LockedAccount::where('account', $username)
            ->where('type', $type)
            ->where('lock_end', '>', now())
            ->first();
    if (isset($la)) {
      $response['status'] = false;
      $response['message'] = 'Account Locked until '.$la->lock_end;
    }else{
      $response['status'] = true;
      $response['message'] = '';
    }

    return $response;
  }
  
}