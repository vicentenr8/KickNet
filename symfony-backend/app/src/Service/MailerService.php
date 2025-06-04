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
    <div style="font-family: Arial, sans-serif; color: #333; background-color: #f9f9f9; padding: 20px;">
        <div style="max-width: 600px; margin: auto; background-color: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 30px;">
            <div style="text-align: center; margin-bottom: 30px;">
                </a>
            </div>
            <h2 style="color: #2c3e50;">Bienvenido al Museo de la Cartuja</h2>
            <p>Gracias por registrarte. Para completar tu registro y verificar tu cuenta, por favor haz clic en el siguiente enlace:</p>
            <p style="text-align: center; margin: 30px 0;">
                <a href="{$url}" style="background-color: #2c3e50; color: white; padding: 12px 20px; border-radius: 4px; text-decoration: none;">Verificar mi cuenta</a>
            </p>
            <p>Si no te registraste en nuestro sitio, puedes ignorar este correo.</p>
            <p style="margin-top: 40px; font-size: 12px; color: #999;">© Museo de la Cartuja – Todos los derechos reservados</p>
        </div>
    </div>
    HTML;

    $email = (new Email())
        ->from('Kicknet <kicknet@gmail.com>')
        ->to($to)
        ->subject('Verifica tu cuenta - Kicknet')
        ->html($html);

    $this->mailer->send($email);
}

}
