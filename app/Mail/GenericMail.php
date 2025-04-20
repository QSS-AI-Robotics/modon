<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GenericMail extends Mailable
{
    use Queueable, SerializesModels;

    public $emailContent; // Dynamic email content
    public $emailSubject; // Dynamic email subject

    /**
     * Create a new message instance.
     *
     * @param string $emailSubject
     * @param string $emailContent
     */
    public function __construct($emailSubject, $emailContent)
    {
        $this->emailSubject = $emailSubject;
        $this->emailContent = $emailContent;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->emailSubject) // Set the dynamic subject
                    ->view('emails.generic_mail') // Use a generic Blade view
                    ->with(['emailContent' => $this->emailContent]); // Pass the dynamic content to the view
    }
}