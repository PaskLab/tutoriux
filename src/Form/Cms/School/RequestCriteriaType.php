<?php

namespace App\Form\Cms\School;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolver,
    Symfony\Component\Validator\Constraints\Valid,
    Symfony\Component\Form\Extension\Core\Type\CheckboxType,
    Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use App\Entity\School\RequestCriteria;

/**
 * Class RequestCriteriaType
 * @package App\Form\Cms\School
 */
class RequestCriteriaType extends AbstractType
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
            ->add('active', CheckboxType::class)
            ->add('optional', CheckboxType::class)
            ->add('translation', RequestCriteriaTranslationType::class, [
                'constraints' => new Valid()
            ])
            ->add('contentType', ChoiceType::class, [
                'expanded' => true,
                'multiple' => true,
                'choices' => [
                    'Document' => 'document',
                    'Blog Post' => 'blog'
                ]
            ])
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'request_criteria';
    }

    /**
     * Set default options
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RequestCriteria::class
        ]);
    }
}
