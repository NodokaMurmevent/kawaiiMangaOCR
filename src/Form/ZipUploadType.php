<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ZipUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('attachment', FileType::class);
        $builder->add('submit', SubmitType::class,[
            "label"=>"Soumettre",
            "attr"=>[
                "class"=> "inline-flex uppercase justify-center py-2 px-5 border border-transparent shadow-sm text-base font-title font-medium rounded-full text-white bg-lime-600 hover:bg-lime-700 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500"
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
