<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Project;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;




class ProjectController extends AbstractController
{
    /**
     * @Route("/project/new", name="newProject")
     */

    public function new(UserInterface $user, EntityManagerInterface $manager)
    {
        
        
        if(isset($_POST['submit'])){
    
        $myUser = new User();

        $project = $myUser->projects->add(new Project());
        
        $project->setProjectName('first Project');


        $manager->persist($myUser);
        $manager->flush();

        }

        return $this->render('project/new.html.twig', [
            'controller_name' => 'ProjectController',
        ]);
    }

       /**
     * @Route("/project/show", name="newProject")
     */

    public function show(){

        return $this->render('project/show.html.twig');
    }
}
