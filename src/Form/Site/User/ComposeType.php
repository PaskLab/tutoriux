<?php

namespace App\Form\Site\User;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolver,
    Symfony\Component\Form\Extension\Core\Type\TextType,
    Trsteel\CkeditorBundle\Form\Type\CkeditorType;

/**
 * Class ComposeType
 * @package SystemBundle\Form\Frontend\User
 */
class ComposeType extends AbstractType
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
            ->add('title', TextType::class)
            ->add('message', CkeditorType::class, [
                'height' => 250,
                'toolbar' => ['oops', 'basicstyles', 'paragraph', 'insert', 'styles'],
                'toolbar_groups' => [
                    'oops' => ['Undo', 'Redo'],
                    'basicstyles' => ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'],
                    'paragraph' => ['NumberedList', 'BulletedList', 'Blockquote', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock',],
                    'insert' => ['Insert_media', '-', 'Link', 'Unlink'],
                    'styles' => ['Format', 'Styles'],
                    'tools' => ['Maximize', 'ShowBlocks'],
                ],
                'contents_css' => [
                    '/bundles/system/assets/global/plugins/bootstrap/css/bootstrap.min.css',
                    '/bundles/system/assets/global/css/components-rounded.css',
                    '/bundles/system/assets/admin/layout3/css/layout.css'
                ],
                'custom_config' => '/bundles/system/frontend/js/ckeditor/custom_config.js',
                'styles_set' => 'tutoriux:/bundles/system/frontend/js/ckeditor/styles_set.js'
            ])
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        // Be careful changing that value, JsValidation or other script can rely on that one
        return 'compose';
    }

    /**
     * Returns the default options for this type.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Message',
            'translation_domain' => 'site'
        ]);
    }
}