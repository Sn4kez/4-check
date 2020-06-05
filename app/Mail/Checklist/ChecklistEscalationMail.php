<?php

namespace App\Mail\Checklist;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChecklistEscalationMail extends Mailable
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

        $subject = "4-check - Checklistenfreigabe";

        if(array_key_exists('sender_address', $this->data)) {
            $senderAddress = $this->data['sender_address'];
        }

        if(array_key_exists('sender_name', $this->data)) {
            $senderName = $this->data['sender_name'];
        }

        return $this->view('emails.checklist.escalation')
                    ->from($senderAddress, $senderName)
                    ->replyTo($senderAddress, $senderName)
                    ->subject($subject)
                    ->with([
                        'url' => config('app.url'),
                        'checklist' => $data['checklist'],
                        'id' => $data['id'],
                        'name' => $data['name'],
                        'gender' => $data['gender'],
                        'score' => $data['score'],
                    ]);
    }
}
