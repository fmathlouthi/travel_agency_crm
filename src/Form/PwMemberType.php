<?php

namespace App\Form;

use App\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;

class PwMemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


 $builder
     ->add('current_password', PasswordType::class, [
         'label' => 'security.password_current',
         'mapped' => false,
         'constraints' => new UserPassword(),
     ])
     ->add('plainPassword', RepeatedType::class, [
         'mapped' => false,
         'type' => PasswordType::class,
         'first_options' => ['label' => 'security.password'],
         'second_options' => ['label' => 'security.password_confirmation'],
         'constraints' => [
             new Length([
                 'min' => 3,
                 'max' => 4096,
             ]),
         ],
         'invalid_message' => 'password_dont_match',
     ])
     ->add('Submit', SubmitType::class, [
         'label' => 'save',
     ]);
    }
}
