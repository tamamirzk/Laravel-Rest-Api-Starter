<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Welcome extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var User
     */
    private $model;

    /**
     * Create a new message instance.
     *
     * @param User $model
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject(trans('Pasar Negeri'))
            ->from(env('MAIL_USERNAME'), 'Pasar Negeri')
            ->view('emails.welcome')
            ->with(['email' => $this->model->email, 'id' => $this->model->id]);
    }
}