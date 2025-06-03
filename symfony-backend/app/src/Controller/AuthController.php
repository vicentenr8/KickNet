<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    private $mailer;
    private $urlGenerator;

    public function __construct(MailerInterface $mailer, UrlGeneratorInterface $urlGenerator)
    {
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
    }

    #[Route('/login-check', name: 'api_login_check', methods: ['GET'])]
    public function loginCheck(): JsonResponse
    {
        return new JsonResponse(['message' => 'Send your credentials']);
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['username'], $data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'Invalid email address'], Response::HTTP_BAD_REQUEST);
        }

        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'Email already in use'], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setRegisterDate(new \DateTime());
        $user->setVerified(false);

        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        // Generar token de activación
        $activationToken = bin2hex(random_bytes(32));
        $user->setActivationToken($activationToken);

        $em->persist($user);
        $em->flush();

        // Enviar email con link de activación
        $activationUrl = $this->urlGenerator->generate('api_verify_account', ['token' => $activationToken], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@tudominio.com', 'Tu App'))
            ->to($user->getEmail())
            ->subject('Activa tu cuenta')
            ->htmlTemplate('emails/activation.html.twig')
            ->context([
                'username' => $user->getUsername(),
                'activationUrl' => $activationUrl,
            ]);

        $this->mailer->send($email);

        return new JsonResponse(['message' => 'User registered successfully. Check your email to activate your account.'], Response::HTTP_CREATED);
    }

    #[Route('/verify-account/{token}', name: 'api_verify_account', methods: ['GET'])]
    public function verifyAccount(string $token, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->findOneBy(['activationToken' => $token]);

        if (!$user) {
            return new JsonResponse(['error' => 'Token inválido'], Response::HTTP_BAD_REQUEST);
        }

        $user->setVerified(true);
        $user->setActivationToken(null);
        $em->flush();

        return new JsonResponse(['message' => 'Cuenta activada correctamente. Ya puedes iniciar sesión.']);
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return new JsonResponse(['error' => 'Email y contraseña requeridos'], 400);
        }

        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], 404);
        }

        if (!$user->isVerified()) {
            return new JsonResponse(['error' => 'Cuenta no activada. Revisa tu correo.'], 403);
        }

        if (!$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Contraseña incorrecta'], 401);
        }

        $token = $jwtManager->create($user);

        return new JsonResponse([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername()
            ]
        ]);
    }
}
