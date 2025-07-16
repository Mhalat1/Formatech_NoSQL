<?php

namespace App\Form;

use App\Entity\SessionModule;
use App\Entity\Utilisateur;
use App\Entity\UtilisateurInstitutionSessionModule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType; 
class CommentaireModuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('commentaireModule', TextType::class, [
                'label' => 'Commentaire',
                'constraints' => [
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'Le commentaire ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ]
            ])
            ->add('noteModule', NumberType::class, [
                'label' => 'Note',
                'constraints' => [
                    new Assert\Range([
                        'min' => 0,
                        'max' => 20,
                        'notInRangeMessage' => 'La note doit être comprise entre {{ min }} et {{ max }}.',
                    ]),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UtilisateurInstitutionSessionModule::class,
        ]);
    }
}
