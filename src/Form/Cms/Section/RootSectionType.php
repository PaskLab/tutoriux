<?php

namespace App\Form\Cms\Section;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class RootSectionType
 * @package App\Form\Cms\Section
 */
class RootSectionType extends SectionType
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
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'root_section';
    }
}
