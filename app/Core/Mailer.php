<?php

namespace Core;

class Mailer
{
    // IMPORTANTE: mueve estas credenciales a config/env lo antes posible
    private static $smtp_host = 'smtp.hostinger.com';
    private static $smtp_port = 587;
    private static $smtp_user = 'no-reply@loopcraft.com.co';
    private static $smtp_pass = 'M1ch3ll3$$$.';

    public static function send($to, $subject, $htmlContent)
    {
        $host = self::$smtp_host;
        $port = self::$smtp_port;
        $user = self::$smtp_user;
        $pass = self::$smtp_pass;

        $socket = fsockopen($host, $port, $errno, $errstr, 15);
        if (!$socket) {
            error_log("SMTP Error: $errstr ($errno)");
            return false;
        }

        self::read_smtp($socket);

        // EHLO
        fputs($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
        self::read_smtp($socket);

        // STARTTLS
        fputs($socket, "STARTTLS\r\n");
        self::read_smtp($socket);

        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

        // EHLO de nuevo
        fputs($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
        self::read_smtp($socket);

        // AUTH LOGIN
        fputs($socket, "AUTH LOGIN\r\n");
        self::read_smtp($socket);

        fputs($socket, base64_encode($user) . "\r\n");
        self::read_smtp($socket);

        fputs($socket, base64_encode($pass) . "\r\n");
        self::read_smtp($socket);

        // MAIL FROM / RCPT TO
        fputs($socket, "MAIL FROM:<$user>\r\n");
        self::read_smtp($socket);

        fputs($socket, "RCPT TO:<$to>\r\n");
        self::read_smtp($socket);

        // DATA
        fputs($socket, "DATA\r\n");
        self::read_smtp($socket);

        // Headers + body
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Fleet Manager <$user>\r\n";
        $headers .= "To: $to\r\n";
        $headers .= "Subject: $subject\r\n\r\n";

        fputs($socket, $headers . $htmlContent . "\r\n.\r\n");
        self::read_smtp($socket);

        // QUIT
        fputs($socket, "QUIT\r\n");
        fclose($socket);

        return true;
    }

    private static function read_smtp($socket)
    {
        $response = "";
        while (($str = fgets($socket, 515)) !== false) {
            $response .= $str;
            if (substr($str, 3, 1) === " ") {
                break;
            }
        }
        return $response;
    }
}
