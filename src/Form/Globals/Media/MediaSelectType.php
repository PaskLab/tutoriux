<?php

namespace App\Form\Globals\Media;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormInterface,
    Symfony\Component\Form\FormView,
    Symfony\Component\OptionsResolver\OptionsResolver,
    Symfony\Component\PropertyAccess\PropertyAccess,
    Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * Class MediaSelectType
 * @package App\Form\Globals\Media
 */
class MediaSelectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $media = null;
        $parentData = $form->getParent()->getData();

        if (null != $options['media_method']) {
            if (null !== $parentData) {
                $accessor = PropertyAccess::createPropertyAccessor();
                $media = $accessor->getValue($parentData, $options['media_method']);
            }
        } else {
            $media = $form->getData();
        }

        $view->vars['media'] = $media;
        $view->vars['type'] = $options['type'];
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'media_select';
    }

    /**
     * Set default options
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => 'App\Entity\Media\Media',
            'translation_domain' => 'media_manager',
            'media_method' => null,
            'type' => 'image'
        ]);
    }
}