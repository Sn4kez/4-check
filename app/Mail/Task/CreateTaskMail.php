<?php

namespace App\Mail\Task;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CreateTaskMail extends Mailable
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

        $subject = "4-check - Aufgabenzuweisung";

        if(array_key_exists('sender_address', $this->data)) {
            $senderAddress = $this->data['sender_address'];
        }

        if(array_key_exists('sender_name', $this->data)) {
            $senderName = $this->data['sender_name'];
        }

        return $this->view('emails.task.create')
                    ->from($senderAddress, $senderName)
                    ->replyTo($senderAddress, $senderName)
                    ->subject($subject)
                    ->with([
                        'url' => config('app.url'),
                        'gender' => $this->data['gender'],
                        'name' => $this->data['name'],
                        'creatorGender' => $this->data['creatorGender'],
                        'creatorName' => $this->data['creatorName'],
                        'description' => $this->data['description'],
                        'priority' => $this->data['priority'],
                        'doneDue' => $this->data['doneDue'],
                        'location' => $this->data['location'],
                    ]);
    }
}
