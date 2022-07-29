<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Materiel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints\File;

class MaterielType extends AbstractType
{
    private $file;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, [
                "label" => "* Nom :",
            ])
            ->add('categorie', EntityType::class, [
                "label" => "* Catégorie :",
                "class" => Categorie::class,
            ])
            ->add('disponible', CheckboxType::class, [
                "label" => "Disponible",
                "required" => false,
            ])
            ->add('reservation', CheckboxType::class, [
                "label" => "Réservable",
                "required" => false,
                'attr' => ['class' => 'checkboxy'],
            ])
            ->add('file', FileType::class, [
                "label" => "Choisir une image :",
                "mapped" => false,
                "required" => false,
                "constraints" => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png'
                        ]
                    ])
                ],
                'help' => 'Une image au format jpg/png d\'une taille maximale de 2Mo est requise.',
                'data_class' => null
            ])
            ->add('Valider', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary btn-color float-end'],
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Materiel::class,
        ]);
    }
}
