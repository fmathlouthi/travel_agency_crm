<?php

/**
 * This file is part of the pdAdmin package.
 *
 * @package     pd-admin
 * @license     LICENSE
 * @author      Ramazan APAYDIN <apaydin541@gmail.com>
 * @link        https://github.com/appaydin/pd-admin
 */

namespace App\Form\Config;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Languages;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;

/**
 * General Settings Form.
 *
 * @author Ramazan APAYDIN <apaydin541@gmail.com>
 */
class GeneralForm extends ConfigAbstractType
{
    /**
     * Create Form.
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Get Language List
        $languageList = array_flip(array_intersect_key(Languages::getNames(), array_flip($options['container']->get('parameter_bag')->get('active_language'))));

        $builder
            ->add('head_title1', TextType::class, [
                'label' => 'head_title',
                'help' => 'head_title_info',
                'constraints' => [
                    new Length([
                        'max' => 150,
                    ]),
                ],
                'empty_data' => 'CRM TUNINFO',
                'attr' => ['placeholder-nt' => 'CRM TUNINFO'],
                'required' => false,
            ])
            ->add('head_title_pattern', TextType::class, [
                'label' => 'head_title_pattern',
                'help' => 'head_title_pattern_info',
                'constraints' => [
                    new Length([
                        'max' => 150,
                    ]),
                ],
                'empty_data' => '&T - &P',
                'attr' => ['placeholder-nt' => '&T - &P'],
                'required' => false,
            ])
            ->add('head_description1', TextareaType::class, [
                'label' => 'head_description',
                'help' => 'head_description_info',
                'constraints' => [
                    new Length([
                        'max' => 200,
                    ]),
                ],
                'empty_data' => 'CRM TUNINFO Head Description',
                'attr' => ['placeholder-nt' => 'CRM TUNINFO Head Description'],
                'required' => false,
            ])
            ->add('head_author1', TextType::class, [
                'label' => 'head_author',
                'help' => 'head_author_info',
                'constraints' => [
                    new Length([
                        'max' => 150,
                    ]),
                ],
                'empty_data' => 'FADI MATHLOUTHI',
                'attr' => ['placeholder-nt' => 'FADI MATHLOUTHI'],
                'required' => false,
            ])
            ->add('head_keywords', TextareaType::class, [
                'label' => 'head_keywords',
                'constraints' => [
                    new Length([
                        'max' => 200,
                    ]),
                ],
                'attr' => ['placeholder-nt' => 'CRM,tuninfo'],
                'required' => false,
            ])
            ->add('footer_copyright1', TextareaType::class, [
                'label' => 'footer_copyright',
                'constraints' => [
                    new Length([
                        'max' => 200,
                    ]),
                ],
                'empty_data' => 'CRM TUNINFO FORYOU',
                'attr' => ['placeholder-nt' => 'CRM TUNINFO FORYOU'],
                'required' => false,
            ])
            ->add('default_locale1', ChoiceType::class, [
                'label' => 'default_locale',
                'choices' => $languageList,
                'choice_translation_domain' => false,
                'empty_data' => 'fr',
                'placeholder' => false,
                'required' => false,
            ])
            ->add('default_country1', CountryType::class, [
                'label' => 'default_country',
                'choice_translation_domain' => false,
                'empty_data' => 'TUN',
                'placeholder' => false,
                'required' => false,
            ])
            ->add('site_logo', FileType::class, [
                'label' => 'site_logo',
                'attr' => [
                    'label' => 'upload_image_btn',
                    'label_class' => 'btn btn-success',
                ],
                'required' => false,
                'data_class' => null,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                    ]),
                    new Image([
                        'mimeTypes' => [
                            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml',
                        ],
                    ]),
                ],
            ])
            ->add('site_favicon', FileType::class, [
                'label' => 'site_favicon',
                'attr' => [
                    'label' => 'upload_image_btn',
                    'label_class' => 'btn btn-success',
                ],
                'required' => false,
                'data_class' => null,
                'constraints' => [
                    new File([
                        'maxSize' => '3M',
                    ]),
                    new Image([
                        'mimeTypes' => [
                            'image/x-icon', 'image/png',
                        ],
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'save',
            ]);
    }

    /**
     * Form Default Options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('container');
    }
}
