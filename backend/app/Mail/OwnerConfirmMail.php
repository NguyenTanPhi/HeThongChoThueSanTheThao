<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OwnerConfirmMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Xác nhận đăng ký chủ sân')
                    ->view('emails.owner_confirm')
                    ->with([
                        'user' => $this->user,
                        'confirmUrl' => 'http://localhost:5173/open/confirmEmail?email=' . urlencode($this->user->email),

                    ]); 
    }
}
