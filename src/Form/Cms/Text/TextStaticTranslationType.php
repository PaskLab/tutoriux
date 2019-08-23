<?php

namespace App\Form\Cms\Text;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolver,
    Symfony\Component\Form\Extension\Core\Type\CheckboxType,
    Trsteel\CkeditorBundle\Form\Type\CkeditorType;
use App\Entity\TextTranslation;

/**
 * Class TextStaticTranslationType
 * @package App\Form\Cms\Text
 */
class TextStaticTranslationType extends AbstractType
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
            ->add('active', CheckboxType::class)
            ->add('text', CkeditorType::class)
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'text_translation';
    }

    /**
     * Set default options
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TextTranslation::class
        ]);
    }
}
