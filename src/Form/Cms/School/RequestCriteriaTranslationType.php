<?php

namespace App\Form\Cms\School;

use App\Entity\School\RequestCriteriaTranslation;
use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextareaType,
    Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class RequestCriteriaTranslationType
 * @package App\Form\Cms\School
 */
class RequestCriteriaTranslationType extends AbstractType
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
            ->add('criteria', TextType::class)
            ->add('details', TextareaType::class)
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'request_criteria_translation';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RequestCriteriaTranslation::class
        ]);
    }
}
