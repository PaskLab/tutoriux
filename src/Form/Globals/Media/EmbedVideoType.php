<?php

namespace App\Form\Globals\Media;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EmbedVideoType
 * @package App\Form\Component\Media
 */
class EmbedVideoType extends MediaType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('url');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mediabundle_videotype';
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