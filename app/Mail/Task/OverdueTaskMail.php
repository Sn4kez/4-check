<?php

namespace App\Mail\Task;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OverdueTaskMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array
     */
    private $data;

    /**
     * Create a new message instance.
     *
     * @param $data
     */
    public function __construct(array $data)
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

        $subject = "4-check - Aufgabenerinnerung";

        if(array_key_exists('sender_address', $this->data)) {
            $senderAddress = $this->data['sender_address'];
        }

        if(array_key_exists('sender_name', $this->data)) {
            $senderName = $this->data['sender_name'];
        }

        return $this->view('emails.task.overdue')
                    ->from($senderAddress, $senderName)
                    ->replyTo($senderAddress, $senderName)
                    ->subject($subject)
                    ->with([
                        'url' => config('app.url'),
                        'gender' => $this->data['gender'],
                        'name' => $this->data['name'],
                        'doneDue' => $this->data['doneDue'],
                        'description' => $this->data['description'],
                    ]);
    }
}
