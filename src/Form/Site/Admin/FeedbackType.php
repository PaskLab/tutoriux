<?php

namespace App\Form\Site\Admin;

use Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\OptionsResolver\OptionsResolver,
    Symfony\Component\Form\FormEvent,
    Symfony\Component\Form\FormEvents,
    Symfony\Component\Form\FormError,
    Symfony\Component\Validator\Validation,
    Symfony\Component\Validator\Constraints\Length,
    Symfony\Component\Validator\Constraints\Email,
    Symfony\Component\Validator\Constraints\NotBlank,
    Symfony\Component\Form\Extension\Core\Type\TextType,
    Symfony\Component\Form\Extension\Core\Type\EmailType,
    Symfony\Component\Form\Extension\Core\Type\TextareaType,
    Symfony\Component\Form\Extension\Core\Type\HiddenType,
    Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Class FeedbackType
 * @package AdminBundle\Form\Frontend
 */
class FeedbackType extends AbstractType
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
            ->add('fullname', TextType::class, [
                'label' => 'Your Name',
                'required' => true,
                'mapped' => false
            ])
            ->add('email', EmailType::class, [
                'label' => 'Your Email',
                'required' => true,
                'mapped' => false
            ])
            ->add('subject', TextType::class, [
                'label' => 'Subject',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'alt' => 'Suggestions, bugs, ideas, etc.'
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Your Comment',
                'required' => true,
                'mapped' => false,
                'attr' => [
                    'rows' => 4
                ]
            ])
            ->add('spec1', HiddenType::class, [
                'mapped' => false,
                'data' => date('Y-m-d'),
                'attr' => ['class' => 'spec_date']
            ])
            ->add('spec2', HiddenType::class, [
                'mapped' => false
            ])
            ->add('width', HiddenType::class, [
                'mapped' => false
            ])
            ->add('send', SubmitType::class, ['label' => 'Send'])
        ;

        // Extra Validator

        $extraValidator = [
            'fullname' => function(FormEvent $event) use ($options) {
                $form = $event->getForm();
                $validator = Validation::createValidator();
                $violations = $validator->validate($form->get('fullname')->getData(), [
                    new NotBlank()
                ]);
                if ($violations->count()) {
                    foreach ($violations as $violation) {
                        $form['fullname']->addError(new FormError($options['translator']->trans($violation->getMessage(), [], 'validators')));
                    }
                }
            },
            'email' => function(FormEvent $event) use ($options) {
                $form = $event->getForm();
                $validator = Validation::createValidator();
                $violations = $validator->validate($form->get('email')->getData(), [
                    new NotBlank(),
                    new Email()
                ]);
                if ($violations->count()) {
                    foreach ($violations as $violation) {
                        $form['email']->addError(new FormError($options['translator']->trans($violation->getMessage(), [], 'validators')));
                    }
                }
            },
            'message' => function(FormEvent $event) use ($options) {
                $form = $event->getForm();
                $validator = Validation::createValidator();
                $violations = $validator->validate($form->get('message')->getData(), [
                    new NotBlank(),
                    new Length([
                        'max' => 10000
                    ]),
                ]);
                if ($violations->count()) {
                    foreach ($violations as $violation) {
                        $form['message']->addError(new FormError($options['translator']->trans($violation->getMessage(), [], 'validators')));
                    }
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
        return 'feedback';
    }

    /**
     * Set Default Options
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin',
            'translator' => null
        ]);
    }
}