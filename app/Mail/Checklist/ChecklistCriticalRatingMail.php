<?php

namespace App\Mail\Checklist;

use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class ChecklistCriticalRatingMail extends Mailable
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

        $subject = "4-check - kritische Bewertung";

        if(array_key_exists('sender_address', $this->data)) {
            $senderAddress = $this->data['sender_address'];
        }

        if(array_key_exists('sender_name', $this->data)) {
            $senderName = $this->data['sender_name'];
        }

        return $this->view('emails.checklist.criticalRating')
                    ->from($senderAddress, $senderName)
                    ->replyTo($senderAddress, $senderName)
                    ->subject($subject)
                    ->with([
                        'url' => config('app.url'),
                        'checklist' => $this->data['checklist'],
                        'id' => $this->data['id'],
                        'name' => $this->data['name'],
                        'gender' => $this->data['gender'],
                        'ratings' => $this->data['ratings'],
                        'audit' => $this->data['audit'],
                        'execGender' => $this->data['execGender'],
                        'execName' => $this->data['execName'],
                    ]);
    }
}
