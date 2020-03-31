<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class FrenchToDateTimeTransformer implements DataTransformerInterface
{


// lorsque l'on passe des données au formulaire
	public function transform($date)
	{

		if ($date===null){

			return '';

		}
		else return $date->format('d/m/Y');


	}



// lorsque l'on reçoit des données du formulaire
	public function reverseTransform($frenchDate)
	{

		$date = \DateTime::createFromFormat('d/m/Y',$frenchDate);

		$date->setTime(0,0,0);

		return $date;


		
	}


}