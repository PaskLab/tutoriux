<?php

namespace App\Form\Cms\Locale;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolver,
    Symfony\Component\Form\Extension\Core\Type\LocaleType as LocType;
use App\Entity\Locale;

/**
 * Class LocaleType
 * @package App\Form\Cms\Locale
 */
class LocaleType extends AbstractType
{

    /**
     * Build Form
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('active')
            ->add('name', null, ['attr' => ['alt' => 'Displayed on the language switcher']])
            ->add('code', LocType::class, [
                'label' => 'Language',
                'preferred_choices' => array('en', 'fr', 'es'),
                'attr' => ['alt' => 'exemple.com/']
            ])
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'locale';
    }

    /**
     * Set default options
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Locale::class
        ]);
    }
}
