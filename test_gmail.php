<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/src/Exception.php';
require __DIR__ . '/vendor/src/PHPMailer.php';
require __DIR__ . '/vendor/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'andinurfauzi2102@gmail.com';
    $mail->Password = 'APP PASSWORD DI SINI';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('andinurfauzi2102@gmail.com');
    $mail->addAddress('andinurfauzi2102@gmail.com');

    $mail->Subject = "Test Email";
    $mail->Body = "Jika ini masuk berarti berfungsi.";

    $mail->send();
    echo "Email terkirim!";
} catch (Exception $e) {
    echo "Gagal: " . $mail->ErrorInfo;
}
