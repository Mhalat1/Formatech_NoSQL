<?php

namespace App\Form;

use App\Entity\Institution;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints as Assert;

class InstitutionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class, [
            'label' => 'nom',
            'constraints' => [
                new Assert\NotBlank(['message' => 'Le nom est requis.']),
                new Assert\Length(['min' => 2, 'max' => 15]),
            ],
        ])
        ->add('adresse', TextType::class, [
            'label' => 'adresse',
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'L\'adresse ne peut pas être vide.',
                ]),
                new Assert\Length([
                    'max' => 50,
                    'maxMessage' => 'L\'adresse ne peut pas dépasser 50 caractères.',
                ]),
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
        ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Institution::class,
        ]);
    }
}
