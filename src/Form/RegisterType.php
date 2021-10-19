<?php

/**
 * This file is part of the pd-admin pd-user package.
 *
 * @package     pd-user
 * @license     LICENSE
 * @author      Ramazan APAYDIN <apaydin541@gmail.com>
 * @link        https://github.com/appaydin/pd-user
 */

namespace App\Form;

use App\Controller\AccountController;
use App\Entity\Contact;
use App\Entity\Service;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Type;

/**
 * User Register Form.
 *
 * @author Ramazan APAYDIN <apaydin541@gmail.com>
 */
class RegisterType extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => ['placeholder' => 'example@example.example'],
                'label' => 'Email Adresse',
                'help' => 'please insert your email adress',

                'required' => true,
            ])



            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => ['placeholder' => 'security.password'],

                    'label' => 'security.password',
                    'help' => 'please insert your email adress',
                    'required' => true,
                ],
                'second_options' => [
                    'attr' => ['placeholder' => 'security.password_confirmation'],

                    'label' => 'security.password_confirmation',
                    'help' => 'please insert your email adress',
                    'required' => true,
                ],
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'max' => 4096,
                    ]),
                ],
                'invalid_message' => 'password_dont_match',
            ])






            ->add('firstName', TextType::class, [
                'label' => 'firstName',
                'help' => 'please insert contact name',

                'empty_data' => 'your name',
                'attr' => ['placeholder' => 'firstName'],
                'required' => false,
            ])

            ->add('lastName', TextType::class, [
                'label' => 'lastName',
                'help' => 'lastName',

                'empty_data' => 'lastName',
                    'attr' => ['placeholder' => 'lastName'],
                'required' => false,
            ])

            ->add('phone', IntegerType::class, [
                'label' => 'phone1',
                'help' => 'PHONE',
                'empty_data' => '12455366',
                'attr' => ['placeholder' => 'phone'],
                'required' => false,
            ])


            ->add('submit', SubmitType::class, [
                'label' => 'security.register_btn',
            ]
        );
    }


}
