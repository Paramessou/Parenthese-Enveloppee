<?php

namespace App\Form;

use App\Entity\Appointment;
use App\Entity\Service;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('serviceId', EntityType::class, [
                'class' => Service::class,
                'label' => 'Prestation',
                'attr' => ['id' => 'appointment_serviceId'],
                'choice_attr' => function (Service $service) {
                    return ['data-duration' => $service->getDuree()];
                },
            ])
            ->add('debut', DateTimeType::class, [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'label' => 'Début',
            ])
            ->add('fin', DateTimeType::class, [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'label' => 'Fin',
                'disabled' => true,
            ])
            ->add('userId', TextType::class, [
                'data' => $options['user']->getNom() . ' ' . $options['user']->getPrenom(),
                'label' => 'Nom',
                'disabled' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
            'user' => null,
        ]);
    }
}
