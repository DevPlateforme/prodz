<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Notification;
use App\Entity\Project;
use App\Entity\Week;
use App\Entity\Day;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Repository\ProjectRepository;
use App\Repository\NotificationRepository;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

//mailing
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;








class DemoController extends AbstractController
{
   
       
    /**
     * @Route("/project/demo/new" , name="newDemoProjectPath")
     */

    


    function newProjectDemo(){


        return $this->render('demo/newProject.html.twig');

    }
    



    /**
     * @Route("/project/demo" , name="demoProjectPath")
     */

    

    function demoProject(){


        if(isset($_POST["projectName"])){

                
             $projectName = $_POST['projectName'];

             $substanceColor = $_POST['substanceColor'];

            
           return $this->render("demo/project.html.twig", ['substanceColor' => $substanceColor , 'projectName' => $projectName]);

          
        } 

        return new JsonResponse(['erreur' => "il y'a eu un petit soucis.."]);

    }




    /**
     * @Route("/project/demonotif/{notifNumber}" , name="demoNotifPath")
     */



    function demoNotifPath(UserInterface $user, EntityManagerInterface $manager, $notifNumber){

        if($notifNumber == 0){

            
         $user->addNotification($notification = new Notification());

         $notification->setContent("dommage...tu n'as pas atteint ton compte du jour  pour le projet nommé projet alpha, tu prend donc une pénalité de 50 points de compétence...on va dire que tu as de la chance, ton compte de points est déjà à zéro..."); 
         
         $user->setPinCount(1);




        }else if($notifNumber == 1){

         $user->addNotification($notification = new Notification());

         $notification->setContent('Super, tu as atteint ton compte journalier, pour le projet nommé "projet alpha", tu gagnes 50 points de compétence!'); 
         
         $user->setPinCount(1);

        
        } else if($notifNumber == 2){

        
         $user->addNotification($notification = new Notification());

         $notification->setContent('Super, tu as atteint ton compte journalier, pour le projet nommé "projet alpha", tu gagnes 50 points de compétence!'); 
         
         $user->addNotification($notification = new Notification());

         $notification->setContent("Bravo!! Tu gagnes 20 points de bonus de dynamique!! Tu as en effet travaillé 2 jours d'affilée sur ton projet"); 
        
         $user->setPinCount(2);


        } else if($notifNumber == 3){


          
        
         $user->addNotification($notification = new Notification());

         $notification->setContent('Super, tu as atteint ton compte journalier, pour le projet nommé "projet alpha", tu gagnes 50 points de compétence!'); 
         

         $user->addNotification($notification = new Notification());

         $notification->setContent("Bravo!! Tu gagnes 30 points de bonus de dynamique!! Tu as en effet travaillé 3 jours d'affilée sur ton projet"); 
        
        
         $user->addNotification($notification = new Notification());


         $notification->setContent("Bravo!! Tu monte de niveau!! Tu passe au niveau : élément à fort potentiel"); 
         
         $user->setPinCount(3);

        }
        
        

        $manager->persist($user);

        $manager->flush();


        return new JsonResponse(['ok' => 'notif created' ]);

        
  }


}
