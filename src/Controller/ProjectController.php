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

        if(isset($_POST['myNewProject'])){    

        $user->addProject($project = new Project());

                
        $project->setProjectName($_POST['projectName']);
        $project->setDailyLimit($_POST['dailyLimit']);
        $project->setTotalLimit($_POST['totalLimit']);
        $project->setSubstanceColor($_POST['substanceColor']);


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
     * @Route("/project/show", name="checkProjectPath")
     */

    public function checkProject(UserInterface $user){

       if(isset($_POST["projectName"])){

        $projects = $user->getProjects();

        foreach($projects as $project){

            if($project->getProjectName() == $_POST["projectName"]){

                return new JsonResponse(['validName' => false]);

            }

        }

        return new JsonResponse(['validName' => true]);



       }

       return $this->redirectToRoute('admin');
      
    }




     /**
     * @Route("/project/show/{projectId}", name="showProjectPath")
     */

    public function show($projectId, UserInterface $user){

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


        $pageReloaded = 'false';

        $interval = 'off';


        if(isset($_POST['pageReload'])){

            $pageReloaded = 'true';

            if($_POST['interval'] == 'on'){

                $interval = 'on';
            }


        }


        

        return $this->render('project/show.html.twig', ['pageReloaded' => $pageReloaded, 'interval' => $interval,  'user' => $user, 'username' => $user->getUserName(), 'projectId' => $projectId ,  'projectName' => $project->getProjectName(), 'dailyCount' => $dailyCount, 'dailyLimit' => $dailyLimit , 'dailyCountDone' => $dailyCountDone  , 'totalCount' => $totalCount, 'totalCountDone' => $totalCountDone ,  'totalLimit' => $totalLimit, 'substanceColor' => $substanceColor]);

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

            $notifications = $user->getNotifications();

            $notifToDelete;

            foreach($notifications as $notification){

                if($notification->getId() == $_POST["notifId"]){

                    $notifToDelete = $notification;
                }

            }            
            
            $notifContentArray = [];

            $notifIdArray = [];

            
            
            foreach($notifications as $notification){

                if($notification != $notifToDelete){

                    $notifContentArray[] = $notification->getContent();

                    $notifIdArray[] = $notification->getId();


                }
                
            }

        


            $manager->remove($notifToDelete);
            $manager->flush();

            $manager->persist($user);
            $manager->flush();




            return new JsonResponse(['notifContentArray' =>  $notifContentArray, "notifIdArray" =>$notifIdArray ]); 
            
            

        }


        return new JsonResponse(['error' =>  'error']); 



    




    }





    
     /**
     * @Route("/notifs/getNotifs", name="getNotifsPath")
     */

    public function getNotif( EntityManagerInterface $manager, UserInterface $user){

        $notifications = $user->getNotifications();

        $notifContentArray = [];


        $notifIdArray = [];



        foreach($notifications as $notification){

        $notifContentArray[] = $notification->getContent();

        $notifIdArray[] = $notification->getId();


        }





        return new JsonResponse(['notifContentArray' => $notifContentArray, 'notifIdArray' =>  $notifIdArray]);

    }


    
     /**
     * @Route("/project/dailyCountDone", name="dailyCountDonePath")
     */

    public function dailyCountDone( EntityManagerInterface $manager, UserInterface $user){


        if(isset($_POST["projectId"])){



        $project = $this->getDoctrine()->getRepository(Project::class)->find($_POST['projectId']);

        $project->setDailyCountToDone();

        
        $pinCount = $user->getPinCount();

        $user->setPinCount($pinCount+1);
        


        $user->addNotification($notification = new Notification());

        $notification->setContent('Super, tu as atteint ton compte journalier, pour le projet nommé '. $project->getProjectName() . 'tu gagnes 50 points de compétence!');
       
    

        $initialCompetencyPoints = $user->getCompetencyPoints();

        $initialUserLevel = $user->getLevel();


        $user->setCompetencyPoints($user->getCompetencyPoints()+50);



        if($user->getCompetencyPoints() >= 50 && $user->getCompetencyPoints() < 100){

            $user->setLevel('élément à fort potentiel');

        } else  if($user->getCompetencyPoints() >= 100  && $user->getCompetencyPoints() < 150){

            $user->setLevel('loup de Wall Street');
        } else if($user->getCompetencyPoints() >= 150){

            $user->setLevel('Jeff Bezzos');
        } else {

            $user->setLevel('novice');
        }



        $updatedUserLevel = $user->getLevel();

        $updatedCompetencyPoints = $user->getCompetencyPoints();



        if($updatedUserLevel !=  $initialUserLevel ){

        
            if($initialCompetencyPoints >  $updatedCompetencyPoints ){

                $user->addNotification($notification = new Notification());

                $pinCount = $user->getPinCount();
                      
                $user->setPinCount($pinCount+1);

                $notification->setContent('Dommage!Tu baisse de niveau!! Tu passe au niveau ' . $updatedUserLevel);

                } else if($initialCompetencyPoints <  $updatedCompetencyPoints ){

                    $user->addNotification($notification = new Notification());

                    $pinCount = $user->getPinCount();
                          
                    $user->setPinCount($pinCount+1);
                
                    $notification->setContent('Top!Tu monte de niveau!! Tu passe au niveau ' . $updatedUserLevel);

                }
            }
      

        
        $manager->persist($user);

        $manager->flush();

        return new JsonResponse(['pin' => $user->getPinCount()]);

        }

        return new JsonResponse(['error' => 'error']);


    }




    
     /**
     * @Route("/endofday" , name="endOfDayPath")
     */

    public function endOfDay( EntityManagerInterface $manager, MailerInterface $mailer, Request $request){

        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        
        foreach($users as $user){

            //useful to check if there is a level change for each user
        
            $initialUserLevel = $user->getLevel();

            $initialCompetencyPoints = $user->getCompetencyPoints();


            $projects = $user->getProjects();

            //dynamic means days worked on a row

            $userDynamic = $user->getDynamic();

            $user->setDynamic($userDynamic+1);

            //count of days from the creation of the User

            $days = $user->getDaysOnTheApp();

            $user->setDaysOnTheApp($days+1);

            $initialCountOfDailyObjective = $user->getAllDailyCountsDone();

            //increase the count by 1, and loop through all the projects, to possibly set it back to the initial count, if at least one daily count is not done

            $user->setAllDailyCountsDone($initialCountOfDailyObjective + 1);


            $totalWork = 0;

            
            foreach($projects as $project){

            $totalWork += $project->getTotalCount();

            $days = $project->getDaysFromCreation();

            $project->setDaysFromCreation($days+1);


                if($project->getDailyCountDone() == 'false'){
                    
                    $user->setAllDailyCountsDone($initialCountOfDailyObjective);

                    $user->setDynamic(0);

                    $project->setDynamic(0);

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


       } else if ($project->getDailyCountDone() == 'true') {

        $projectDynamic = $project->getDynamic();

        $project->setDynamic($projectDynamic+1);
        
        $dailyCountsDoneCount = $project->getDailyCountsDoneCount();

        $project->setDailyCountsDoneCount($dailyCountsDoneCount+1);

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

            $project->initializeDailyCount();



            if ($project->getDynamic() == 2 ){

                
                $user->setCompetencyPoints($user->getCompetencyPoints()+20);

                $user->addNotification($notification = new Notification());

                $pinCount = $user->getPinCount();
                      
                $user->setPinCount($pinCount+1);


                $notification->setContent("Tu gagnes un bonus de 20 points de compétence, car tu as travaillé 2 jours d'affilée, sur ton projet: " . $project->getProjectName());

            } 

            else if ($project->getDynamic() == 3 ){

                $user->setCompetencyPoints($user->getCompetencyPoints()+30);


                $user->addNotification($notification = new Notification());

                $pinCount = $user->getPinCount();
                      
                $user->setPinCount($pinCount+1);

                $notification->setContent("Tu gagnes un bonus de 20 points de compétence, car tu as travaillé 3 jours d'affilée, sur ton projet: " . $project->getProjectName());

            } else if ($project->getDynamic() == 4 ){

                $user->setCompetencyPoints($user->getCompetencyPoints()+40);


                $user->addNotification($notification = new Notification());

                $pinCount = $user->getPinCount();
                      
                $user->setPinCount($pinCount+1);

                $notification->setContent("Tu gagnes un bonus de 40 points de compétence, car tu as travaillé 4 jours d'affilée, sur ton projet: " . $project->getProjectName());

            } else if ($project->getDynamic() == 5 ){

                $user->setCompetencyPoints($user->getCompetencyPoints()+50);


                $user->addNotification($notification = new Notification());

                $pinCount = $user->getPinCount();
                      
                $user->setPinCount($pinCount+1);

                $notification->setContent("Tu gagnes un bonus de 50 points de compétence, car tu as travaillé 5 jours d'affilée, sur ton projet: " . $project->getProjectName());

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

        $updatedUserLevel = $user->getLevel();

        $updatedCompetencyPoints = $user->getCompetencyPoints();



        if($updatedUserLevel !=  $initialUserLevel ){

        
            if($initialCompetencyPoints >  $updatedCompetencyPoints ){

                $user->addNotification($notification = new Notification());

                $pinCount = $user->getPinCount();
                      
                $user->setPinCount($pinCount+1);

                $notification->setContent('Dommage!Tu baisse de niveau!! Tu passe au niveau ' . $updatedUserLevel);

                } else if($initialCompetencyPoints <  $updatedCompetencyPoints ){

                    $user->addNotification($notification = new Notification());

                    $pinCount = $user->getPinCount();
                          
                    $user->setPinCount($pinCount+1);
                
                    $notification->setContent('Top!Tu monte de niveau!! Tu passe au niveau ' . $updatedUserLevel);

                }
            }




        }




    
        //end of users loop


        $user->setTotalWork($totalWork);
    
        $manager->persist($user);

        $manager->flush();



        return $this->redirectToRoute('profilePagePath');

        
    }










    
     /**
     * @Route("/goToNextDay" , name="goToNextDayPath")
     */

    public function goToNextDay( EntityManagerInterface $manager, MailerInterface $mailer, Request $request , UserInterface $user){

        
            //useful to check if there is a level change for each user
        
            $initialUserLevel = $user->getLevel();

            $initialCompetencyPoints = $user->getCompetencyPoints();


            $projects = $user->getProjects();

            //dynamic means days worked on a row

            $userDynamic = $user->getDynamic();

            $user->setDynamic($userDynamic+1);

            //count of days from the creation of the User

            $days = $user->getDaysOnTheApp();

            $user->setDaysOnTheApp($days+1);

            $initialCountOfDailyObjective = $user->getAllDailyCountsDone();

            //increase the count by 1, and loop through all the projects, to possibly set it back to the initial count, if at least one daily count is not done

            $user->setAllDailyCountsDone($initialCountOfDailyObjective + 1);


            $totalWork = 0;

            
            foreach($projects as $project){

            $totalWork += $project->getTotalCount();

            $days = $project->getDaysFromCreation();

            $project->setDaysFromCreation($days+1);


                if($project->getDailyCountDone() == 'false'){
                    
                    $user->setAllDailyCountsDone($initialCountOfDailyObjective);

                    $user->setDynamic(0);

                    $project->setDynamic(0);

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


       } else if ($project->getDailyCountDone() == 'true') {

        $projectDynamic = $project->getDynamic();

        $project->setDynamic($projectDynamic+1);
        
        $dailyCountsDoneCount = $project->getDailyCountsDoneCount();

        $project->setDailyCountsDoneCount($dailyCountsDoneCount+1);

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

            $project->initializeDailyCount();



            if ($project->getDynamic() == 2 ){

                
                $user->setCompetencyPoints($user->getCompetencyPoints()+20);

                $user->addNotification($notification = new Notification());

                $pinCount = $user->getPinCount();
                      
                $user->setPinCount($pinCount+1);


                $notification->setContent("Tu gagnes un bonus de 20 points de compétence, car tu as travaillé 2 jours d'affilée, sur ton projet: " . $project->getProjectName());

            } 

            else if ($project->getDynamic() == 3 ){

                $user->setCompetencyPoints($user->getCompetencyPoints()+30);


                $user->addNotification($notification = new Notification());

                $pinCount = $user->getPinCount();
                      
                $user->setPinCount($pinCount+1);

                $notification->setContent("Tu gagnes un bonus de 20 points de compétence, car tu as travaillé 3 jours d'affilée, sur ton projet: " . $project->getProjectName());

            } else if ($project->getDynamic() == 4 ){

                $user->setCompetencyPoints($user->getCompetencyPoints()+40);


                $user->addNotification($notification = new Notification());

                $pinCount = $user->getPinCount();
                      
                $user->setPinCount($pinCount+1);

                $notification->setContent("Tu gagnes un bonus de 40 points de compétence, car tu as travaillé 4 jours d'affilée, sur ton projet: " . $project->getProjectName());

            } else if ($project->getDynamic() == 5 ){

                $user->setCompetencyPoints($user->getCompetencyPoints()+50);


                $user->addNotification($notification = new Notification());

                $pinCount = $user->getPinCount();
                      
                $user->setPinCount($pinCount+1);

                $notification->setContent("Tu gagnes un bonus de 50 points de compétence, car tu as travaillé 5 jours d'affilée, sur ton projet: " . $project->getProjectName());

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

        $updatedUserLevel = $user->getLevel();

        $updatedCompetencyPoints = $user->getCompetencyPoints();



        if($updatedUserLevel !=  $initialUserLevel ){

        
            if($initialCompetencyPoints >  $updatedCompetencyPoints ){

                $user->addNotification($notification = new Notification());

                $pinCount = $user->getPinCount();
                      
                $user->setPinCount($pinCount+1);

                $notification->setContent('Dommage!Tu baisse de niveau!! Tu passe au niveau ' . $updatedUserLevel);

                } else if($initialCompetencyPoints <  $updatedCompetencyPoints ){

                    $user->addNotification($notification = new Notification());

                    $pinCount = $user->getPinCount();
                          
                    $user->setPinCount($pinCount+1);
                
                    $notification->setContent('Top!Tu monte de niveau!! Tu passe au niveau ' . $updatedUserLevel);

                }
            }







    
        //end of users loop


        $user->setTotalWork($totalWork);
    
        $manager->persist($user);

        $manager->flush();



        return $this->redirectToRoute('profilePagePath');

        
    }
























    



     /**
     * @Route("/totalCountDone" , name="totalCountDonePath")
     */

    public function totalCountDone( EntityManagerInterface $manager, UserInterface $user){

        $project = $this->getDoctrine()->getRepository(Project::class)->find($_POST['projectId']);

        $project->setTotalCountToDone();

        $project->totalCount = $project->getTotalLimit();


        $user->addNotification($notification = new Notification());

        $notification->setContent('Super, tu es venu à bout de ton projet , intitulé : ' . $project->getProjectName() . 'tu gagnes 200 points de compétence!!!' );



        
        $initialUserLevel = $user->getLevel();

        $initialCompetencyPoints = $user->getCompetencyPoints();

        $user->setCompetencyPoints($initialCompetencyPoints+200);

        $updatedCompetencyPoints = $user->getCompetencyPoints();

    




        if($user->getCompetencyPoints() >= 50 && $user->getCompetencyPoints() < 100){

            $user->setLevel('élément à fort potentiel');

        } else if($user->getCompetencyPoints() >= 100  && $user->getCompetencyPoints() < 150){

            $user->setLevel('loup de Wall Street');
        } else if($user->getCompetencyPoints() >= 150){

            $user->setLevel('Jeff Bezzos');
        } else {

            $user->setLevel('novice');
        }

        $updatedUserLevel = $user->getLevel();



        
        if($updatedUserLevel !=  $initialUserLevel ){

        
            if($initialCompetencyPoints >  $updatedCompetencyPoints ){

                $user->addNotification($notification = new Notification());

                $pinCount = $user->getPinCount();
                      
                $user->setPinCount($pinCount+1);

                $notification->setContent('Dommage!Tu baisse de niveau!! Tu passe au niveau ' . $updatedUserLevel);

                } else if($initialCompetencyPoints <  $updatedCompetencyPoints ){

                    $user->addNotification($notification = new Notification());

                    $pinCount = $user->getPinCount();
                          
                    $user->setPinCount($pinCount+1);
                
                    $notification->setContent('Top!Tu monte de niveau!! Tu passe au niveau ' . $updatedUserLevel);

                }
            }





        $pinCount = $user->getPinCount();
        
        $user->setPinCount($pinCount+1);








        
        


        
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
        
        $currentWeek = $project->currentWeek;
        $currentDay = $project->currentDay;

        $displayedWeek = $currentWeek;

        $day1 = $project->weeks[$displayedWeek]->days[0]->getDailyCount(); 
        $day2 = $project->weeks[$displayedWeek]->days[1]->getDailyCount(); 
        $day3 = $project->weeks[$displayedWeek]->days[2]->getDailyCount(); 
        $day4 = $project->weeks[$displayedWeek]->days[3]->getDailyCount(); 
        $day5 = $project->weeks[$displayedWeek]->days[4]->getDailyCount(); 
        $day6 = $project->weeks[$displayedWeek]->days[5]->getDailyCount(); 
        $day7 = $project->weeks[$displayedWeek]->days[6]->getDailyCount(); 



        $dynamic = $project->getDynamic();

        $count = $project->getDailyCountsDoneCount();

        $days = $project->getDaysFromCreation();


        if($days != 0 ){

            $averageRespectOfCount = floor(($count/$days)*100);
    

        } else {

            $averageRespectOfCount  = 0;
        }
        

        $totalWork = $project->getTotalCount();

    

        $averageWorkTime = floor( (($totalWork/($days+1)))/60);
    

           return $this->render('project/graph.html.twig', ['currentDay' => $currentDay , 'currentWeek'=> $currentWeek , 'averageWorkTime' => $averageWorkTime ,  'averageDailyRespect' => $averageRespectOfCount , 'dynamic'=> $dynamic , 'projectId' => $projectId , 'displayedWeek' => $displayedWeek , 'nextWeekButton' => 'no' ,'day1' => $day1 , 'day2' => $day2,'day3' => $day3,'day4' => $day4,'day5' => $day5,'day6' => $day6,'day7' => $day7]);


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
