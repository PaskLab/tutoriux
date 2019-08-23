<?php

namespace App\Form\Cms\Text;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolver;
use Trsteel\CkeditorBundle\Form\Type\CkeditorType;
use App\Entity\TextTranslation;

/**
 * Class TextMainTranslationType
 * @package App\Form\Cms\Text
 */
class TextMainTranslationType extends AbstractType
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
            ->add('active')
            ->add('name')
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
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TextTranslation::class
        ]);
    }
}
