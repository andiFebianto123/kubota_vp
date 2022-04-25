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
    $attempFailure = Configuration::where('name', 'attemp_failure_'.$type)->first()->value;
    $lockedAccountOnFailure = Configuration::where('name', 'locked_account_on_failure_'.$type)->first()->value;
    $existUser = User::where('username', $username)->exists();
    $response['status'] = false;
    if ($existUser) {
        $maxAttempt = $attempFailure;
        $minutesPassed = $lockedAccountOnFailure;
        $countFailure = TempCountFailure::where('account', $username)->where('type', $type)->count();
        $currentIp = (new Constant())->getUserIp();
        $la = $this->checkLock($username, $type);
            if ($la['status'] == false) {
              $response['status'] = $la['status'];
              $response['message'] = $la['message'];
            }else{
              if ($countFailure >= $maxAttempt) {
                $insertLa = new LockedAccount();
                $insertLa->account = $username;
                $insertLa->type = $type;
                $insertLa->ip = $currentIp;
                $insertLa->ua = $_SERVER['HTTP_USER_AGENT'];
                $insertLa->detail = null;
                $insertLa->lock_start = now();
                $insertLa->lock_end = Carbon::now()->addMinutes($minutesPassed);
                $insertLa->save();

                $tempCountFailures = TempCountFailure::where('account', $username)->where('type', $type)->get();
                if($tempCountFailures->count() > 0){
                  foreach($tempCountFailures as $tempCountFailure){
                    $tempCountFailure->delete();
                  }
                }
    
                $response['status'] = false;
                $response['message'] = 'Max Attempt '.$maxAttempt.' Please try again '.$minutesPassed.' minutes later';
              }else{
                  $insertFailure = new TempCountFailure();
                  $insertFailure->account = $username;
                  $insertFailure->type = $type;
                  $insertFailure->ip = $currentIp;
                  $insertFailure->ua = $_SERVER['HTTP_USER_AGENT'];
                  $insertFailure->detail = null;
                  $insertFailure->save();
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