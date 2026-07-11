<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = strip_tags(trim($_POST["name"] ?? ''));
    $email = filter_var(trim($_POST["email"] ?? ''), FILTER_SANITIZE_EMAIL);
    $message = trim($_POST["message"] ?? '');
    $lang = $_POST["lang"] ?? 'en'; // Formdan gelen dil bilgisi

    if (empty($name) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => getMessage('error_input', $lang)]);
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.sametbozkurt.com.tr';
        $mail->SMTPAuth = true;
        $mail->Username = 'info@sametbozkurt.com.tr';
        $mail->Password = 'YOUR_PASSWORD_HERE';
        $mail->SMTPSecure = ''; // İstersen 'tls' veya 'ssl' yap
        $mail->Port = 587;

        $mail->setFrom('info@sametbozkurt.com.tr', 'Web Form');
        $mail->addAddress('info@sametbozkurt.com.tr');
        $mail->addReplyTo($email, $name);

        $mail->isHTML(false);
        $mail->Subject = 'Yeni iletişim formu mesajı';
        $mail->Body = "İsim: $name\nEmail: $email\n\nMesaj:\n$message";

        $mail->send();
        echo json_encode(["status" => "success", "message" => getMessage('success', $lang)]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => getMessage('error_send', $lang) . ": " . $mail->ErrorInfo]);
    }
}

function getMessage($type, $lang) {
    $messages = [
        'success' => [
            'en' => 'Your message has been sent successfully.',
            'tr' => 'Mesajınız başarıyla gönderildi.',
            'de' => 'Ihre Nachricht wurde erfolgreich gesendet.'
        ],
        'error_input' => [
            'en' => 'Please fill in all fields correctly.',
            'tr' => 'Lütfen tüm alanları doğru girin.',
            'de' => 'Bitte füllen Sie alle Felder korrekt aus.'
        ],
        'error_send' => [
            'en' => 'An error occurred while sending the email',
            'tr' => 'Mail gönderilirken hata oluştu',
            'de' => 'Beim Senden der Nachricht ist ein Fehler aufgetreten'
        ]
    ];

    return $messages[$type][$lang] ?? $messages[$type]['en'];
}
