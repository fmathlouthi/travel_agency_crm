<?php

namespace App\Form;

use App\Entity\LoyaltyCards;
use Doctrine\DBAL\Types\IntegerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class LoyaltyCardsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder




            ->add('is_valid')








            ->add('loyalty_points', \Symfony\Component\Form\Extension\Core\Type\IntegerType::class, [
                'label' => 'loyalty_points',
                'help' => 'loyalty_points',
                'empty_data' => '0',
                'attr' => ['placeholder-nt' => '0'],
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'status',
                'help' => 'please select the status card',
                'multiple' => false,
                'choices' => [
                    'withdraw' =>'withdraw' ,
                    'Active' => 'Active',
                    'DesActive' => 'DesActive',
                ],
                'empty_data' => ['Dear Mr.'],
                'required' => false,
                'placeholder' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'save',
            ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => LoyaltyCards::class,
        ]);
    }
}
