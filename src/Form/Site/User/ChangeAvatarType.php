<?php

namespace App\Form\Site\User;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolver,
    Symfony\Component\Form\Extension\Core\Type\SubmitType,
    App\Form\Globals\Media\MediaSelectType;

/**
 * Class ChangeAvatarType
 * @package App\Form\Site\User
 */
class ChangeAvatarType extends AbstractType
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
            ->add('avatar', MediaSelectType::class)
            ->add('save', SubmitType::class, ['label' => 'Save'])
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'change_avatar';
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