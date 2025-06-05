<?php

namespace App\Service;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class MailerService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendVerificationEmail(string $to, string $url): void
    {
        $html = <<<HTML
        <div style="font-family: Arial, sans-serif; background-color: #000000; color: #eee; padding: 20px;">
            <div style="max-width: 600px; margin: auto; background-color: #121212; border-radius: 12px; padding: 30px; text-align: center;">
                
                <!-- Logo -->
                <div style="margin-bottom: 30px;">
                    <img src="logo.svg" alt="Kicknet Logo" style="max-width: 150px; height: auto; margin: 0 auto;" />
                </div>
                
                <!-- Heading -->
                <h2 style="font-weight: 700; font-size: 24px; color: #1DA1F2; margin-bottom: 15px;">¡Bienvenido a Kicknet!</h2>
                
                <!-- Text -->
                <p style="font-size: 16px; color: #ccc; margin-bottom: 30px;">
                    Gracias por registrarte. Para completar tu registro y verificar tu cuenta, por favor haz clic en el siguiente botón:
                </p>
                
                <!-- Button -->
                <a href="{$url}" 
                   style="
                    display: inline-block;
                    background-color: #1DA1F2; 
                    color: white; 
                    text-decoration: none; 
                    padding: 12px 30px; 
                    border-radius: 9999px; 
                    font-weight: 600;
                    font-size: 16px;
                    transition: background-color 0.3s ease;
                    "
                   onmouseover="this.style.backgroundColor='#0d8ddb';"
                   onmouseout="this.style.backgroundColor='#1DA1F2';"
                >
                    Verificar mi cuenta
                </a>
                
                <!-- Note -->
                <p style="color: #777; font-size: 14px; margin-top: 30px;">
                    Si no te registraste en nuestro sitio, puedes ignorar este correo.
                </p>
                
                <!-- Footer -->
                <p style="margin-top: 40px; font-size: 12px; color: #555;">
                    © 2025 Kicknet – Todos los derechos reservados
                </p>
            </div>
        </div>
        HTML;

        $email = (new Email())
            ->from('Kicknet <vicentenr8@gmail.com>')
            ->to($to)
            ->subject('Verifica tu cuenta - Kicknet')
            ->html($html);

        $this->mailer->send($email);
    }
}
