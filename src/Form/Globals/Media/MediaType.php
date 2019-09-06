<?php

namespace App\Form\Globals\Media;

use Symfony\Component\Form\{AbstractType, FormBuilderInterface};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\{TextType, UrlType, TextareaType};

/**
 * Class MediaType
 * @package App\Form\Component\Media
 */
class MediaType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Name'
            ])
            ->add('credit', TextType::class, [
                'required' => false,
                'label' => 'Credit',
                'attr' => [
                    'alt' => 'Author or media owner'
                ]
            ])
            ->add('sourceUrl', UrlType::class, [
                'required' => false,
                'label' => 'Source URL',
            ])
            ->add('sourceDetails', TextareaType::class, [
                'required' => false,
                'label' => 'Source Details',
                'attr' => [
                    'alt' => 'More information, going from usage authorisations to the way the media has been produced'
                ]
            ])
            ->add('caption', TextareaType::class, [
                'required' => false,
                'label' => 'Caption',
                'attr' => [
                    'alt' => 'Will be displayed with the media'
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Description',
                'attr' => [
                    'alt' => 'Description of what is contained in this media'
                ]
            ])
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mediabundle_mediatype';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Media\Media',
            'translation_domain' => 'media_manager'
        ]);
    }
}