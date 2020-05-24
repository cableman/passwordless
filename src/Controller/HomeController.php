<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        return $this->render('home.html.twig');
    }

    /**
     * @Route("/personal-home-page", name="personal")
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
     * @Route("/login", methods={"post"}, name="loginPost")
     */
    public function postlogin(Request $request, \Swift_Mailer $mailer)
    {
        $mail = $request->request->get('_mail');

        $url = 'test';

        $message = new \Swift_Message('Hello Email');
        $message->setFrom('no-reply@local.itkdev.dk')
            ->setTo($mail)
            ->setBody(
                $this->renderView('magic.html.twig', ['url' => $url]),
                'text/html'
            )
        ;

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