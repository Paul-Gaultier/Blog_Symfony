<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title',TextType::class, [
                'label' => 'Titre',
                'required' => true,
                'attr' => ['placeholder' => 'Entrez un titre']
            ])

            ->add('subtitle',TextType::class, [
                'label' => 'Soustitre',
                'required' => true,
                'attr' => ['placeholder' => 'Entrez un soustitre'],
                'constraints' => [

                    new NotBlank([

                        'message' => 'Ce champs ne peut être vide'
                    ]),

                    new length([

                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Le sous-titre doit comporter {{Limit} caractères au minimum.'
                    ])
                ]
            ])
            ->add('description',TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => ['placeholder' => 'Entrez unse description']
            ])

            ->add('category', EntityType::class,[

                'class'=>Category::class,
                'choice_label'=>'name',
                'label'=>"Choissisez la catégorie de l'article"
            ])
            ->add('picture',FileType::class, [
                'label' => 'Photo',
                'required' => true,
                'attr' => ['placeholder' => 'Entrez une illustration'],
                'constraints'=> [

                    new Image([
                        'mimeTypes' => ['image/jpeg' , 'image/png'],
                        'mimeTypesMessage' => 'Les types de fichiers autorisés sont : .jpeg / .png'
                    ])
                ]

            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Ajouter',
                'attr' => ['class' => 'btn btn-warning d-block mx-auto my-3 col-4']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([// La configuration des paramètres par defaut
            'data_class' => Article::class, //La liaison entre le formulaire et l'entité class se fait ici 
            /*
            On rajoute 'allow_file_upload' (une cléf d'une paire ($key => $value) dans un array)qui est un paramètre symfony
            Qu'on définit à true.
            Cela permet d'autoriser notre formulaire à importer des fichiers.
            => revient à <form enctype=multipart/form-data> 
            */
            'allow_file_upload' => true, //décommenter cette ligne permet d'autoriser l'upload des images
//            'picture' => null,
        ]);
    }
}
