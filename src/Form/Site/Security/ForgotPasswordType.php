<?php

namespace App\Form\Site\Security;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolver,
    Symfony\Component\Form\FormEvent,
    Symfony\Component\Form\FormEvents,
    Symfony\Component\Form\FormError,
    Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class ForgotPasswordType
 * @package App\Form\Site\Security
 */
class ForgotPasswordType extends AbstractType
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
            ->add('email', null, [
                'label' => 'Email',
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
        return 'forgot_password';
    }

    /**
     * Returns the default options for this type.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
