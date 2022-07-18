<?php

namespace App\Domain\User\Form;

use App\Domain\User\Command\ChangePassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'currentPassword',
                PasswordType::class,
                [
                    'label' => 'change_password.form.current_password',
                    'required' => true,
                    'attr'     => ['autocomplete' => 'current-password', 'class' => 'form-control'],
                ]
            )
            ->add(
                'password',
                RepeatedType::class,
                [
                    'type'           => PasswordType::class,
                    'first_options'  => [
                        'label' => 'change_password.form.new_password',
                        'required' => true,
                        'attr'     => ['autocomplete' => 'new-password', 'class' => 'form-control'],
                    ],
                    'second_options' => [
                        'label' => 'change_password.form.new_password_repeat',
                        'required' => true,
                        'attr'     => ['autocomplete' => 'new-password', 'class' => 'form-control'],
                    ],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ChangePassword::class,
            ]
        );
    }
}
