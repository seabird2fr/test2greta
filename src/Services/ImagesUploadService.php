<?php

namespace App\Services;

use App\Entity\ImageUpload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class ImagesUploadService extends AbstractController {


	public function upload($ad,$manager){



				foreach ($ad->file as $file) {
			                   
			            // suppression de l'extension du fichier original 
			                $position_point = strpos($file->getClientOriginalName(), '.'); 
			                $nom_original=substr($file->getClientOriginalName(), 0, $position_point);

			                $Filename = md5(uniqid()).'.'.$file->guessExtension();
			                
			                $upload=new ImageUpload();
			                $upload->setName($nom_original)
			                        ->setUrl('/uploads/'.$Filename)
			                        ->setAd($ad);
			                $manager->persist($upload);       

			                $file->move(
			                        $this->getParameter('directory_files'),
			                        $Filename);

			                }




	}










}