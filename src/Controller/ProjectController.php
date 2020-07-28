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





class ProjectController extends AbstractController
{
    /**
     * @Route("/project/new", name="newProjectPath")
     */

    public function new(UserInterface $user, EntityManagerInterface $manager)
    {        
        $user->addProject($project = new Project());

        if(isset($_POST['submit'])){    
                
        $project->setProjectName($_POST['projectName']);

        $project->setDailyLimit($_POST['dailyLimit']);
        $project->setTotalLimit($_POST['totalLimit']);

        $project->addWeek($week = new Week());

             
            $week->addDay(new Day());
            $week->addDay(new Day());
            $week->addDay(new Day());
            $week->addDay(new Day());
            $week->addDay(new Day());
            $week->addDay(new Day());
            $week->addDay(new Day());
                    

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

        $currentWeek = $project->currentWeek;

        $currentDay = $project->currentDay;

        

        $dailyLimit = $project->getDailyLimit();

        $totalLimit = $project->getTotalLimit();

        $dailyCount = $project->weeks[$currentWeek]->days[$currentDay]->getDailyCount();

        $totalCount = $project->getTotalCount();

        $dailyCountDone = $project->getDailyCountDone();

        $totalCountDone = $project->getTotalCountDone();


        

        return $this->render('project/show.html.twig', ['user' => $user, 'username' => $user->getUserName(), 'projectId' => $projectId ,  'projectName' => $project->getProjectName(), 'dailyCount' => $dailyCount, 'dailyLimit' => $dailyLimit , 'dailyCountDone' => $dailyCountDone  , 'totalCount' => $totalCount, 'totalCountDone' => $totalCountDone ,  'totalLimit' => $totalLimit]);

    }


    
     /**
     * @Route("/project/dailyCountDone", name="dailyCountDonePath")
     */

    public function dailyCountDone( EntityManagerInterface $manager, UserInterface $user){


        $project = $this->getDoctrine()->getRepository(Project::class)->find($_POST['projectId']);

        $project->setDailyCountToDone();

        
        $user->addNotification($notification = new Notification());

        $notification->setContent('Super, tu as atteint ton compte journalier, pour le projet nommé '. $project->getProjectName());

        
        $manager->persist($user);

        $manager->flush();

        return new JsonResponse(['data' => 'ok']);
    }


     /**
     * @Route("/totalCountDone" , name="totalCountDonePath")
     */

    public function totalCountDone( EntityManagerInterface $manager, UserInterface $user){

        $project = $this->getDoctrine()->getRepository(Project::class)->find($_POST['projectId']);

        $project->setTotalCountToDone();


        $user->addNotification($notification = new Notification());


        $notification->setContent('Super, tu es venu à bout de ton projet , intitulé : ' . $project->getProjectName() );

        
        $manager->persist($user);

        $manager->flush();

        return $this->redirectToRoute('admin');
        
    }


    /**
     * @Route("/project/showall" , name="showAllProjectsPath")
     */


    public function showAll(UserInterface $user){


        $projects = $user->getProjects();
        

        return $this->render('project/showall.html.twig', ['projects' => $projects] );

    }


    
    /**
     * @Route("/project/storeCounts" , name="storeCountsPath")
     */


    public function storeCounts(EntityManagerInterface $manager, UserInterface $user){


        if(isset($_POST['projectId'])){

            $project = $this->getDoctrine()->getRepository(Project::class)->find($_POST['projectId']);


            $currentWeek = $project->currentWeek;

            $currentDay = $project->currentDay;


            $project->totalCount = $_POST['totalCount'];

            $project->weeks[$currentWeek]->days[$currentDay]->dailyCount = $_POST['dailyCount'];

            $manager->persist($user);

            $manager->flush();

            return new JsonResponse(['count' => $project->weeks[$currentWeek]->days[$currentDay]->dailyCount ]);            
            
        }
       
    }


    
    /**
     * @Route("/project/nextDay" , name="nextDayPath")
     */


    public function nextDayPath(EntityManagerInterface $manager, UserInterface $user){


        if(isset($_POST['projectId'])){

            $project = $this->getDoctrine()->getRepository(Project::class)->find($_POST['projectId']);
            

            if($project->currentDay == 6){

                $project->currentDay = 0;

                $project->currentWeek += 1;

                $project->addWeek($week = new Week());                    
                  $week->addDay(new Day());
                  $week->addDay(new Day());
                  $week->addDay(new Day());
                  $week->addDay(new Day());
                  $week->addDay(new Day());
                  $week->addDay(new Day());
                  $week->addDay(new Day());
                  

                 $manager->persist($user);
                 $manager->flush();

                 return new JsonResponse(['day' => $project->currentDay]);            


            } else{

                $project->currentDay += 1;

                
                $manager->persist($user);
                $manager->flush();

                return new JsonResponse(['day' => $project->currentDay]);            


            }


        }



    }


    
    
    /**
     * @Route("/project/graph/show/{projectId}" , name="showGraphPath")
     */

    function showGraph($projectId){


        $project = $this->getDoctrine()->getRepository(Project::class)->find($projectId);

        $displayedWeek = $project->currentWeek;

        $day1 = $project->weeks[$displayedWeek]->days[0]->getDailyCount(); 
        $day2 = $project->weeks[$displayedWeek]->days[1]->getDailyCount(); 
        $day3 = $project->weeks[$displayedWeek]->days[2]->getDailyCount(); 
        $day4 = $project->weeks[$displayedWeek]->days[3]->getDailyCount(); 
        $day5 = $project->weeks[$displayedWeek]->days[4]->getDailyCount(); 
        $day6 = $project->weeks[$displayedWeek]->days[5]->getDailyCount(); 
        $day7 = $project->weeks[$displayedWeek]->days[6]->getDailyCount(); 
        

           return $this->render('project/graph.html.twig', ['projectId' => $projectId , 'displayedWeek' => $displayedWeek , 'nextWeekButton' => 'no' ,'day1' => $day1 , 'day2' => $day2,'day3' => $day3,'day4' => $day4,'day5' => $day5,'day6' => $day6,'day7' => $day7]);


    }


    /**
     * @Route("/project/graph/nextWeek" , name="graphNextWeekPath")
     */

    function graphNextWeek(){


        if($_POST['projectId']){

            $displayedWeek = $_POST['displayedWeek'];
            

        $project = $this->getDoctrine()->getRepository(Project::class)->find($_POST['projectId']);


            $newDisplayedWeek = $displayedWeek + 1;
            
        
            $day1 = $project->weeks[$newDisplayedWeek]->days[0]->getDailyCount(); 
            $day2 = $project->weeks[$newDisplayedWeek]->days[1]->getDailyCount(); 
            $day3 = $project->weeks[$newDisplayedWeek]->days[2]->getDailyCount(); 
            $day4 = $project->weeks[$newDisplayedWeek]->days[3]->getDailyCount(); 
            $day5 = $project->weeks[$newDisplayedWeek]->days[4]->getDailyCount(); 
            $day6 = $project->weeks[$newDisplayedWeek]->days[5]->getDailyCount(); 
            $day7 = $project->weeks[$newDisplayedWeek]->days[6]->getDailyCount(); 


           return new JsonResponse(['newDisplayedWeek' => $newDisplayedWeek , 'day1' => $day1 , 'day2' => $day2,'day3' => $day3,'day4' => $day4,'day5' => $day5,'day6' => $day6,'day7' => $day7]);
            
       

        }
    }



    
    /**
     * @Route("/project/graph/lastWeek" , name="graphLastWeekPath")
     */

    function graphLastWeek(){


        if($_POST['projectId']){

            $displayedWeek = $_POST['displayedWeek'];



        $project = $this->getDoctrine()->getRepository(Project::class)->find($_POST['projectId']);
         
        

          $newDisplayedWeek = $displayedWeek -1;

            
        $day1 = $project->weeks[$newDisplayedWeek]->days[0]->getDailyCount(); 
        $day2 = $project->weeks[$newDisplayedWeek]->days[1]->getDailyCount(); 
        $day3 = $project->weeks[$newDisplayedWeek]->days[2]->getDailyCount(); 
        $day4 = $project->weeks[$newDisplayedWeek]->days[3]->getDailyCount(); 
        $day5 = $project->weeks[$newDisplayedWeek]->days[4]->getDailyCount(); 
        $day6 = $project->weeks[$newDisplayedWeek]->days[5]->getDailyCount(); 
        $day7 = $project->weeks[$newDisplayedWeek]->days[6]->getDailyCount(); 




           return new JsonResponse(['newDisplayedWeek' => $newDisplayedWeek , 'day1' => $day1 , 'day2' => $day2,'day3' => $day3,'day4' => $day4,'day5' => $day5,'day6' => $day6,'day7' => $day7]);
        

        }
    }

}
