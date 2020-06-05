<?php

namespace App\Mail\Internal;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewUserInformationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array
     */
    private $data;

    /**
     * Create a new message instance.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $senderAddress = config('app.mail_address');
        $senderName = config('app.mail_name');

        $subject = "test";

        if(array_key_exists('sender_address', $this->data)) {
            $senderAddress = $this->data['sender_address'];
        }

        if(array_key_exists('sender_name', $this->data)) {
            $senderName = $this->data['sender_name'];
        }

        return $this->view('emails.internal.newUserInformation')
                    ->from($senderAddress, $senderName)
                    ->replyTo($senderAddress, $senderName)
                    ->subject($subject)
                    ->with([
                        'url' => config('app.url'),
                    ]);

    }
}
