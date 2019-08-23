<?php

namespace App\Form\Cms\Section;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolver,
    Symfony\Bridge\Doctrine\Form\Type\EntityType,
    Symfony\Component\Validator\Constraints\Valid;
use App\Entity\Navigation;
use App\Entity\Section;

/**
 * Class SectionType
 * @package App\Form\Cms\Section
 */
class SectionType extends AbstractType
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
            ->add('translation', SectionTranslationType::class, [
                'constraints' => new Valid()
            ])
            ->add('icon')
            ->add('removeFromUrl', null, [
                'label' => 'Remove from URL',
                'attr' => [
                    'alt' => 'This action can cause Url\'s inconsistency.'
                ]
            ])
            ->add('parent', null, [
                'placeholder' => '',
                'empty_data' => null,
                'required' => false,
                'query_builder' => function(EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('s')->select('s', 'st');

                    if ($options['current_section'] && $options['current_section']->getId()) {
                        $qb->where('s.materializedPath NOT LIKE :path')
                            ->setParameter('path', $options['current_section']->getNodeId().'%');
                    }

                    $qb->innerJoin('s.translations', 'st');

                    if ($options['current_locale']) {
                        $qb->andWhere('st.locale = :locale')
                            ->setParameter('locale', $options['current_locale'])
                        ;
                    }

                    return $qb;
                }
            ])
            ->add('navigations', EntityType::class, [
                'multiple' => true,
                'expanded' => true,
                'class' => Navigation::class,
                'required' => false,
                'query_builder' => function(EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('n');
                    // excluding internal navigations that starts with an underscore
                    $qb->where($qb->expr()->neq($qb->expr()->substring('n.code', 1, 1), ':prefix'))
                        ->setParameter('prefix', '_')
                        ->orderBy('na.name', 'ASC')
                        ->addOrderBy('n.name', 'ASC');

                    return $qb;
                }
            ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'section';
    }

    /**
     * Set Default Options
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Section::class,
            'current_section' => null,
            'current_locale' => null
        ]);
    }
}