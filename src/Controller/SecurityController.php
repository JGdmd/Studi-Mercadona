<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\DriverException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/auth', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/login.html.twig', ['last_username' => $lastUsername, 'error' => $error, 'title' => 'Login']);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout()
    {
        return $this->redirectToRoute('app_login');
    }

    #[Route(path: '/2fa', name: '2fa_login')]
    public function check2fa(GoogleAuthenticatorInterface $authenticator, TokenStorageInterface $storage, EntityManagerInterface $entityManager): Response
    {

        $user = $this->getUser();

        if($user->isEnable2FA()) {

            return $this->render('security/2fa_login.html.twig', [
                'title' => 'Authentification 2FA'
            ]);
        } else {
            $qrCodeContent = $authenticator->getQRContent($storage->getToken()->getUser());
            try {
                $user->setEnable2FA(true);
                $entityManager->persist($user);
                $entityManager->flush();
            } catch(DriverException $e) {
                return $this->redirectToRoute('app_login');
            }
            $qrCode = 'https://chart.googleapis.com/chart?cht=qr&chs=100x100&chl='. $qrCodeContent;
            return $this->render('security/2fa_display.html.twig', [
                'qrCode' => $qrCode,
                'title' => 'Authentification 2FA'
               ]);
        }
    }
}
