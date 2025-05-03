<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPasswordNotification extends ResetPasswordNotification
{
    /**
     * Lấy thông tin mail của thông báo.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(), // 👈 thêm email vào
        ], false));

        return (new MailMessage)
            ->subject('Đặt lại mật khẩu')
            ->line('Bạn nhận được email này vì chúng tôi nhận được yêu cầu đặt lại mật khẩu.')
            ->action('Đặt lại mật khẩu', $resetUrl)
            ->line('Nếu bạn không yêu cầu đặt lại mật khẩu, hãy bỏ qua email này.');
    }
}
