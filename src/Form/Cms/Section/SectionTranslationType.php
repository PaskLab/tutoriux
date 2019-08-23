<?php

namespace App\Form\Cms\Section;

use Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolver;
use Tutoriux\DoctrineBehaviorsBundle\Form\MetadatableType;
use App\Entity\SectionTranslation;

/**
 * Class SectionTranslationType
 * @package App\Form\Cms\Section
 */
class SectionTranslationType extends MetadatableType
{
    /**
     * Build Form
     *
     * @param FormBuilderInterface $builder The Builder
     * @param array                $options Array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('active')
            ->add('name')
            ->add('slug')
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'section_translation';
    }

    /**
     * Set default options
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SectionTranslation::class
        ]);
    }
}
