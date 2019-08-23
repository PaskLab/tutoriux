<?php

namespace App\Form\Cms\Text;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Text;

/**
 * Class TextStaticType
 * @package App\Form\Cms\Text
 */
class TextStaticType extends AbstractType
{
    /**
     * Build Form
     *
     * @param FormBuilderInterface $builder The builder
     * @param array                $options Form options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('translation', TextStaticTranslationType::class);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'texte';
    }

    /**
     * Set default options
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Text::class,
            'cascade_validation' => true
        ]);
    }
}
