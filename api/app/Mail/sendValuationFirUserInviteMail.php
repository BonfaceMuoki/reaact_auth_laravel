<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class sendValuationFirUserInviteMail extends Mailable
{
    use Queueable, SerializesModels;
    public $token,$registration_url,$login_url, $invited_by,$invitee_details;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($token,$registration_url,$login_url,$invited_by,$invitee_details)
    {
      $this->token=$token;
      $this->registration_url=$registration_url;
      $this->login_url=$login_url; 
      $this->invited_by=$invited_by; 
      $this->invitee_details=$invitee_details; 
        //
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Send Valuation Firm User Invite Mail',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'Email.valuationfirms.invitevaluationfirmuser',
            with: [
                'token' => $this->token,
                'rgistrationcallbackurl' => $this->registration_url,
                'logincallback' => $this->login_url,
                'invitee_name' => $this->invitee_details['name'],
                'invted_by' => $this->invited_by['full_name'],
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
