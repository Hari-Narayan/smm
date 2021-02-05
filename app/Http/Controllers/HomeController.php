<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

use App\Models\User;

use Auth;

class HomeController extends Controller {
    protected $status_code = 200;

    public function __construct() {}

    public function getUserDetails() {
        $output = [
            'message' => 'Login success',
            'data' => Auth::user(),
        ];

        return Response::json($output, $this->status_code);
    }

    public function moneyTransfer(Request $request) {
        $output = $sender = $receiver = [];

        $validator = Validator::make($request->all(), [
            'amount' => 'required',
            'phone_number' => 'required',
        ]);

        if ($validator->fails()) {
            $this->status_code = 400;

            $output = [
                'message' => $validator->errors()->first(),
                'data' => (object) []
            ];
        } else {
            if (Auth::user()->phone_number != $request->phone_number) {
                $sender = User::where('id', Auth::id())
                ->where('amount', '>=', $request->amount)
                ->first();

                if (!empty($sender)) {
                    $receiver = User::where('phone_number', $request->phone_number)
                    ->first();

                    if (!empty($receiver)) {
                        $sender->update(['amount' => $sender->amount - $request->amount]);
                        $receiver->update(['amount' => $receiver->amount + $request->amount]);

                        $output = [
                            'message' => 'Money tranfer successfully',
                            'data' => (object) [],
                        ];
                    } else {
                        $this->status_code = 400;

                        $output = [
                            'message' => 'Receiver not found',
                            'data' => (object) [],
                        ];
                    }
                } else {
                    $this->status_code = 400;

                    $output = [
                        'message' => 'Insufficient amount',
                        'data' => (object) [],
                    ];
                }
            } else {
                $this->status_code = 400;

                $output = [
                    'message' => 'You can not send money to yourself',
                    'data' => (object) [],
                ];
            }
        }

        return Response::json($output, $this->status_code);
    }
}
