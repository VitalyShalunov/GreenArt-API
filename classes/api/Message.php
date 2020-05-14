<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once 'phpmailer/vendor/autoload.php';
require 'phpmailer/vendor/phpmailer/phpmailer/src/Exception.php';
require 'phpmailer/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'phpmailer/vendor/phpmailer/phpmailer/src/SMTP.php';
class Message
{
    private $data;
    function __construct($data) {
        $this->data = $data;
    }

    public function sendMessage()
    {
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if (!isset($this->data['nameSite'])||
            !isset($this->data['name'])||
            !isset($this->data['email'])||
            !isset($this->data['numberOfPhone'])||
            !isset($this->data['message']))throw new ErrorAPI("Data wasn't sent", 400);

        $mail = new PHPMailer;

        $mail->CharSet = 'UTF-8';
        $mail->SMTPDebug = 0;
        $mail->isSMTP();

        $mail->Host = gethostbyname('smtp.gmail.com');
        $mail->SMTPAuth = true;
        $mail->Username = "email";//Изменить
        $mail->Password = "password";//Изменить
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587; 

        $mail->setFrom('email', $this->data['name']);//Изменить
        $mail->addAddress('email', 'Decor');//Изменить

        $mail->Subject = 'message from '.$this->data['nameSite'];
        $mail->isHTML(true);
        $mailContent = "<p>".$this->data['message']."</p>";
        $mail->Body = $mailContent;
        $mail->AltBody = "Текстовая версия письма";
        $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    if (!$mail->send()) {
        echo "Error: " . $mail->ErrorInfo;
    } else {
        echo "Message sent!";
    }
}
}
?>