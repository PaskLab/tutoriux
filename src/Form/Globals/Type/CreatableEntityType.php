<?php

namespace App\Form\Globals\Type;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormInterface,
    Symfony\Component\Form\FormView,
    Symfony\Component\OptionsResolver\Exception\MissingOptionsException,
    Symfony\Component\OptionsResolver\OptionsResolver,
    Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * Class CreatableEntityType
 * @package App\Form\Globals\Type
 */
class CreatableEntityType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (null == $options['quick_create_route']) {
            throw new MissingOptionsException('The "quick_create_route" option must be set.');
        }

        $view->vars['quick_create_route'] = $options['quick_create_route'];

        $tokens = explode('\\', $options['class']);
        $view->vars['entity_name'] = array_pop($tokens);
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
        return 'creatable_entity';
    }

    /**
     * Set default options
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'quick_create_route' => null
        ]);
    }
}