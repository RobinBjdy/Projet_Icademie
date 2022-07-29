<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

use Symfony\Component\Form\CallbackTransformer;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                "label" => "* Nom :",
            ])
            ->add('prenom', TextType::class, [
                "label" => "* Prénom :",
            ])
            ->add('email', EmailType::class, [
                "label" => "* Email :",
            ])
            ->add('password', RepeatedType::class, [
                "type" => PasswordType::class,
                'invalid_message' => "Les mots de passes doivet êtres identiques.",
                "options" => ["attr" => ["class" => "password-field"]],
                "first_options"  => ["label" => "Mot de passe"],
                "second_options" => ["label" => "Confirmer le mot de passe"],
            ])
            ->add('roles', ChoiceType::class, [
                "label" => "* Role",
                "choices" => ['Admin' => 'ROLE_ADMIN', 'Utilisateur' => 'ROLE_USER'],
                "multiple" => false
            ])
            ->add('Valider', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary btn-color float-end'],
            ]);
        ;

        // Data transformer
        $builder->get('roles')
                ->addModelTransformer(new CallbackTransformer(
                function ($rolesArray) {
                     // transform the array to a string
                     return count($rolesArray)? $rolesArray[0]: null;
                },
                function ($rolesString) {
                     // transform the string back to an array
                     return [$rolesString];
                }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
