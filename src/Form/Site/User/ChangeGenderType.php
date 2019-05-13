<?php

namespace App\Form\Site\User;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolver,
    Symfony\Component\Form\Extension\Core\Type\ChoiceType,
    Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Class ChangeGenderType
 * @package App\Form\Site\User
 */
class ChangeGenderType extends AbstractType
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
            ->add('gender', ChoiceType::class, [
                'choices_as_values' => true,
                'label' => 'Select your gender',
                'choices' => [
                    'f' => 'Female',
                    'm' => 'Male'
                ],
                'expanded' => true,
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
        return 'change_gender';
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
            'translation_domain' => 'site'
        ]);
    }
}