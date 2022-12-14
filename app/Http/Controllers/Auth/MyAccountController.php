<?php

namespace App\Http\Controllers\Auth;

use Alert;
use App\Http\Requests\AccountInfoRequest;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use App\Rules\IsValidPassword;
use Carbon\Carbon;

class MyAccountController extends Controller
{
    protected $data = [];

    public function __construct()
    {
        $this->middleware(backpack_middleware());
    }

    /**
     * Show the user a form to change their personal information & password.
     */
    public function getAccountInfoForm()
    {
        $this->data['title'] = trans('backpack::base.my_account');
        $this->data['user'] = $this->guard()->user();
        return view(backpack_view('my_account'), $this->data);
    }

    /**
     * Save the modified personal information for a user.
     */
    public function postAccountInfoForm(AccountInfoRequest $request)
    {
        $result = $this->guard()->user()->update($request->except(['_token']));

        if ($result) {
            Alert::success(trans('backpack::base.account_updated'))->flash();
        } else {
            Alert::error(trans('backpack::base.error_saving'))->flash();
        }

        return redirect()->back();
    }

    protected function guard()
    {
        return backpack_auth();
    }

    public function postChangePasswordForm2(ChangePasswordRequest $request)
    {
        $request->validate(
            [
                'new_password' => ['required',  new IsValidPassword()]
            ]
            );
        $user = $this->guard()->user();
        $user->password = Hash::make($request->new_password);
        $user->last_update_password = Carbon::now();

        if ($user->save()) {
            Alert::success(trans('backpack::base.account_updated'))->flash();
        } else {
            Alert::error(trans('backpack::base.error_saving'))->flash();
        }

        return redirect()->back();
    }
}
