<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Image;
use App\Entity\ImageUpload;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use App\Services\ImagesUploadService;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class AdController extends AbstractController
{
    /**
     * @Route("/ads", name="ads_index")
     */
    public function index(AdRepository $repo)
    {

    	//$repo=$this->getDoctrine()->getRepository(Ad::class);

    	$ads=$repo->findAll();

    	//dump($ads);

        return $this->render('ad/index.html.twig', [
            'adss'=>$ads
        ]);
    }



/**
     * @Route("/ads/new", name="ads_create")
     * @IsGranted("ROLE_USER")
     */
    public function create(EntityManagerInterface $manager,Request $request, ImagesUploadService $upload)
    {
        

        $ad = new Ad();



        $form = $this -> createForm(AnnonceType::class,$ad);

        $form->handleRequest($request);



       if ($form ->isSubmitted() && $form->isValid()) 
        {

                    $ad->setAuthor($this->getUser());

                    // fonction d'upload d'images déportée dans Services
                    $upload->upload($ad,$manager);


       
                    $slugify=new Slugify();
                    $slug = $slugify->slugify($ad->getTitle().$ad->getId());
                    $ad->setSlug($slug);

                foreach ($ad->getImages() as $image) {
                    $image->setAd($ad);
                    $manager->persist($image);

                }

            $manager->persist($ad);
            $manager->flush();

            $slug2 = $ad->getSlug().'_'.$ad->getId();
            $ad->setSlug($slug2);

            $manager->flush();

              $this->addFlash(
            'success',
            "l'annonce {$ad->getTitle()} a été correctement enregistré"
            );

            return $this->redirectToRoute('ads_show',['slug'=>$ad->getSlug()]);


        } 



        return $this->render('ad/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
    * @Route("/ads/{slug}/edit", name="ads_edit")
    * @Security("is_granted('ROLE_USER') and user ===ad.getAuthor()", message="Cette annonce ne vous appartient pas")
    */
    public function edit(EntityManagerInterface $manager,Request $request,Ad $ad,ImagesUploadService $upload)
    {
      


        $form = $this -> createForm(AnnonceType::class,$ad);

        $form->handleRequest($request);

       if ($form ->isSubmitted() && $form->isValid()) 
        {   

                // fonction d'upload d'images déportée dans Services
                    $upload->upload($ad,$manager);

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

            return $this->redirectToRoute('ads_show',['slug'=>$ad->getSlug()]);


        } 



        return $this->render('ad/edit.html.twig', [
            'form' => $form->createView(),
            'ad'=>$ad
        ]);
    }   


    /**
    * @Route("/ads/{slug}/delete", name="ads_delete")
    * @Security("is_granted('ROLE_USER') and user ===ad.getAuthor()", message="Vous ne pouvez pas supprimer cette annonce, elle ne vous appartient pas")
    */
    public function delete(EntityManagerInterface $manager, Ad $ad)
    {

        $manager->remove($ad);
        $manager->flush();

  $this->addFlash(
            'success',
            "l'annonce a été supprimée"
            );

            return $this->redirectToRoute('ads_index');


}



/**
     * @Route("/ads/{slug}", name="ads_show")
     */
    public function show(Ad $ad)
    {


    	//$ad=$repo->findOneBySlug($slug);

    	//dump($ad);

        return $this->render('ad/show.html.twig', [
            'ad'=>$ad
        ]);
    }






}
