<?php

namespace App\Form;

use App\Entity\Utilisateur;
use App\Entity\Institution;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert; 


class InscriptionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder


            ->add('institutionNom', TextType::class, [
                'label' => 'Nom de l\'institution',
                'mapped' => false, 
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nom de l\'institution ne peut pas être vide.',
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ]
            ])
            ->add('institutionAdresse', TextType::class, [
                'label' => 'Adresse de l\'institution',
                'mapped' => false, 
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'L\'adresse ne peut pas être vide.',
                    ]),
                    new Assert\Length([
                        'min' => 5,
                        'max' => 255,
                        'minMessage' => 'L\'adresse doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'L\'adresse ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ]
            ])
            ->add('institutionTelephone', TextType::class, [
                'label' => 'Téléphone de l\'institution',
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le téléphone ne peut pas être vide.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^\+?[0-9]{8,15}$/',
                        'message' => 'Le numéro de téléphone doit être valide (ex. : +1234567890).'
                    ]),
                ]
            ])
            ->add('institutionCourriel', EmailType::class, [
                'label' => 'Courriel de l\'institution',
                'mapped' => false, 
                'constraints' => [
                    new Assert\Email([
                        'message' => 'Veuillez entrer une adresse email valide.',
                    ]),
                    new Assert\NotBlank([
                        'message' => 'L\'email ne peut pas être vide.',
                    ]),
                ]
            ])





            ->add('courriel', EmailType::class, [
                'label' => 'Courriel personnel',
                'constraints' => [
                    new Assert\Email([
                        'message' => 'Veuillez entrer une adresse email valide.',
                    ]),
                    new Assert\NotBlank([
                        'message' => 'L\'email ne peut pas être vide.',
                    ]),
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prénom est requis.']),
                    new Assert\Length(['min' => 2, 'max' => 15]),
                ],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est requis.']),
                    new Assert\Length(['min' => 2, 'max' => 15]),
                ],
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone personnel (8 chiffres)',
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
            ->add('dateNaissance')
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
                        'minMessage' => 'au moins 6 caractères',
                        'max' => 16,
                    ]),
                ],
            ])
            ->add('inscrire', SubmitType::class, [
                'label' => 'Créez votre compte',
                'attr' => ['class' => 'btn btn-primary']
            ]);

            
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
            //eliminer csrf pour les test
            'csrf_protection' => $_SERVER['APP_ENV'] !== 'test',

        ]);
    }
}