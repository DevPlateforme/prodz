<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Notification;
use App\Entity\Project;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Repository\ProjectRepository;
use App\Repository\NotificationRepository;





class ProjectController extends AbstractController
{
    /**
     * @Route("/project/new", name="newProjectPath")
     */

    public function new(UserInterface $user, EntityManagerInterface $manager)
    {        
        $user->projects->add($project = new Project());

        if(isset($_POST['submit'])){    
                
        $project->setProjectName($_POST['projectName']);

        $project->setDailyLimit($_POST['dailyLimit']);
        $project->setTotalLimit($_POST['totalLimit']);



        $manager->persist($user);

        $manager->flush();
        
        return $this->redirectToRoute('showProjectPath', ['projectId' =>  $project->getId()]);
        }
        

        return $this->render('project/new.html.twig', [
            'controller_name' => 'ProjectController',
        ]);
    }

     /**
     * @Route("/project/show/{projectId}", name="showProjectPath")
     */

    public function show($projectId, UserInterface $user){

        $project = $this->getDoctrine()->getRepository(Project::class)->find($projectId);

        $dailyLimit = $project->getDailyLimit();

        $totalLimit = $project->getTotalLimit();

        $dailyCount = $project->getDailyCount();

        $totalCount = $project->getTotalCount();

        $dailyCountDone = $project->getDailyCountDone();

        $totalCountDone = $project->getTotalCountDone();

        

        return $this->render('project/show.html.twig', ['username' => $user->getUserName(), 'projectId' => $projectId ,  'projectName' => $project->getProjectName(), 'dailyCount' => $dailyCount, 'dailyLimit' => $dailyLimit , 'dailyCountDone' => $dailyCountDone  , 'totalCount' => $totalCount, 'totalCountDone' => $totalCountDone ,  'totalLimit' => $totalLimit]);

    }


    
     /**
     * @Route("/project/dailyCountDone", name="dailyCountDonePath")
     */

    public function dailyCountDone( EntityManagerInterface $manager, UserInterface $user){

        $user->getNotifications()->add($notification = new Notification());

        $project = $this->getDoctrine()->getRepository(Project::class)->find($_POST['projectId']);

        $project->setDailyCountToDone();

        $notification->setContent('Bravo, tu as atteint ton compte journalier!!');
        
        $manager->persist($user);

        $manager->flush();

        return new JsonResponse(['data' => 'ok']);
    }


     /**
     * @Route("/totalCountDone" , name="totalCountDonePath")
     */

    public function totalCountDone( EntityManagerInterface $manager, UserInterface $user){

        $user->getNotifications()->add($notification = new Notification());

        $notification->setContent('Bravo, tu as réalisé ton projet!!');

        $manager->persist($user);

        $manager->flush();

        return new JsonResponse(['data' => 'ok']);

    
        
    }
}
