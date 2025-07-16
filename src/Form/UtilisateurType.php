<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Role;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints as Assert; 




class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('prenom', TextType::class, [
            'label' => 'prenom',
            'constraints' => [
                new Assert\NotBlank(['message' => 'Le prénom est requis.']),
                new Assert\Length(['min' => 2, 'max' => 15]),
            ],
        ])
        ->add('nom', TextType::class, [
            'label' => 'nom',
            'constraints' => [
                new Assert\NotBlank(['message' => 'Le nom est requis.']),
                new Assert\Length(['min' => 2, 'max' => 15]),
            ],
        ])
        ->add('courriel', EmailType::class, [
            'label' => 'courriel',
            'constraints' => [
                new Assert\Email([
                    'message' => 'Veuillez entrer une adresse email valide.',
                ]),
                new Assert\NotBlank([
                    'message' => 'L\'email ne peut pas être vide.',
                ]), 
            ]
        ])
        ->add('telephone', TextType::class, [
            'label' => 'telephone',
            'constraints' => [
                new Assert\Regex([
                    'pattern' => '/^\+?[0-9]{8,10}$/',  
                    'message' => 'Le numéro de téléphone doit être valide (ex. : +1234567890).'
                ]),
                new Assert\NotBlank([
                    'message' => 'Le numéro de téléphone ne peut pas être vide.',
                ]),
            ]
        ])
    
        ->add('dateNaissance', DateType::class, [
            'label' => 'Date de naissance',
            'widget' => 'single_text', 
            'format' => 'yyyy-MM-dd',   
        ])

            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('motdepasse', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 16,
                    ]),
                ],
            ])
            ->add('commentaire', TextType::class, [
                'label' => 'commentaire',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le commentaire ne peut pas être vide.',
                    ]),
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'Le commentaire ne peut pas dépasser 255 caractères.',
                    ]),
                ],
            ]);
        }
        
    

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
