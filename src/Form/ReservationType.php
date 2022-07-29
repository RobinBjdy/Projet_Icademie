<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('debut', DateTimeType::class, array(
                'widget' => 'single_text',
                'required' => true,
                'label' => "Début"))
            ->add('fin', DateTimeType::class, array(
                'widget' => 'single_text',
                'required' => true,
                'label' => "Fin"))
            ->add('Valider', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary btn-color float-end'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
