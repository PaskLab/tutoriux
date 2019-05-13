<?php

namespace App\Form\Site\User;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolver,
    Symfony\Component\Form\Extension\Core\Type\TextType,
    Symfony\Component\Form\Extension\Core\Type\EmailType,
    Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Class ChangeInformationType
 * @package App\Form\Site\User
 */
class ChangeInformationType extends AbstractType
{
    /**
     * Build Form
     *
     * @param FormBuilderInterface $builder The builder
     * @param array                $options Form options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'First Name',
                'required' => false
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Last Name',
                'required' => false
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true
            ])
            ->add('save', SubmitType::class, ['label' => 'Save'])
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'change_information';
    }

    /**
     * Returns the default options for this type.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\User',
            'translation_domain' => 'site',
            'validation_groups' => 'change_information',
        ]);
    }
}