<?php

namespace App\Form\Cms\Text;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolver,
    Symfony\Component\Validator\Constraints\Valid;
use App\Entity\Text;

/**
 * Class TextMainType
 * @package App\Form\Cms\Text
 */
class TextMainType extends AbstractType
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
            ->add('collapsable')
            ->add('translation', TextMainTranslationType::class, [
                'constraints' => new Valid()
            ])
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'text';
    }

    /**
     * Set default options
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Text::class
        ]);
    }
}
