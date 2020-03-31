<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminAdController extends AbstractController
{
    /**
     * @Route("/admin/ads/{page}", name="admin_ads_index" , requirements={"page":"[0-9]{1,}"})
     */
    public function index(AdRepository $repo,$page=1)
    {

      //dump($page);

      $limit=10;

       $start = $page * $limit - $limit; 

 $total=count($repo->findAll()); 
 $pages= ceil($total/$limit);  

    	return $this->render('admin/ad/index.html.twig', [
    		'ads'=>$repo-> findBy([],[],$limit,$start), 
          'pages' => $pages, 
            'page' => $page 
    	]);
    }



      /**
    * @Route("/admin/ads/{id}/edit", name="admin_ads_edit")
    */
      public function edit(EntityManagerInterface $manager,Request $request,Ad $ad)
      {


      	$form = $this -> createForm(AnnonceType::class,$ad);

      	$form->handleRequest($request);

      	if ($form ->isSubmitted() && $form->isValid()) 
      	{   

                // fonction d'upload d'images déportée dans Services
                   // $upload->upload($ad,$manager);

      		$tabid = $ad->tableau_id;
      		$tabid = preg_replace("#^,#","",$tabid);

      		$tabid = explode(',',$tabid);

      		foreach ($tabid as $id) {

      			foreach ($ad->getImageUploads() as $imageUploadee) {

      				if ($id == $imageUploadee->getId())
      				{


      					$manager->remove($imageUploadee);
      					$manager->flush();

      					unlink($_SERVER['DOCUMENT_ROOT'].$imageUploadee->getUrl());



      				}

      			}



      		}


                // images venant de la collection de champs
      		foreach ($ad->getImages() as $image) {
      			$image->setAd($ad);
      			$manager->persist($image);

      		}

      		$manager->persist($ad);
      		$manager->flush();


      		$this->addFlash(
      			'success',
      			"l'annonce {$ad->getTitle()} a été correctement modifiée"
      		);

      		return $this->redirectToRoute('admin_ads_index');


      	} 



      	return $this->render('admin/ad/edit.html.twig', [
      		'form' => $form->createView(),
      		'ad'=>$ad
      	]);
      }   



        /**
    * @Route("/admin/ads/{id}/delete", name="admin_ads_delete")
 */
        public function delete(EntityManagerInterface $manager, Ad $ad)
        {

        
        	if (count($ad->getBookings()) == 0)
        	{
        		$manager->remove($ad);
        		$manager->flush();

        		$this->addFlash(
        			'success',
        			"l'annonce a été supprimée"
        		);

        	}
        	else
        	{

        		$this->addFlash(
        			'warning',
        			//"l'annonce {$ad->getTitle()} ne peut pas être supprimée car il y a des réservations en cours !"
        			'l\'annonce '.$ad->getTitle().' ne peut pas être supprimée car il y a des réservations en cours !'
        		);

        	}	  

        	return $this->redirectToRoute('admin_ads_index');


        }




    }
