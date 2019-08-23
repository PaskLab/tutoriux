<?php

namespace App\Form\Cms\User;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolver,
    Symfony\Component\Form\Extension\Core\Type\RepeatedType,
    Symfony\Bridge\Doctrine\Form\Type\EntityType,
    Symfony\Component\Form\Extension\Core\Type\ChoiceType,
    Symfony\Component\Form\Extension\Core\Type\CheckboxType,
    Symfony\Component\Form\Extension\Core\Type\TextType,
    Symfony\Component\Form\Extension\Core\Type\EmailType,
    Symfony\Component\Form\Extension\Core\Type\PasswordType,
    Doctrine\ORM\EntityRepository;
use App\Form\Globals\Media\MediaSelectType;
use App\Entity\Role;
use App\Entity\User;

/**
 * Class UserType
 * @package App\Form\Cms\User
 */
class UserType extends AbstractType
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
            ->add('active', CheckboxType::class, ['disabled' => $options['self_edit']])
            ->add('username', TextType::class)
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat']
            ])
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
            ->add('email', EmailType::class)
            ->add('userRoles', EntityType::class, [
                'class' => Role::class,
                'expanded' => true,
                'multiple' => true,
                'label' => 'Roles',
                'query_builder' => function(EntityRepository $repo) use ($options) {
                    $repo->setReturnQueryBuilder(true);

                    if ($options['developer']) {
                        return $repo->findAllExcept('ROLE_BACKEND_ACCESS');
                    } else {
                        return $repo->findAllExcept(['ROLE_DEVELOPER', 'ROLE_BACKEND_ACCESS']);
                    }
                }
            ])
            ->add('locale', ChoiceType::class, [
                'required' => false,
                'label' => 'Language',
                'choices' => $this->getSupportedBackendLanguages(),
                // 2.7, 3.0 transition hack
                'choices_as_values' => true
            ])
            ->add('avatar', MediaSelectType::class)
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
       return 'user';
    }

    /**
     * Get the currently supported languages of the backend application
     *
     * @return array
     */
    protected function getSupportedBackendLanguages()
    {
        return [
            'en' => 'English',
            'fr' => 'French'
        ];
    }

    /**
     * Returns the default options for this type.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'self_edit' => false,
            'developer' => false,
            'error_mapping' => ['roles' => 'userRoles']
        ]);
    }
}
