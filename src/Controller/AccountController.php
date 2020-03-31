<?php

namespace App\Controller;

use App\Entity\PasswordUpdate;
use App\Entity\User;
use App\Form\AccountType;
use App\Form\PasswordUpdateType;
use App\Form\RegistrationType;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AccountController extends AbstractController
{


/**
     * @Route("/register", name="account_register")
     */
    public function register(EntityManagerInterface $manager,Request $request,UserPasswordEncoderInterface $passwordEncoder)
    {

        $user = new User();

        $form = $this -> createForm(RegistrationType::class,$user);

        $form->handleRequest($request);


       if ($form ->isSubmitted() && $form->isValid()) 
        {

                $password=$user->getHash();
                $pass_encoded=$passwordEncoder->encodePassword($user,$password);
                $user->setHash($pass_encoded);

                $slugify=new Slugify();
                $slug = $slugify->slugify($user->getFirstName().'-'.$user->getLastName());
                $user->setSlug($slug);

                $manager->persist($user);
                $manager->flush();


                    $this->addFlash(
            'success',
            "votre compte a bien été créé"
            );

            return $this->redirectToRoute('account_login');
            
      


        }


         return $this->render('account/register.html.twig', [
            'formUser' => $form->createView(),
        ]);

    }



/*modification du profil */
/**
* @Route("/account/profile", name="account_profile")
*/
    public function profile(EntityManagerInterface $manager,Request $request)
    {

        $user=$this->getUser();
       // dump($user);
         $form = $this -> createForm(AccountType::class,$user);

        $form->handleRequest($request);


       if ($form ->isSubmitted() && $form->isValid()) 
        {

                $manager->persist($user);
                $manager->flush();

                 $this->addFlash(
            'success',
            "Votre profil a bien été modifié"
            );


            return $this->redirectToRoute('account_index');

        }

     return $this->render('account/profile.html.twig', [
            'formUser' => $form->createView(),
        ]);

    }



    /**
    *liste des réservations de l'utilisateur
     * @Route("/account/bookings", name="account_bookings")
     */
    public function bookings()
    {

      return $this->render('account/bookings.html.twig');

   }





/**
     * @Route("/account/password-update", name="account_password")
     */
    public function updatePassword(EntityManagerInterface $manager,Request $request,UserPasswordEncoderInterface $passwordEncoder)
    {

        $user=$this->getUser();

        $passwordUpdate=new PasswordUpdate();

         $form = $this -> createForm(PasswordUpdateType::class,$passwordUpdate);

        $form->handleRequest($request);


       if ($form ->isSubmitted() && $form->isValid()) 
        {

            if (!password_verify($passwordUpdate->getOldPassword(), $user->getHash()))
            {
                    $this->addFlash(
            'danger',
            "L'ancien mot de passe est incorrect"
            );

            }
            else
            {

                $newPassword=$passwordUpdate->getNewPassword();
                $pass_encoded=$passwordEncoder->encodePassword($user,$newPassword);
                $user->setHash($pass_encoded);

                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
            'success',
            "Votre mot de passe a bien été modifié"
            );

                return $this->redirectToRoute('account_index');

            }


        }

             return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
        ]);

    }


    /**
     * @Route("/login", name="account_login")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {

    	$error = $authenticationUtils->getLastAuthenticationError();



        return $this->render('account/login.html.twig', [
        	'errorLogin'=> $error,
          
        ]);
    }




      /**
     * @Route("/account/", name="account_index")
     */
    public function myAccount()
    {
       

    return $this->render('user/index.html.twig', [
           'user'=> $this->getUser(),
        ]);

    }



  /**
     * @Route("/logout", name="account_logout")
     */
    public function logout()
    {
       
    }


}
