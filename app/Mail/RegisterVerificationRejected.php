<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegisterVerificationRejected extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $role;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $role)
    {
        $this->name = $name;
        $this->role = $role;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.register_verification_rejected')
                    ->subject(ucwords($this->role)." Registration Rejected");
    }
}
