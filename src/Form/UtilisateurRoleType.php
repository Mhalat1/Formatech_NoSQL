<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Mime\Email;
use Symfony\Component\Form\Extension\Core\Type\TextType; 
use Symfony\Component\Validator\Constraints as Assert; 


class UtilisateurRoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Enseignant' => 'ROLE_ENSEIGNANT',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'multiple' => true,  
                'expanded' => true,  
            ])
            ->add('courriel', EmailType::class, [
                'label' => 'Adresse email',
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
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\Utilisateur::class,
        ]);
    }
}
