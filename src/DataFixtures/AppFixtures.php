<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\Role;
use App\Entity\User;
use Cocur\Slugify\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;




class AppFixtures extends Fixture
{

private $passwordEncoder;


 public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }


    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
            $adminRole=new Role();
            $adminRole->setTitle('ROLE_ADMIN');
            $manager->persist($adminRole);

            $adminUser=new User();
            $adminUser ->setFirstName("Eric")
                ->setLastName("Devolder")
                ->setEmail("eric.devolder@ac-nice.fr")
                ->setPicture("http://via.placeholder.com/64")
                ->setIntroduction("je suis l'admin")
                ->setDescription("c'est moi le chef")
                ->setSlug("eric-devolder")
                ->setHash($this->passwordEncoder->encodePassword(
                   $adminUser,
                    'password'
                    ))
                ->addRole($adminRole);

              $manager->persist($adminUser);  



		$slugify=new Slugify();
		$titre='Titre ànnOnce n!:';
		$slug = $slugify->slugify($titre);


    for ($l=1; $l<=5;$l++){

            $user = new User();
            $user ->setFirstName("prenom$l")
                ->setLastName("nom$l")
                ->setEmail("test$l@test.fr")
                ->setPicture("http://via.placeholder.com/64")
                ->setIntroduction("introduction$l")
                ->setDescription("description$l")
                ->setSlug($slugify->slugify("prenom$l-nom$l"))
                ->setHash($this->passwordEncoder->encodePassword(
                   $user,
                    'pass'
                    ));

                $manager->persist($user);


    	for ($i=1; $i <=mt_rand(1,5) ; $i++) { 
    	
    	$ad = new Ad();

    	$ad->setTitle("titre annonce $i")
    		->setSlug($slug.$i)
    		->setCoverImage('https://via.placeholder.com/350')
    		->setIntroduction("<p>je suis l'introduction $i</p>")
    		->setContent('<p>je suis le contenu'.$i.'</p>')
    		->setPrice(mt_rand(40,200))
    		->setRooms(mt_rand(1,5))
            ->setAuthor($user);

			    	for ($j=1; $j <= mt_rand(1,5)  ; $j++) 
			    	{ 
			    		$image= new Image();

			    		$image->setUrl('https://via.placeholder.com/350')
			    		   		->setCaption('legende image'.$j)
			    		   		->setAd($ad);

			    		$manager->persist($image);   		

			    	}	



    		$manager->persist($ad);
            $manager->flush();

            $slug2 = $ad->getSlug().'_'.$ad->getId();
            $ad->setSlug($slug2);
            //dump($ad);


            for ($k=1; $k <= mt_rand(1,5)  ; $k++) 
                    { 

                        $booking=new Booking();
                        $booking->setCreatedAt(new \DateTime())
                        ->setStartDate(new \DateTime("+ 5 days"))
                        ->setEndDate(new \DateTime("+ 10 days"))
                        ->setAmount($ad->getPrice()*5)
                        ->setComment("Commentaire réservation $k")
                        ->setBooker($user)
                        ->setAd($ad);

                     $manager->persist($booking);   


                 // ajout d'un commentaire ou non a la fin de la réservation   
                     if (mt_rand(0,1))
                     {
                        $comment=new Comment();
                        $comment->setCreatedAt(new \DateTime())
                            -> setRating(mt_rand(0,5))
                            ->setContent("commentaire fin de réservation N° $k")
                            ->setAd($ad)
                            ->setAuthor($user);

                            $manager->persist($comment);

                     }



                    }



			}





        }

        $manager->flush();

    }
}
