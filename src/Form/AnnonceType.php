<?php

namespace App\Form;

use App\Entity\Ad;
use App\Form\ImageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnonceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            //->add('slug',TextType::class,['required' => false])
            ->add('price',MoneyType::class)
            ->add('introduction')
            ->add('content')
            ->add('coverImage')
            ->add('rooms',IntegerType::class)
            ->add('images',CollectionType::class,[
                        'entry_type' => ImageType::class,
                        'allow_add'=>true, // autorise l'ajout et l'utilisation d'un prototype
                        'allow_delete'=>true
            ])
            ->add('file',FileType::class,[
                'label'=>false,
                'required'=>false,
                'attr'=>['placeholder'=>'Choisissez un image'],
                'multiple'=>true,

            ])
            ->add('tableau_id',HiddenType::class,[

                'required'=> false
            ])


        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ad::class,
        ]);
    }
}
