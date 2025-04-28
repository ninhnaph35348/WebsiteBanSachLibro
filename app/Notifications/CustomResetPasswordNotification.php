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
        return (new MailMessage)
            ->subject('Yêu cầu thay đổi mật khẩu') // Chủ đề email
            ->greeting('Xin chào!') // Lời chào
            ->line('Chúng tôi đã nhận được yêu cầu thay đổi mật khẩu cho tài khoản của bạn.')
            ->line('Nhấp vào nút dưới đây để đặt lại mật khẩu của bạn.')
            ->action('Đặt lại mật khẩu', url('password/reset', $this->token)) // Link reset mật khẩu
            ->line('Đường dẫn này sẽ hết hạn trong 60 phút.')
            ->line('Nếu bạn không yêu cầu thay đổi mật khẩu, không cần thực hiện thêm hành động nào.')
            ->salutation('Trân trọng, Libro'); // Lời chào cuối
    }
}
