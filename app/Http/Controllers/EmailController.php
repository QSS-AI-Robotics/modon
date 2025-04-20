<?php

namespace App\Http\Controllers;

use App\Mail\GenericMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    /**
     * Send a dynamic email.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendEmail(Request $request)
    {
        $request->validate([
            'recipients' => 'required|array', // Array of recipient emails
            'recipients.*' => 'email', // Each recipient must be a valid email
            'subject' => 'required|string', // Email subject
            'content' => 'required|string', // Email content
        ]);
    
        // Send the email to each recipient
        foreach ($request->input('recipients') as $recipient) {
            Mail::to($recipient)->send(new GenericMail(
                $request->input('subject'),
                $request->input('content')
            ));
        }
    
        return response()->json(['message' => 'Emails sent successfully!']);
    }
}