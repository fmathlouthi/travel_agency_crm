<?php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReclaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder



            ->add('Answer', TextareaType::class, [
                'label' => 'Answer',
                'help' => ' Answer',
                'empty_data' => 'Answer',
                'attr' => ['placeholder-nt' => 'ansewr'],
                'required' => false,
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'send email',
            ])
        ;
    }

}
