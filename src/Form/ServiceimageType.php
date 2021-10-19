<?php

namespace App\Form;

use App\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ServiceimageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder



            ->add('image', FileType::class, [
                'label' => 'image',
                'attr' => [

                    'label' => 'chose_file_please',
                    'label_class' => 'btn btn-danger',

                ],
                'help' => 'slecte a image',
                'required' => false,
                'data_class' => null,

                'constraints' => [
                    new File([
                        'maxSize' => '3M',
                    ]),
                ],
            ])

        ->add('submit', SubmitType::class, [
            'label' => 'save',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }
}
