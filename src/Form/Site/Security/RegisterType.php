<?php

namespace App\Form\Site\Security;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolver,
    Symfony\Component\Form\FormEvent,
    Symfony\Component\Form\FormEvents,
    Symfony\Component\Form\FormError,
    Symfony\Component\Form\Extension\Core\Type\RepeatedType,
    Symfony\Component\Form\Extension\Core\Type\PasswordType,
    Symfony\Component\Form\Extension\Core\Type\HiddenType,
    Symfony\Component\Form\Extension\Core\Type\CheckboxType,
    Symfony\Component\Form\Extension\Core\Type\EmailType;

/**
 * Class RegisterType
 * @package App\Form\Site\Security
 */
class RegisterType extends AbstractType
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
            ->add('username', null, ['label' => 'Username'])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'first_options' => array('label' => 'Password'),
                'second_options' => array('label' => 'Confirm password')
            ])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('tnc', CheckboxType::class, [
                'mapped' => false
            ])
            ->add('spec1', HiddenType::class, [
                'mapped' => false,
                'data' => date('Y-m-d'),
                'attr' => array('class' => 'spec_date')
            ])
            ->add('spec2', HiddenType::class, [
                'mapped' => false
            ])
        ;

        // Extra Validator

        $extraValidator = [
            'tnc' => function(FormEvent $event){
                $form = $event->getForm();
                $tnc = $form->get('tnc')->getData();
                if (false == $tnc) {
                    $form['tnc']->addError(new FormError("You must read and accept the terms of service and privacy policy."));
                }
            },
            'spec1' => function(FormEvent $event){ // HoneyPot
                $form = $event->getForm();
                $spec1 = $form->get('spec1')->getData();
                if (false == empty($spec1)) {
                    $form['spec1']->addError(new FormError("Error."));
                }
            },
            'spec2' => function(FormEvent $event){ // HoneyPot
                $form = $event->getForm();
                $spec2 = $form->get('spec2')->getData();
                if (false == empty($spec2)) {
                    $form['spec2']->addError(new FormError("Error."));
                }
            }
        ];

        foreach ($extraValidator as $validator) {
            $builder->addEventListener(FormEvents::POST_SUBMIT, $validator);
        }
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'user';
    }

    /**
     * Returns the default options for this type.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\User'
        ]);
    }
}
