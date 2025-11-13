<?php

// Load Composer's autoloader
require 'vendor/autoload.php';

// Cargar variables de entorno desde el archivo .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Mailer class.
 * 
 * Permite enviar correos electrónicos desde tu correo de Gmail.
 * 
 * Es necesario generar una contraseña de aplicación en Google. Para crear
 * una aplicación y generar una contraseña, dirigete a:
 * https://myaccount.google.com/apppasswords
 * Ingresa el nombre de la aplicación y automáticamente generará una contraseña.
 * Es necesario que tengas activada la verificación en 2 pasos en tu cuenta.
 * 
 * Crea en la carpeta raíz del proyecto un archivo de variables de entorno `.env`
 * y coloca ahí los valores `MAIL_USERNAME`, que es tu correo electrónico de Gmail
 * y `MAIL_PASSWORD`, que es la contraseña generada en el punto anterior.
 */
class Mailer
{
    /**
     * Permite realizar acciones de PHPMailer.
     * @var PHPMailer Instancia de PHPMailer.
     */
    private $mail;

    /**
     * Correo electrónico de Gmail.
     * @var string
     */
    private $mail_username;

    /**
     * Contraseña generada para la aplicación.
     * @var string
     */
    private $mail_password;

    public function __construct()
    {
        $this->mail = new PHPMailer();

        /**
         * Variables de entorno.
         */
        $this->mail_username = $_ENV['MAIL_USERNAME'];
        $this->mail_password = $_ENV['MAIL_PASSWORD'];

        /**
         * Configuración del servidor SMTP.
         * Se está usando Gmail, pero se podría usar otro servicio de correo.
         * Las variables `Username` y `Password` se encuentran en el archivo `.env`.
         */
        $this->mail->isSMTP();
        $this->mail->Host       = 'smtp.gmail.com';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $this->mail_username;
        $this->mail->Password   = $this->mail_password;
        $this->mail->SMTPSecure = 'tls';
        $this->mail->Port       = 587;
        $this->mail->CharSet    = 'UTF-8';

        /**
         * Remitente por defecto. Los emails siempre se enviarán desde este
         * correo.
         */
        $this->mail->setFrom($this->mail_username, 'SEyBT TESCHA');
    }

    /**
     * Envía un email.
     *
     * @param  string $destinatario Correo electrónico destino.
     * @param  string $asunto       Asunto del correo.
     * @param  string $cuerpo       Contenido del correo (puede contener HTML).
     * @return bool                 `true` si el correo fue enviado, de lo contrario
     *                              retornará `false`.
     */
    public function enviar($destinatario, $asunto, $cuerpo)
    {
        try {
            $this->mail->addAddress($destinatario);

            /**
             * Contenido del correo.
             */
            $this->mail->isHTML(true);
            $this->mail->Subject = $asunto;
            $this->mail->Body    = $cuerpo;

            /**
             * Añade la imagen del la palomita verde al correo.
             */
            $img_path = __DIR__ . "/assets/green-check.png";
            $this->mail->addEmbeddedImage($img_path, "check");

            // Enviar correo
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            // return $this->mail->ErrorInfo;
            return false;
        }
    }
}
