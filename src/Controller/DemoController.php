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
     * @Route("/project/demo/new/{substanceColor}" , name="newDemoProjectPath")
     */

    
    function newProjectDemo(UserInterface $user, EntityManagerInterface $manager, $substanceColor){
            
        $user->addProject($project = new Project());   

        $project->setProjectName('projet top secret');
        $project->setDailyLimit('dailyLimit3');
        $project->setTotalLimit('midTerm');
        $project->setSubstanceColor($substanceColor);
    
         



        $project->addWeek($week = new Week());

         //simulate a start at day 2

         $project->setCurrentDay(1);

      
             
            $week->addDay($firstDay = new Day());

            //simulate the daily count of the first day at 20 minutes

            $firstDay->setDailyCount(1200);
            $firstDay->setNumberOfPauses(2);

            $week->addDay(new Day());
            $week->addDay(new Day());
            $week->addDay(new Day());
            $week->addDay(new Day());
            $week->addDay(new Day());
            $week->addDay(new Day());


            $user->addNotification($notification = new Notification());

            $notification->setContent("Dommage...tu n'as pas atteint ton compte journalier, pour le projet nommé : projet top secret. On va dire que tu as de la chance...tu ne perds de points de compétences, vu que ton compteur est déjà à 0...");


            $manager->persist($user);
 
            $manager->flush();



           return $this->redirectToRoute('demoProjectPath', ['projectId' => $project->getId()] );

    }



    /**
     * @Route("/project/demo/{projectId}" , name="demoProjectPath")
     */

    

    function showDemoProject($projectId, UserInterface $user, EntityManagerInterface $manager){

        
        $project = $this->getDoctrine()->getRepository(Project::class)->find($projectId);

        $currentWeek = $project->currentWeek;

        $currentDay = $project->currentDay;

        $substanceColor = $project->getSubstanceColor();

        

        $dailyLimit = $project->getDailyLimit();

        $totalLimit = $project->getTotalLimit();

        $dailyCount = $project->weeks[$currentWeek]->days[$currentDay]->getDailyCount();

        $totalCount = $project->getTotalCount();

        $dailyCountDone = $project->getDailyCountDone();

        $totalCountDone = $project->getTotalCountDone();

        $numberOfPauses = $project->weeks[$currentWeek]->days[$currentDay]->getNumberOfPauses();


        $pageReloaded = 'false';

        $interval = 'off';


        //comparison with last day

        
       if ($project->currentDay != 0){

            $lastDay  = $project->weeks[$currentWeek]->days[$currentDay - 1];

        } else if($currentDay == 0){

          if($currentWeek != 0){

            $lastDay = $project->weeks[$currentWeek - 1]->days[6];
       
          } else if ($currentWeek == 0){

           $lastDay = null;
          } 

        } 
        
        if($lastDay == null){

            $comparison = 'firstDay';

            $lastDayCount = 1;


        } else{

            $lastDayCount = $lastDay->getDailyCount();


            if($dailyCount != 0){
                
                //We check how far from 1 the difference between the two values is.
                //If the number is higher than 1, then the dailycount is bigger than the day before
                //If negative, then the last day lost
                 
                if($lastDayCount == 0){
    
                    $comparison = 100;
                    
                } else{
                    $comparison = (($dailyCount/$lastDayCount)-1)*100;
                }
    
            }else if($dailyCount == 0){
    
                if($lastDayCount != 0){
    
                    $comparison = -100;
                } else if($lastDayCount == 0){
                    $comparison = 'zerozero';
                }
            } 
 
         }
        

        if(isset($_POST['pageReload'])){

            $pageReloaded = 'true';

            if($_POST['interval'] == 'on'){

                $interval = 'on';
            }


        }
 
   
           $weeks = $project->getWeeks();

           $bestDailyCount = 0;
         
           $totalNumberOfPauses = 0;

           foreach($weeks as $week){
           
            $projectDays = $week->getDays();

            foreach($projectDays as $day){

                $numberOfPauses = $day->getNumberOfPauses();

                $totalNumberOfPauses += $numberOfPauses;

                if( $day->getDailyCount() > $bestDailyCount){

                    $bestDailyCount =  $day->getDailyCount();
                }
                
            }

        }


        return $this->render('demo/project.html.twig', ['pageReloaded' => $pageReloaded, 'interval' => $interval,  'user' => $user, 'username' => $user->getUserName(), 'projectId' => $projectId ,  'projectName' => $project->getProjectName(), 'dailyCount' => $dailyCount, 'dailyLimit' => $dailyLimit , 'dailyCountDone' => $dailyCountDone  , 'totalCount' => $totalCount, 'totalCountDone' => $totalCountDone ,  'totalLimit' => $totalLimit, 'substanceColor' => $substanceColor, 'comparison' => $comparison , 'lastDayCount' => $lastDayCount, 'numberOfPauses'  => $numberOfPauses, 'bestDailyCount' => $bestDailyCount]);
   
    }



  

    /**
     * @Route("/d1" , name="demoStep1Path")
     */


     function demo1Step1(){


        return $this->render('demo/demoStep1.html.twig');



     }


}
