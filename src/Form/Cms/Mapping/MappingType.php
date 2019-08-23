<?php

namespace App\Form\Cms\Mapping;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolver,
    Symfony\Bridge\Doctrine\Form\Type\EntityType,
    Symfony\Component\Form\Extension\Core\Type\ChoiceType,
    Symfony\Component\Validator\Constraints\Valid;

use App\Entity\Navigation;
use Doctrine\ORM\EntityRepository;
use App\Entity\Mapping;

/**
 * Class MappingType
 * @package App\Form\Cms\Mapping
 */
class MappingType extends AbstractType
{
    /**
     * Build Form
     *
     * @param FormBuilderInterface $builder The Builder
     * @param array                $options Array of options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('navigation', EntityType::class, [
                'constraints' => new Valid(),
                'class' => Navigation::class,
                'required' => false,
                'query_builder' => function(EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('n')
                        ->orderBy('n.name', 'ASC');

                    return $qb;
                }
            ])
            ->add('type', ChoiceType::class, [
                // 2.8 to 3.0 transition hack
                'choices_as_values' => true,
                'required' => true,
                'expanded' => true,
                'choices' => [
                    'route' => 'route',
                    'render' => 'render',
                    'include' => 'include',
                    'url' => 'url',
                    'raw' => 'raw'
                ]
            ])
            ->add('target', null, [
                'required' => true
            ])
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'mapping';
    }

    /**
     * Set Default Options
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Mapping::class
        ]);
    }
}