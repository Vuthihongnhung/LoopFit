<?php

namespace App\Services;

require_once __DIR__ . '/../../libs/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../libs/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../../libs/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Models\User;

class EmailService
{
    protected $mailer;
    protected $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/mail.php';

        $this->mailer = new PHPMailer(true);

        try {

            // DEBUG
            $this->mailer->SMTPDebug = 2;

            $this->mailer->Debugoutput = function ($str, $level) {
                error_log("SMTP DEBUG [$level]: $str");
            };

            // SMTP
            $this->mailer->isSMTP();

            $this->mailer->Host = $this->config['host'];

            $this->mailer->SMTPAuth = true;

            $this->mailer->Username = $this->config['username'];

            $this->mailer->Password = str_replace(' ', '', $this->config['password']);

            // TLS
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

            $this->mailer->Port = $this->config['port'];

            // UTF8
            $this->mailer->CharSet = 'UTF-8';

            // SSL FIX
            $this->mailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            // FROM
            $this->mailer->setFrom(
                $this->config['from_email'],
                $this->config['from_name']
            );

            $this->mailer->isHTML(true);

        } catch (Exception $e) {

            error_log('Mailer init error: ' . $e->getMessage());
        }
    }

    public function sendOrderConfirmed($order)
    {
        $user = (new User())->getById($order['user_id']);

        if (!$user) {

            error_log("User not found for order #{$order['id']}");

            return false;
        }

        $subject = 'Đơn hàng #' . $order['id'] . ' đã được xác nhận - LoopFit';

        $content = "
            <h2>Xin chào {$user['name']},</h2>

            <p>
                Đơn hàng <strong>#{$order['id']}</strong>
                của bạn đã được xác nhận thành công.
            </p>

            <p>
                <strong>Ngày thuê:</strong>
                " . date('d/m/Y', strtotime($order['rent_date'])) . "
            </p>

            <p>
                <strong>Ngày trả dự kiến:</strong>
                " . date('d/m/Y', strtotime($order['return_date'])) . "
            </p>

            <p>
                Hãy theo dõi đơn hàng trong phần
                <a href='" . BASE_URL . "/orders'>
                    Đơn hàng của tôi
                </a>
            </p>

            <p>Cảm ơn bạn đã sử dụng dịch vụ của LoopFit!</p>
        ";

        return $this->send($user['email'], $subject, $content);
    }

    public function sendOrderCompleted($order)
    {
        $user = (new User())->getById($order['user_id']);

        if (!$user) {

            return false;
        }

        $subject = 'Đơn hàng #' . $order['id'] . ' đã hoàn tất - LoopFit';

        $content = "
            <h2>Xin chào {$user['name']},</h2>

            <p>
                Đơn hàng <strong>#{$order['id']}</strong>
                của bạn đã hoàn tất.
            </p>

            <p>Cảm ơn bạn đã tin tưởng LoopFit!</p>
        ";

        return $this->send($user['email'], $subject, $content);
    }

    public function sendReturnReminder($order)
    {
        $user = (new User())->getById($order['user_id']);

        if (!$user) {

            return false;
        }

        $subject = 'Nhắc nhở trả hàng - LoopFit';

        $content = "
            <h2>Xin chào {$user['name']},</h2>

            <p>
                Đơn hàng <strong>#{$order['id']}</strong>
                sẽ đến hạn trả vào ngày
                <strong>
                    " . date('d/m/Y', strtotime($order['return_date'])) . "
                </strong>.
            </p>

            <p>
                Vui lòng trả đúng hạn để tránh phí trễ.
            </p>
        ";

        return $this->send($user['email'], $subject, $content);
    }

    // PUBLIC để test được
    public function send($to, $subject, $htmlContent)
    {
        try {

            $this->mailer->clearAddresses();

            $this->mailer->addAddress($to);

            $this->mailer->Subject = $subject;

            $this->mailer->Body = $htmlContent;

            $this->mailer->AltBody = strip_tags($htmlContent);

            $result = $this->mailer->send();

            error_log(
                "Email sent to $to at " . date('Y-m-d H:i:s')
            );

            return $result;

        } catch (Exception $e) {

            error_log(
                "PHPMailer Error: " . $this->mailer->ErrorInfo
            );

            error_log(
                "Exception: " . $e->getMessage()
            );

            return false;
        }
    }
}