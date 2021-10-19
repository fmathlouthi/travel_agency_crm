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


class PromotionimageType extends AbstractType
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


                'constraints' => [
                    new File([
                        'maxSize' => '6M',
                    ]),
                ],
               'required' => false,
               'data_class' => null,
               'empty_data' => 'description',
            ])


        ->add('submit', SubmitType::class, [
            'label' => 'save',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Promotion::class,

        ]);
    }
}
