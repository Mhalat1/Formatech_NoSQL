<?php

namespace App\Form;

use App\Entity\FormContact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'prenom'
                ]
            ])
            ->add('Nom', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'nom'
                ]
            ])
            ->add('Email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'email@example.com'
                ]
            ])
            ->add('NomInstitution', TextType::class, [
                'label' => 'Nom de l\'institution *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Institution nom'
                ]
            ])
            ->add('NomSession', TextType::class, [
                'label' => 'Nom de la session *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Session nom'
                ]
            ])
            ->add('NomModule', TextType::class, [
                'label' => 'Nom du module *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Module nom'
                ]
            ])
                ->add('Dates', DateType::class, [
                    'label' => 'Dates *',
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',  
                    'html5' => true,
                    'attr' => [
                        'class' => 'form-control'
                    ],

                    'input' => 'string', 
                    'empty_data' => '',  
                ])
            ->add('Offre', ChoiceType::class, [
                'label' => 'Choisissez votre offre  *',
                'choices' => [
                    'Basic' => 'basic',
                    'Premium' => 'premium',
                ],
                'expanded' => true,
                'multiple' => false,
                'attr' => [
                    'class' => 'offre-choices'
                ],
                'label_attr' => [
                    'class' => 'offre-label'
                ]
            ])
            
            ->add('Message', TextareaType::class, [
                'label' => 'Message',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Votre message... (max 250 caractères)',
                    'rows' => 4,
                    'maxlength' => 250,
                    'data-counter' => 'true'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FormContact::class,
            'csrf_protection' => true,  
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'contact_form',
            'attr' => ['class' => 'contact-form'] 
        ]);
    }
}