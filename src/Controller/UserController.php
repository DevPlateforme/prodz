<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserType;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;



class UserController extends AbstractController
{
    /**
     * @Route("/register", name="registerPath")
     */
    public function register(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();

        if(isset($_POST['submit'])){


            
        }



        return $this->render('user/register.html.twig');
    }


    /**
     * @Route("/login", name="loginPath")
     */


    public function login(){
       
        return $this->render('user/login.html.twig');

    }

      /**
     * @Route("/logout", name="logoutPath")
     */


    public function logout(){
       
     //logout path
    }



    
}
