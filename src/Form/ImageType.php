<?php

namespace App\Form;

use App\Entity\Image;
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

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;


class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $x)
    {

        
        $builder











            ->add('imagepath',FileType::class, [
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
            'data_class' => Image::class,
            'constraints' => [
                new UniqueEntity(['fields' => ['contact_id']])
            ],
        ]);
    }
}
