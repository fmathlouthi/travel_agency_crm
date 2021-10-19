<?php

namespace App\Form;

use App\Entity\Promotion;
use Doctrine\DBAL\Types\DateType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class PromotionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
            'label' => 'name',
            'help' => 'please insert contact name',

            'empty_data' => 'name',
            'attr' => ['placeholder-nt' => 'name'],
            'required' => false,
        ])

           ->add('description', TextType::class, [
            'label' => 'description',
            'help' => 'description',

            'empty_data' => 'description',
            'attr' => ['placeholder-nt' => 'description'],
            'required' => false,
        ])
           ->add('image', FileType::class, [
                'label' => 'image',
                'attr' => [

                    'label' => 'chose_file_please',
                    'label_class' => 'btn btn-danger',

                ],
                'help' => 'slecte a image',


                'constraints' => [
                    new File([
                        'maxSize' => '6M',
                    ]),
                ],
               'required' => false,
               'data_class' => null,
               'empty_data' => 'description',
            ])

          ->add('startsAt', \Symfony\Component\Form\Extension\Core\Type\DateType::class, [
            'label' => 'startsAt',
            'help' => 'insert startsAt date',
            'empty_data' => '99/99/9999',
            'attr' => ['placeholder-nt' => 'startsAt'],
            'required' => false,
        ])
            ->add('endsAt', \Symfony\Component\Form\Extension\Core\Type\DateType::class, [
                'label' => 'endsAt',
                'help' => 'insert endsAt date',
                'empty_data' => '99/99/9999',
                'attr' => ['placeholder-nt' => 'endsAt'],
                'required' => false,
            ])
            ->add('exclusive')



        ->add('linkpro', TextType::class, [
            'label' => 'linkpro',
            'help' => 'link promotion',
            'empty_data' => 'link',
            'attr' => ['placeholder-nt' => 'link'],
            'required' => false,
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'save',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Promotion::class,
            'constraints' => [
                new UniqueEntity(['fields' => ['name']])
            ],
        ]);
    }
}
