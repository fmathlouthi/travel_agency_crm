<?php

namespace App\Form;

use App\Entity\ContactService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Contact;
use Doctrine\DBAL\Types\DateType;

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


class ContactServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder


        ->add('contact_id', ChoiceType::class, [

            'label' => 'contact ',
                'help' => 'please select the contact',
            'choices'  =>  $options['categories'],
                'multiple' => false,
                'required' => false,
                'placeholder' => false,
            ])

            ->add('service_id', ChoiceType::class, [

                'label' => 'service ',
                'help' => 'please select the service',
                'choices'  =>  $options['services'],
                'multiple' => false,

                'required' => false,
                'placeholder' => false,
            ])

            ->add('sccore', IntegerType::class, [
                'label' => 'score',
                'help' => 'sccore',
                'empty_data' => '20',
                'attr' => ['placeholder-nt' => 'sccore'],
                'required' => false,
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'save',
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContactService::class,
            'categories' => array(),
            'services' => array(),
        ]);
    }
}
