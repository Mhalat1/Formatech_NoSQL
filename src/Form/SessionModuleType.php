<?php

namespace App\Form;

use App\Entity\Module;
use App\Entity\Session;
use App\Entity\Institution;
use App\Entity\SessionModule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SessionModuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('module', EntityType::class, [
                'class' => Module::class,
                'choice_label' => function (Module $module) {
                    return sprintf('%s (du %s au %s)', 
                        $module->getNom(),
                        $module->getDateDebut()->format('d/m/Y'),
                        $module->getDateFin()->format('d/m/Y')
                    );
                },
                'constraints' => [
                    new Callback([$this, 'validateModuleDates']),
                ],
            ])
            ->add('session', EntityType::class, [
                'class' => Session::class,
                'choice_label' => function (Session $session) {
                    return sprintf('%s (du %s au %s)', 
                        $session->getNom(),
                        $session->getDateDebut()->format('d/m/Y'),
                        $session->getDateFin()->format('d/m/Y')
                    );
                },
            ])
            ->add('institution', EntityType::class, [
                'class' => Institution::class,
                'choice_label' => 'nom',
            ])
            ->add('enregistrersessionmodule', SubmitType::class, [
                'label' => 'Créez votre association institution session module ',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SessionModule::class,
            'invalid_message' => 'Les dates du module doivent être comprises dans les dates de la session',
        ]);
    }

    public function validateModuleDates($module, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();
        $session = $form->get('session')->getData();

        if (!$module instanceof Module || !$session instanceof Session) {
            return;
        }

        if ($module->getDateDebut() < $session->getDateDebut()) {
            $context->buildViolation('La date de début du module doit être postérieure ou égale à celle de la session (%date%)')
                   ->setParameter('%date%', $session->getDateDebut()->format('d/m/Y'))
                   ->atPath('form.module')
                   ->addViolation();
        }

        if ($module->getDateFin() > $session->getDateFin()) {
            $context->buildViolation('La date de fin du module doit être antérieure ou égale à celle de la session (%date%)')
                   ->setParameter('%date%', $session->getDateFin()->format('d/m/Y'))
                   ->atPath('form.module')
                   ->addViolation();
        }
    }
}