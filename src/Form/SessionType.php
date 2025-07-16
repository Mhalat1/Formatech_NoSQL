<?php
namespace App\Form;

use App\Entity\Session;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom de la session'])
            ->add('type', TextType::class, ['label' => 'Type de la session'])
            ->add('date_debut', DateType::class, ['label' => 'Date de début', 'widget' => 'single_text'])
            ->add('date_fin', DateType::class, ['label' => 'Date de fin', 'widget' => 'single_text'])
            ->add('description', TextareaType::class, ['label' => 'Description de la session']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
        ]);
    }
}
