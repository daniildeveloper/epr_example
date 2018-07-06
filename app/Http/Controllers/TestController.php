<?php

namespace App\Http\Controllers;

use Mail;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test (Request $request) {
        $data = [
            "title" => 'TestController',
            'subject' => 'Some subject',
        ];
        Mail::send('mail.test', $data, function ($message) {
            $message->from(env('MAIL_USERNAME'), 'John Doe');
        
            $message->to('daniildeveloper@yandex.com');
        
            $message->subject('Subject');
        
            $message->priority(3);
        });
    }
}
