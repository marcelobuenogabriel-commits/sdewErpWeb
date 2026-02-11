<?php

namespace Modules\FunctionCenter\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;


class FunctionCenterController extends Controller
{

    public function sendEmail($subject, $to, $body, $content, $attach = null)
    {

        $mailData = [
            'subject' => $subject,
            'body' => $body,
            'content' => $content,
            'attach' => $attach
        ];

        return Mail::to($to)->send(new \Modules\FunctionCenter\App\Emails\Mail($mailData));
    }
}
