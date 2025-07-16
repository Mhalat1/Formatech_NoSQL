<?php

namespace App\Form;

use App\Entity\Institution;
use App\Entity\Invitation;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints as Assert;

class InvitationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

        ->add('email', EmailType::class, [
            'label' => 'Adresse email du destinataire',
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'L\'email est obligatoire'
                ]),
                new Assert\Email([
                    'message' => 'Veuillez saisir un email valide'
                ])
            ],
            'attr' => [
                'required' => true
            ]
        ])
            ->add('token', HiddenType::class)
            ->add('expireLe', DateTimeType::class, [
                'data' => new \DateTimeImmutable('+7 days'),
                'widget' => 'single_text',
                'disabled' => true
            ])
            ->add('creeLe', DateTimeType::class, [
                'label' => false,
                'data' => new \DateTime(),
                'widget' => 'single_text',
                'attr' => [
                    'style' => 'display: none;', 
                ],
                'disabled' => false,
            ])
            ->add('institution', EntityType::class, [
                'class' => Institution::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez une institution...',
                'required' => true,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('i')
                        ->innerJoin('i.utilisateurs', 'u') 
                        ->where('u.id = :userId')
                        ->setParameter('userId', $options['utilisateur_connecté']->getId())
                        ->orderBy('i.nom', 'ASC');
                },
            ])
            ->add('invitedBy', HiddenType::class, [ // Champ caché car valeur automatique
                'data' => $options['utilisateur_connecté']->getId(),
                'mapped' => false
            ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invitation::class,
            'institution' => [], 
            'utilisateur_institution' => [], 
            'utilisateur_connecté' => null,


        ]);    $resolver->setRequired('utilisateur_connecté');

    }
}
