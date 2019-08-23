<?php

namespace App\Form\Cms\Role;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolver,
    Symfony\Component\Validator\Constraints\Valid;
use App\Form\Globals\Type\TreeChoiceType;
use App\Entity\Section;
use App\Entity\Role;

/**
 * Class RoleType
 * @package App\Form\Cms\Role
 */
class RoleType extends AbstractType
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
            ->add('translation', RoleTranslationType::class, [
                'constraints' => new Valid()
            ])
        ;

        if (!$options['admin']) {
            $builder->add('sections', TreeChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'property' => 'name',
                'class'    => Section::class,
                'required' => false
            ]);
        }
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'role';
    }

    /**
     * Returns the default options for this type.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Role::class,
            'admin' => false
        ]);
    }
}
