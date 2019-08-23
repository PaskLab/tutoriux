<?php

namespace App\Form\Globals\Media;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DocumentType
 * @package App\Form\Globals\Media
 */
class DocumentType extends MediaType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mediabundle_documenttype';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => 'App\Entity\Media\Media',
            'translation_domain' => 'media_manager'
        ]);
    }
}