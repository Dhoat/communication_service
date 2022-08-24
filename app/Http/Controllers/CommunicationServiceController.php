<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Mail;
use App\CommunicationLog;

use Illuminate\Http\Request;

class CommunicationServiceController extends Controller
{
    public function sendEmail(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'to'          => 'required',
            'subject'     => 'required',
            'content'     => 'required',
        ]);
        if($validator->fails())
        {
            return response()->json($validator->errors(), 422);
        }
        $subject = $request->subject;
        $to = $request->to;

        Mail::raw($request->content, function ($m) use($to, $subject){
            $m->to($to);
            $m->subject($subject);
        });

        $this->addLog(CommunicationLog::TYPE_EMAIL, $to, $request->content, $subject);

        return [
            'status' => 200,
            'message'=> 'email  succefull sent',
        ];
          
      
    }

    public function sendSms(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'to'          => 'required',
            'content'     => 'required',
        ]);

        $basic  = new \Vonage\Client\Credentials\Basic(env('SMS_KEY'), env('SMS_PASSWORD'));
        $client = new \Vonage\Client($basic);

        $response = $client->sms()->send(
            new \Vonage\SMS\Message\SMS($request->to, BRAND_NAME, $request->content)
        );

        $message = $response->current();

        if ($message->getStatus() == 0) {

            $this->addLog(CommunicationLog::TYPE_SMS, $request->to, $request->content, BRAND_NAME, CommunicationLog::SUCCESSFULL);

        return [
            'status' => 200,
            'message'=> 'sms succefull sent',
        ];
        } else {
            $this->addLog(CommunicationLog::TYPE_SMS, $request->to, $request->content, BRAND_NAME, CommunicationLog::FAILLED);   
        }
        return [
            'status' => 403,
            'message'=> 'sms  failled',
        ];

    }

    private function addLog($type, $to, $content, $subject, $status)
    {
        CommunicationLog::insert([
            'type'    => $type,
            'to'      => $to,
            'subject' => $subject,
            'content' => $content,
            'status'  => $status
        ]);
        return;
    }

    public function getCommunicationLog(Request $request){

        $log = CommunicationLog::select(['id', 'type', 'to', 'subject', 'content', 'status', 'created_at']);
       
        if(!empty($request->from_date)) {
            $log->where('created_at','>=', $request->from);
        }
        if(!empty($request->to_date)) {
            $log->where('created_at', '<', $request->to);
        }

        if(!empty($request->type))
        {
            $log->where('type', $request->type);
        }
        if(isset($request->status))
        {
            $log->where('status', $request->status);
        }

        if(!empty($request->to))
        {
            $log->where('to', $request->to);
        }

        $log = $log->get();
        return [
            'status' => 200,
            'message' => $log
        ]; 


    }
}
