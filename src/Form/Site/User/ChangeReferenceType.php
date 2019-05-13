<?php

namespace App\Form\Site\User;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolver,
    Symfony\Component\Form\Extension\Core\Type\UrlType,
    Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Class ChangeReferenceType
 * @package App\Form\Site\User
 */
class ChangeReferenceType extends AbstractType
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
            ->add('website', UrlType::class, [
                'required' => false,
                'label' => 'Website'
            ])
            ->add('facebook', UrlType::class, [
                'required' => false,
                'label' => 'Facebook'
            ])
            ->add('twitter', UrlType::class, [
                'required' => false,
                'label' => 'Twitter'
            ])
            ->add('google', UrlType::class, [
                'required' => false,
                'label' => 'Google+'
            ])
            ->add('save', SubmitType::class, ['label' => 'Save'])
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        // Be careful changing that value, JsValidation or other script can rely on that one
        return 'change_reference';
    }

    /**
     * Returns the default options for this type.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\UserSetting',
            'translation_domain' => 'site'
        ]);
    }
}