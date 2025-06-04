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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Service\MailerService;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    #[Route('/login-check', name: 'api_login_check', methods: ['GET'])]
    public function loginCheck(): JsonResponse
    {
        return new JsonResponse(['message' => 'Send your credentials']);
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        MailerService $mailerService,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
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
        $activationUrl = $urlGenerator->generate(
            'api_verify_account',
            ['token' => $activationToken],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $mailerService->sendVerificationEmail($user->getEmail(), $activationUrl);

        return new JsonResponse([
            'message'       => 'User registered successfully. Check your email to activate your account.',
            'activationUrl' => $activationUrl
        ], Response::HTTP_CREATED);
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
    public function login(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return new JsonResponse(['error' => 'Email y contraseña requeridos'], Response::HTTP_BAD_REQUEST);
        }

        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }

        if (!$user->isVerified()) {
            return new JsonResponse(['error' => 'Cuenta no activada. Revisa tu correo.'], Response::HTTP_FORBIDDEN);
        }

        if (!$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Contraseña incorrecta'], Response::HTTP_UNAUTHORIZED);
        }

        // Creamos el payload para el JWT con "email" y "roles"
        $payload = [
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ];
        $token = $jwtManager->createFromPayload($user, $payload);

        return new JsonResponse([
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => [
                'id'       => $user->getId(),
                'email'    => $user->getEmail(),
                'username' => $user->getUsername(),
            ]
        ]);
    }
}