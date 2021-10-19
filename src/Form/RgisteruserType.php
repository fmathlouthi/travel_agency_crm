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

class RgisteruserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {



        $builder


            ->add('email', EmailType::class, [
                'attr' => ['placeholder' => 'security.email'],
                'label' => 'email',
                'help' => 'please insert your email adress',

                'required' => true,
            ])
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => ['placeholder' => 'security.password'],
                    'label_attr' => ['style' => 'display:none'],
                    'label' => false,
                ],
                'second_options' => [
                    'attr' => ['placeholder' => 'security.password_confirmation'],
                    'label_attr' => ['style' => 'display:none'],
                    'label' => false,
                ],
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'max' => 4096,
                    ]),
                ],
                'invalid_message' => 'password_dont_match',
            ]);

        // Add Profile
        $builder->add($builder
            ->create('profile', FormType::class, [
                'data_class' => $options['profile_class'],
                'label' => false,
                'attr' => ['class' => 'col-12'],
            ])
            ->add('firstname', TextType::class, [
                'label_attr' => ['style' => 'display:none'],
                'label' => false,
                'attr' => ['placeholder' => 'firstname'],
            ])
            ->add('lastname', TextType::class, [
                'label_attr' => ['style' => 'display:none'],
                'label' => false,
                'attr' => ['placeholder' => 'lastname'],
            ])
            ->add('phone', TextType::class, [
                'label_attr' => ['style' => 'display:none'],
                'label' => false,
                'attr' => ['placeholder' => 'phone'],
                'required' => false,
                'constraints' => [
                    new Type([
                        'type' => 'numeric',
                    ]),
                ],
            ])
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('profile_class');
    }
}
