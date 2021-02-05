<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Http\Controllers\Traits\FileUploadTrait;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

use App\Models\User;

use QrCode;
use Mail;

class RegisterController extends Controller {
    use FileUploadTrait;
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    protected $status_code = 200;

    public function __construct() {
        $this->middleware('guest');
    }

    protected function validator(array $data) {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    protected function create(array $data) {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    public function userRegister(Request $request) {
        $output = $user = [];

        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'image' => 'nullable|mimes:png,jpg,jpeg,gif',
            'amount' => 'required|numeric|gt:0',
            'phone_number' => ['required', 'numeric', 'digits:10', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            $this->status_code = 400;

            $output = [
                'message' => $validator->errors()->first(),
                'data' => (object) []
            ];
        } else {
            $request = $this->uploadFiles($request, 'user');
            $phone_otp = rand(10000, 999999);
            $email_otp = rand(10000, 999999);
            $email = $request->email;
            $phone_number = $request->phone_number;
            $data['otp'] = $email_otp;

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $email,
                'amount' => $request->amount,
                'image' => $request->image,
                'phone_otp' => $phone_otp,
                'email_otp' => $email_otp,
                'phone_number' => $phone_number,
                'qr_code' => '',
                'is_phone_number_verified' => 0,
                'is_email_verified' => 0,
                'password' => Hash::make($request->password),
            ]);

            // Send SMS Code Start

            // Send SMS Code End

            Mail::send('mail/email_otp', $data, function($message) use ($email) {
                $message->to($email)->subject('Validate OTP');
            });

            $output = [
                'message' => 'You are registered successfully',
                'data' => $user->toArray(),
            ];
        }

		return Response::json($output, $this->status_code);
    }

    public function userVerify(Request $request) {
        $output = [];

        $validator = Validator::make($request->all(), [
            'login_id' => 'required',
            'otp' => 'required'
        ],[
            'login_id.required' => 'Please enter email address or phone number',
            'otp.required' => 'Please enter otp',
        ]);

        if ($validator->fails()) {
            $this->status_code = 400;

            $output = [
                'message' => $validator->errors()->first(),
                'data' => (object) []
            ];
        } else {
            if (is_numeric($request->login_id)) {
                $user = User::where('phone_number', $request->login_id)
                ->where('phone_otp', $request->otp)
                ->first();

                if (!empty($user)) {
                    $user->update(['is_phone_number_verified' => 1]);

                    $output = [
                        'message' => 'Your phone number successfully verified',
                        'data' => $user->toArray(),
                    ];
                } else {
                    $output = [
                        'message' => 'Sorry, please try again !!',
                        'data' => (object) []
                    ];
                }
            } else {
                $user = User::where('email', $request->login_id)
                ->where('email_otp', $request->otp)
                ->first();

                if (!empty($user)) {
                    $user->update(['is_email_verified' => 1]);

                    $output = [
                        'message' => 'Your email address successfully verified',
                        'data' => $user->toArray(),
                    ];
                } else {
                    $output = [
                        'message' => 'Sorry, please try again !!',
                        'data' => (object) []
                    ];
                }
            }
        }

		return Response::json($output, $this->status_code);
    }

    private function generateQrCode ($phone_number) {
        return QrCode::format('png')->merge('https://www.w3adda.com/wp-content/uploads/2019/07/laravel.png', 0.3, true)
        ->size(500)->errorCorrection('H')
        ->generate($phone_number);
    }
}