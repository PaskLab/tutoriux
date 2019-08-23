<?php

namespace App\Form\Cms\Role;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\RoleTranslation;

/**
 * Class RoleTranslationType
 * @package App\Form\Cms\Role
 */
class RoleTranslationType extends AbstractType
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
            ->add('name', null, ['label' => 'Rolefullname', 'attr' => ['alt' => 'Ex: Administrateur']])
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'role_translation';
    }

    /**
     * Returns the default options for this type.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RoleTranslation::class
        ]);
    }
}
