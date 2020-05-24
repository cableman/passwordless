<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\LoginService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class HomeController extends AbstractController
{
    protected $userRepository;
    protected $loginService;
    protected $entityManager;
    protected $security;
    protected $guardHandler;

    public function __construct(UserRepository $userRepository, LoginService $loginService, EntityManagerInterface $entityManager, Security $security, GuardAuthenticatorHandler $guardHandler)
    {
        $this->userRepository = $userRepository;
        $this->loginService = $loginService;
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->guardHandler = $guardHandler;
    }

    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        return $this->render('home.html.twig');
    }

    /**
     * @Route("/personal", name="personal")
     */
    public function personal()
    {
        return $this->render('personal.html.twig');
    }

    /**
     * @Route("/login", methods={"get"}, name="login")
     */
    public function login()
    {
        return $this->render('login.html.twig');
    }

    /**
     * @Route("/redirect", name="redirect")
     */
    public function loginRedirect(Request $request) {
        // This redirect step is only here to remove the token from the users URL in the browser.
        return new RedirectResponse('personal', 302);
    }

    /**
     * @Route("/login", methods={"post"}, name="loginPost")
     */
    public function postlogin(Request $request, \Swift_Mailer $mailer)
    {
        $email = $request->request->get('_mail');
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (false === $user) {
            // Never tell a attacker that the user was not found.
            return  $this->render('mail.html.twig');
        }

        // Generate user token and save the user.
        $this->loginService->addToken($user);
        $this->entityManager->flush();

        $url = $request->getSchemeAndHttpHost() . '/redirect?token=' . $user->getAuthToken();

        $message = new \Swift_Message('Hello Email');
        $message->setFrom('no-reply@local.itkdev.dk')
            ->setTo($email)
            ->setBody(
                $this->renderView('magic.html.twig', ['url' => $url]),
                'text/html'
            );

        $mailer->send($message);

        return  $this->render('mail.html.twig');
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        return;
    }
}