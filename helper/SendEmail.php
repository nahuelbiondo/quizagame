<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

include_once('vendor/PHPMailer/src/Exception.php');
include_once('vendor/PHPMailer/src/PHPMailer.php');
include_once('vendor/PHPMailer/src/SMTP.php');
class SendEmail{

    private $mailer;

    public function __construct() {
        $this->mailer = $this->getMailer();
    }

    private function getMailer() {
        $config = parse_ini_file('configuration/config.ini');
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 0;                     
        $mail->isSMTP();                                        
        $mail->Host       = $config['mailerhost'];             
        $mail->SMTPAuth   = true;                               
        $mail->Username   = $config['mailerusername'];           
        $mail->Password   = $config['mailerpassword'];           
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        
        $mail->Port       = $config['mailerport'];

        return $mail;
    }

   
    public function sendVerificationEmail($email, $nombre, $usuario, $codigoVerificacion) {
        try {
            $this->mailer->setFrom('quizgame088@gmail.com', 'QuizGame');
            $this->mailer->addAddress($email); 
            $this->mailer->isHTML(true);        
            $this->mailer->Subject = "Hola $nombre!";
            $body = '
            <html>
            <head>
                <style>
                    .button {
                        background-color: #1c6d93;
                        border: none;
                        color: #fcfcfc;
                        padding: 10px 20px;
                        text-align: center;
                        text-decoration: none;
                        display: inline-block;
                        font-size: 16px;
                        margin: 4px 2px;
                        cursor: pointer;
                        border-radius: 4px;
                    }
                    .link {
                        color: blue;
                    }
                </style>
            </head>
            <body>
                <h2>¡Verifique su cuenta!</h2>
                <p>Estimado/a ' . $usuario . ',</p>
                <p>Gracias por registrarse a QuizGame. Para poder activar su cuenta, necesitamos que haga clic en el botón a continuación para completar el proceso de verificación:</p>
                <a href="http://localhost/quizgame/auth/authenticate/token=' . $codigoVerificacion . '&usuario=' . $usuario . '" class="button">Verificar Cuenta</a>
                <p>Si no puede hacer clic en el botón, copie y pegue el siguiente enlace en su navegador:</p>
                <p class="link"><a href="http://localhost/quizgame/auth/authenticate/token=' . $codigoVerificacion . '&usuario=' . $usuario . '">http://localhost/quizgame/auth/authenticate/token=' . $codigoVerificacion . '&usuario=' . $usuario . '</a></p>
                <p>¡Esperamos verlo/a pronto!</p>
                <p>Atentamente,<br>El equipo de QuizGame</p>
            </body>
            </html>';
            
            $this->mailer->Body = $body;
            $this->mailer->send();
        } catch (Exception $e) {
            echo "El mensaje no pudo ser enviado: {$this->mailer->ErrorInfo}";
        }
    }
}
