<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Email;
use App\Repository\EventRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Project;





class MailerController extends AbstractController
{
    /**
     * @Route("/mailer/{projectId}", name="mailerPath")
     */
    public function mailer(MailerInterface $mailer, Request $request, UserInterface $user, $projectId)
    {

        $email = new Email();

        $projectName = $this->getDoctrine()->getRepository(Project::class)->find($projectId)->getProjectName();

        $email->from($user->getMail())
            ->to($user->getAssociatedMail())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject("Juste pour t'informer..." )
            ->text("...")
            ->html("<p>Juste pour t'informer qu'aujourd'hui , <br> je n'ai pas assez bossé sur mon projet : " . $projectName . "</p>");
        

        $mailer->send($email);

        return $this->redirectToRoute('profilePagePath');

    }
}
