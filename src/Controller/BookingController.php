<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Entity\Comment;
use App\Form\BookingType;
use App\Form\CommentType;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BookingController extends AbstractController
{
    /**
     * @Route("/ads/{slug}/book", name="booking_create")
     *@IsGranted("ROLE_USER")
     */
    public function book(Ad $ad, EntityManagerInterface $manager,Request $request,BookingRepository $repo)
    {



//******* début liste dates non disponibles pour la réservation ***********
          $notAvailableDays=[];
          
          $repo= $repo->findBy(array('ad'=>$ad->getId()));
          //dump($repo);

          foreach ($repo as $item) {

          $tous_les_timestamp=range($item->getStartDate()->getTimestamp(),$item->getEndDate()->getTimestamp(),24*60*60);      

            $notAvailableDays = array_merge($notAvailableDays,$tous_les_timestamp);

          }

          //dump($notAvailableDays);
        $notAvailableDays=array_unique($notAvailableDays);

       // dump($notAvailableDays);

        $notAvailableDays=array_values($notAvailableDays);

       // dump($notAvailableDays);

//******* fin liste dates non disponibles pour la réservation ***********


    	  $booking= new Booking();


        $form = $this -> createForm(BookingType::class,$booking);

        $form->handleRequest($request);

         if ($form ->isSubmitted() && $form->isValid()) 
            {
                    $booker=$this->getUser();

                    $booking->setBooker($booker)
                            ->setAd($ad)
                            ->setCreatedAt(new \DateTime());

                    //*** debut calcul du prix de la réservation 
                    $interval = date_diff($booking->getStartDate(), $booking->getEndDate());        
                   $booking->setAmount($ad->getPrice()*$interval->days);
                    //*** fin calcul du prix de la réservation 

                // toutes les dates de la réservation
                $tous_les_timestamp_choisies=range($booking->getStartDate()->getTimestamp(),$booking->getEndDate()->getTimestamp(),24*60*60);

                // 
                $datesOK=true;

                foreach ($tous_les_timestamp_choisies as $value) {
                   
                   if (array_search($value,$notAvailableDays)) {$datesOK=false; break;   }

                }


                // si les dates ne sont pas dispos    
                if (!$datesOK)
                {

                    $this-> addFlash(
                        'warning',
                        "Les dates choisies ne sont pas disponibles"

                    );


                }

                // si les dates sont dispos
                else {

                 $manager->persist($booking);
                 $manager->flush();

                 return $this->redirectToRoute('booking_show',['id'=>$booking->getId(),'withAlert'=>true]);

                }



            }// if submit



        return $this->render('booking/index.html.twig', [
            'form'=>$form->createView(),
            'ad'=>$ad,
            'notAvailableDays'=>$notAvailableDays
        ]);
    }


    /**
     * @Route("/booking/{id}", name="booking_show")
     *@IsGranted("ROLE_USER")
     */
    public function show(Booking $booking, EntityManagerInterface $manager,Request $request)
    {


        $comment= new Comment();

        $form = $this -> createForm(CommentType::class,$comment);

        $form->handleRequest($request);

         if ($form ->isSubmitted() && $form->isValid()) 
            {

              $comment->setAd($booking->getAd())
                    ->setCreatedAt(new \DateTime())
                    ->setAuthor($booking->getBooker());

                 $manager->persist($comment);
                 $manager->flush();     

                  $this-> addFlash(
                        'success',
                        "Le commentaire a bien été enregistré"

                  );

            }


 return $this->render('booking/show.html.twig', [
            'booking'=>$booking,
            'form'=> $form->createView()
        ]);

    }

}
