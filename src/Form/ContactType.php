<?php

namespace App\Form;

use App\Entity\Contact;
use Doctrine\DBAL\Types\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Type;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Symfony\Component\Validator\Constraints\NotBlank;
class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('salutationName', ChoiceType::class, [
                'label' => 'salutationName',
                'help' => 'please select the salutationName',
                'multiple' => false,
                'choices' => [
                    'Dear Mr.' =>'Dear Mr.' ,
                    'Dear Ms.' => 'Dear Ms.',
                    'Dear Dr.' => 'Dear Dr.',
                ],
                'empty_data' => ['Dear Mr.'],
                'required' => false,
                'placeholder' => false,
            ])





            ->add('firstName', TextType::class, [
                'label' => 'firstName',
                'help' => 'please insert contact name',

                'empty_data' => 'your name',
                'attr' => ['placeholder-nt' => 'firstname'],
                'required' => false,
            ])

            ->add('lastName', TextType::class, [
                'label' => 'lastName',
                'help' => 'lastName',

                'empty_data' => 'lastName',
                'attr' => ['placeholder-nt' => 'lastName'],
                'required' => false,
            ])

            ->add('email', EmailType::class, [
                'attr' => ['placeholder' => 'security.email'],
                'label' => 'email',
                'help' => 'please insert your email adress',

                'required' => true,
            ])


            ->add('birhday', BirthdayType::class, [
                'label' => 'birthday',
                'help' => 'insert birthday date',
                'empty_data' => '99/99/9999',
                'attr' => ['placeholder-nt' => 'birthday'],
                'required' => false,
            ])

            ->add('phone', IntegerType::class, [
                'label' => 'phone1',
                'help' => 'PHONE',
                'empty_data' => '12455366',
                'attr' => ['placeholder-nt' => 'phone'],
                'required' => false,
            ])



            ->add('address', TextType::class, [
                'label' => 'address',
                'help' => 'address',
                'empty_data' => 'address',
                'attr' => ['placeholder-nt' => 'address'],
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'save',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
            'constraints' => [
                new UniqueEntity(['fields' => ['email']])
            ],
        ]);
    }
}
