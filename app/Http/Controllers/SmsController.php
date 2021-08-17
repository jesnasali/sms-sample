<?php

namespace App\Http\Controllers;

use App\Libraries\SmsService;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Action to send an sms
     */
    public function send(Request $request)
    {
        // reads request params
        $to = $request->input('to');
        $provider = $request->input('provider');
        $content = $request->input('content');

        // execute sms service and return the result
        $smsService = new SmsService($to, $content);
        if($smsService->send($provider)){
            return response()->json([
                'code' => 200,
                'message' => 'Message sent successfully.'
            ]);
        }else{
            return response()->json([
                'code' => 400,
                'message' => $smsService->error()
            ]);
        }
    }
}
