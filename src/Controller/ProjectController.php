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


        $pageReloaded = 'false';

        $interval = 'off';


        if(isset($_POST['pageReload'])){

            $pageReloaded = 'true';

            if($_POST['interval'] == 'on'){

                $interval = 'on';
            }



        }


        

        return $this->render('project/show.html.twig', ['pageReloaded' => $pageReloaded, 'interval' => $interval,  'user' => $user, 'username' => $user->getUserName(), 'projectId' => $projectId ,  'projectName' => $project->getProjectName(), 'dailyCount' => $dailyCount, 'dailyLimit' => $dailyLimit , 'dailyCountDone' => $dailyCountDone  , 'totalCount' => $totalCount, 'totalCountDone' => $totalCountDone ,  'totalLimit' => $totalLimit]);

    }


    
     /**
     * @Route("/notifPin/reset", name="resetNotifPinPath")
     */

    public function resetNotifPin( EntityManagerInterface $manager, UserInterface $user){
                
        $user->setPinCount(0);

        $manager->persist($user);

        $manager->flush();

        return new JsonResponse(['pinCount' => $user->getPinCount()]);

    
    }


         /**
     * @Route("/notifPin/getPin", name="getNotifPinPath")
     */

    public function getNotifPinPath( EntityManagerInterface $manager, UserInterface $user){
                
  
        return new JsonResponse(['pinCount' => $user->getPinCount()]);

    
    }





    
    
     /**
     * @Route("/notifs/viewed", name="setNotifToViewedPath")
     */

    public function setNotifToViewed( EntityManagerInterface $manager, UserInterface $user){

        if(isset($_POST["notifId"])){

            $notif = $this->getDoctrine()->getRepository(Notification::class)->find($_POST['notifId']);

            $notif->setViewed(true);

            $unviewedNotifs = $this->getDoctrine()->getRepository(Notification::class)-> findBy(['viewed' => false]);
            
            
            $notifContentArray = [];

            $notifIdArray = [];

            
            
            foreach($unviewedNotifs as $notification){

                if($notification != $notif){

                    $notifContentArray[] = $notification->getContent();

                    $notifIdArray[] = $notification->getId();


                }
                
            }


    
            $manager->persist($user);

            $manager->flush();




            return new JsonResponse(['notifContentArray' =>  $notifContentArray, "notifIdArray" =>$notifIdArray ]); 
            
            

        }


    




    }





    
     /**
     * @Route("/notifs/getNotifs", name="getNotifsPath")
     */

    public function getNotif( EntityManagerInterface $manager, UserInterface $user){



        $unviewedNotifs = $this->getDoctrine()->getRepository(Notification::class)-> findBy(['viewed' => false]);

        $notifContentArray = [];


        $notifIdArray = [];





        foreach($unviewedNotifs as $notif){

        $notifContentArray[] = $notif->getContent();

        $notifIdArray[] = $notif->getId();


        }







        return new JsonResponse(['notifContentArray' => $notifContentArray, 'notifIdArray' =>  $notifIdArray]);

    }


    
     /**
     * @Route("/project/dailyCountDone", name="dailyCountDonePath")
     */

    public function dailyCountDone( EntityManagerInterface $manager, UserInterface $user){


        $project = $this->getDoctrine()->getRepository(Project::class)->find($_POST['projectId']);

        $project->setDailyCountToDone();

        
        $user->addNotification($notification = new Notification());

        $pinCount = $user->getPinCount();

        $user->setPinCount($pinCount+1);

        $user->setCompetencyPoints($user->getCompetencyPoints()+50);


        $notification->setContent('Super, tu as atteint ton compte journalier, pour le projet nommé '. $project->getProjectName() . 'tu gagnes 50 points de compétence!');
       
        
        $manager->persist($user);

        $manager->flush();

        return new JsonResponse(['pin' => $user->getPinCount()]);
    }




    
     /**
     * @Route("/endofday" , name="endOfDayPath")
     */

    public function endOfDay( EntityManagerInterface $manager, MailerInterface $mailer, Request $request){

        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        foreach($users as $user){

            
            $projects = $user->getProjects();

            foreach($projects as $project){

                


                if($project->getDailyCountDone() == 'false'){


                    if($user->getMailing() == 'on'){


                        $email = new Email();

                        $email->from($user->getMail())
                        ->to($user->getAssociatedMail())                     
                        ->subject("Juste pour t'informer..." )
                        ->text("...")
                        ->html("bonjour, c'est : " . $user->getMail(). "<p>Juste pour t'informer qu'aujourd'hui , <br> je n'ai pas assez bossé sur mon projet : " . $project->getProjectName() . "</p>");
                    
            
                         $mailer->send($email);
    

                    }

                      //For each project, if the dailyCount is not done, send a mail


           

                  $user->addNotification($notification = new Notification());

                  $pinCount = $user->getPinCount();
                  
                  $user->setPinCount($pinCount+1);

                  if( $user->getCompetencyPoints() != 0){

                      if( $user->getCompetencyPoints() >= 50){

                       $user->setCompetencyPoints($user->getCompetencyPoints()-50);

                       $notification->setContent("Dommage...tu n'as pas atteint ton compte journalier, pour le projet nommé". $project->getProjectName() . "tu perds 50 points de compétence!");


                      } else{

                        $user->setCompetencyPoints(0);

                        $notification->setContent("Dommage...tu n'as pas atteint ton compte journalier, pour le projet nommé". $project->getProjectName() . "tu perds 50 points de compétence, et ton compteur est donc à 0...!");
 
                      }

                  } else{

                    $notification->setContent("Dommage...tu n'as pas atteint ton compte journalier, pour le projet nommé". $project->getProjectName() . "on va dire que tu as de la chance...tu ne perds de points de compétences, vu que ton compteur est déjà à 0...");


                  }


       }


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
                      

                } else{

                    $project->currentDay += 1;

            }
         }
        //end of projects loop

        if($user->getCompetencyPoints() >= 50 && $user->getCompetencyPoints() < 100){

            $user->setLevel('élément à fort potentiel');

        } else  if($user->getCompetencyPoints() >= 100  && $user->getCompetencyPoints() < 150){

            $user->setLevel('loup de Wall Street');
        } else if($user->getCompetencyPoints() >= 150){

            $user->setLevel('Jeff Bezzos');
        } else {

            $user->setLevel('novice');
        }

        

        }
        //end of users loop


    
        $manager->persist($user);

        $manager->flush();



        return new JsonResponse(['update' => 'ok']);

        


    }
     /**
     * @Route("/totalCountDone" , name="totalCountDonePath")
     */

    public function totalCountDone( EntityManagerInterface $manager, UserInterface $user){

        $project = $this->getDoctrine()->getRepository(Project::class)->find($_POST['projectId']);

        $project->setTotalCountToDone();

        $project->totalCount = $project->getTotalLimit();


        $user->addNotification($notification = new Notification());

        $user->setCompetencyPoints($user->getCompetencyPoints()+1000);

        
        $pinCount = $user->getPinCount();

        $pinCount++;


        $notification->setContent('Super, tu es venu à bout de ton projet , intitulé : ' . $project->getProjectName() . 'tu gagnes 1000 points de compétence!!!' );
        

        
        $manager->persist($user);

        $manager->flush();

        return $this->redirectToRoute('showAllDoneProjectsPath');
        
    }





    

    /**
     * @Route("/project/delete/{projectId}/{redirect}" , name="deleteProjectPath")
     */


    public function deleteProject(UserInterface $user, $projectId, EntityManagerInterface $manager, $redirect){



        $project = $this->getDoctrine()->getRepository(Project::class)->find($projectId);


        $manager->remove($project);


        $manager->flush();


         
        if($redirect == 'allProjects'){

            return $this->redirectToRoute('showAllProjectsPath');

        } else {

            return $this->redirectToRoute('showAllDoneProjectsPath');

        }

        



    
    }





    /**
     * @Route("/project/showall" , name="showAllProjectsPath")
     */


    public function showAll(UserInterface $user){
       

        $projects = $this->getDoctrine()->getRepository(Project::class)->findBy(['user' => $user , 'totalCountDone' => 'false' ]);
        

        return $this->render('project/showall.html.twig', ['projects' => $projects] );

    }
     


    

    /**
     * @Route("/project/showallDone" , name="showAllDoneProjectsPath")
     */


    public function showAllDoneProjects(UserInterface $user){


        $projects = $this->getDoctrine()->getRepository(Project::class)->findBy(['user' => $user , 'totalCountDone' => 'true' ]);
    
        
        return $this->render('project/doneProjects.html.twig', ['projects' => $projects] );
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
     * @Route("/project/notifs/show" , name="showNotifsPath")
     */

    function showNotifs(UserInterface $user){


        $notifs = $user->getNotifications();


         $content = [];

         foreach($notifs as $notif){

            $content[] = $notif->getContent();
         }


             

    return new JsonResponse(['notifs' => $content]);


    
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
