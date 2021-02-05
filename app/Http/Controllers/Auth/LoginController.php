<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use App\Models\User;

use Auth;

class LoginController extends Controller {
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

    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    protected $status_code = 200;

    public function __construct() {
        $this->middleware('guest')->except('logout');
    }

    public function userLogin(Request $request) {
        $output = [];

        $validator = Validator::make($request->all(), [
            'login_id' => 'required',
            'password' => 'required'
        ],[
            'login_id.required' => 'Please enter email address or phone number',
            'password.required' => 'Please enter password',
        ]);

        if ($validator->fails()) {
            $this->status_code = 400;

            $output = [
                'message' => $validator->errors()->first(),
                'data' => (object) []
            ];
        } else {
            if (is_numeric($request->login_id)) {
                if (Auth::attempt(['phone_number' => request('login_id'), 'password' => request('password')])) {
                    if (Auth::user()->is_phone_number_verified == 0) {
                        $output = [
                            'message' => 'Phone number not varified',
                            'data' => (object) []
                        ];
                    } else {
                        $output = [
                            'message' => 'Login success',
                            'data' => Auth::user(),
                        ];

                        $output['data']['token'] = Auth::user()->createToken('appToken')->accessToken;
                    }
                } else {
                    $output = [
                        'message' => 'Incorrect phone number or password',
                        'data' => (object) []
                    ];
                }
            } else {
                if (Auth::attempt(['email' => request('login_id'), 'password' => request('password')])) {
                    if (Auth::user()->is_email_verified == 0) {
                        $output = [
                            'message' => 'Email address not varified',
                            'data' => (object) []
                        ];
                    } else {
                        $output = [
                            'message' => 'Login success',
                            'data' => Auth::user(),
                        ];

                        $output['data']['token'] = Auth::user()->createToken('appToken')->accessToken;
                    }
                } else {
                    $output = [
                        'message' => 'Incorrect email address or password',
                        'data' => (object) []
                    ];
                }
            }
        }

		return Response::json($output, $this->status_code);
    }

    public function logout(Request $request) {
        Auth::logout();

        return Response::json([
            'message' => 'Logout success',
            'data' => (object) [],
        ], $this->status_code);
    }
}
