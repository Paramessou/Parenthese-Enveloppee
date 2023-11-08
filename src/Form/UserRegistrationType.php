<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Regex;

class UserRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('adresse')
            ->add('codePostal', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un code postal']),
                    new Regex(['pattern' => '/^([0-9]{5})$/', 'message' => 'Le code postal doit être composé de 5 chiffres'])
                ],
            ])
            ->add('ville')
            ->add('password', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un mot de passe']),
                    new Length(['min' => 8, 'minMessage' => 'Votre mot de passe doit contenir au moins 8 caractères'])
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir une adresse email']),
                    new Email(['message' => 'Veuillez saisir une adresse email valide'])
                ],
            ])
            ->add('numeroTelephone')
            ->add('genre', ChoiceType::class, [
                'choices' => [
                    'Homme' => 'Homme',
                    'Femme' => 'Femme',
                    'Autre' => 'Autre'
                ],
                'expanded' => true,
                'multiple' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner un genre'])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'constraints' => [
                new UniqueEntity([
                    'fields' => 'email', 'message' => 'Cette adresse email est déjà utilisée'
                ])
            ]
        ]);
    }
}
