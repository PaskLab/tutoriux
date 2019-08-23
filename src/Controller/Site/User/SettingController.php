<?php

namespace App\Controller\Site\User;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\UserSetting;
use App\Form\Site\User\ChangePasswordType;
use App\Form\Site\User\ChangeAvatarType;
use App\Form\Site\User\ChangeLanguageType;
use App\Library\BaseController;
use App\Entity\User;
use App\Form\Site\User\ChangeDescriptionType;
use App\Form\Site\User\ChangeGenderType;
use App\Form\Site\User\ChangeReferenceType;
use App\Form\Site\User\ChangeInformationType;
use App\Services\ApplicationCore;
use App\Services\DoctrineInit;

/**
 * Class SettingController
 * @package App\Controller\Site\User
 */
class SettingController extends BaseController
{
    const STANDARD_SUCCESS_MESSAGE = 'Your informations has been changed!',
          STANDARD_ERROR_MESSAGE = 'Sorry, we have not been able to change your informations...',
          CONTACT_SUPPORT_MESSAGE = 'Try again or contact technical support.';

    /**
     * @var boolean
     */
    private $isValid;

    /**
     * SettingController constructor.
     * @param ApplicationCore $applicationCore
     * @param DoctrineInit $doctrineInit
     */
    public function __construct(ApplicationCore $applicationCore, DoctrineInit $doctrineInit)
    {
        $this->isValid = false;
        parent::__construct($applicationCore, $doctrineInit);
    }

    /**
     * Add standard flash success message
     */
    private function addFlashSuccessMessage()
    {
        $this->addFlashSuccess(self::STANDARD_SUCCESS_MESSAGE);
    }

    /**
     * Add standard flash error message
     */
    private function addFlashErrorMessage()
    {
        $this->addFlashError(self::STANDARD_ERROR_MESSAGE);
        $this->addFlashError(self::CONTACT_SUPPORT_MESSAGE);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function setting(Request $request)
    {
        $user = $this->getDoctrine()->getRepository('SystemBundle:User')
            ->find($this->getUser()->getId());

        $settings = $user->getSettings();

        $this->createAndPushNavigationElement(
            $user->getFullName(),
            'section_id_35',
            ['username' => $user->getUsername()]
        );
        $this->createAndPushNavigationElement(
            'Account Settings',
            'section_id_35_settings'
        );

        if (!$settings) {
            $settings = new UserSetting();
            $user->setSettings($settings);
        }

        $formsViews = [
            'informationForm' => $this->handleInformationForm($request, $user),
            'passwordForm' => $this->handlePasswordForm($request, $user),
            'avatarForm' => $this->handleAvatarForm($request, $user),
            'languageForm' => $this->handleLanguageForm($request, $user),
            'genderForm' => $this->handleGenderForm($request, $user),
            'descriptionForm' => $this->handleDescriptionForm($request, $user->getSettings()),
            'referenceForm' => $this->handleReferenceForm($request, $user->getSettings())
        ];

        if ('POST' === $request->getMethod() && $this->isValid) {

            if ($request->request->has('change_language')) {
                return $this->redirect($this->generateUrl('section_id_35_settings', [
                    '_locale' => $user->getLocale()
                ]));
            }

            return $this->redirect($this->generateUrl('section_id_35_settings'));
        }

        return $this->render('SystemBundle:Frontend/User:setting.html.twig', array_merge([
            'user' => $user
        ], $formsViews));
    }

    /**
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\Form\FormView
     */
    private function handleInformationForm(Request $request, User $user)
    {
        $form = $this->createForm(ChangeInformationType::class, $user);

        if ('POST' === $request->getMethod() && $request->request->has('change_information')) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getEm()->flush();

                $this->addFlashSuccessMessage();
                $this->isValid = true;

            } else {
                $this->addFlashErrorMessage();
            }
        }

        return $form->createView();
    }

    /**
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\Form\FormView
     */
    private function handlePasswordForm(Request $request, User $user)
    {
        $form = $this->createForm(ChangePasswordType::class, $user);

        if ('POST' === $request->getMethod() && $request->request->has('change_password')) {

            $currentPassword = $user->getPassword();
            $form->handleRequest($request);

            // Encode current password
            $encoder = $this->get('security.encoder_factory')->getEncoder($user);
            $currentPasswordForm = $encoder->encodePassword($form->get('currentPassword')->getData(), $user->getSalt());

            if ($currentPassword != $currentPasswordForm) {
                $form->get('currentPassword')->addError(new FormError('Wrong password.'));
            }

            if ($form->isValid()) {

                // Encode new password
                $encoder = $this->get('security.encoder_factory')->getEncoder($user);
                $encodedPassword = $encoder->encodePassword($user->getPassword(), $user->getSalt());

                $user->setPassword($encodedPassword);
                $this->getEm()->flush();

                $this->addFlashSuccess('Your password has been changed!');
                $this->isValid = true;

            } else {
                $this->addFlashError('We have not been able to change your password, please check all fields.');
            }
        }

        return $form->createView();
    }

    /**
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\Form\FormView
     */
    private function handleAvatarForm(Request $request, User $user)
    {
        $form = $this->createForm(ChangeAvatarType::class, $user);

        if ('POST' === $request->getMethod() && $request->request->has('change_avatar')) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getEm()->flush();

                $this->addFlashSuccess('Your avatar has been changed!');
                $this->isValid = true;

            } else {
                $this->addFlashError('Sorry, we have not been able to change your avatar...');
                $this->addFlashError(self::CONTACT_SUPPORT_MESSAGE);
            }
        }

        return $form->createView();
    }

    /**
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\Form\FormView
     */
    private function handleLanguageForm(Request $request, User $user)
    {
        $form = $this->createForm(ChangeLanguageType::class, $user);

        if ('POST' === $request->getMethod() && $request->request->has('change_language')) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getEm()->flush();

                $this->addFlashSuccess('The default interface language has been changed!');
                $this->isValid = true;

            } else {
                $this->addFlashError('Sorry, we have not been able to change your default language...');
                $this->addFlashError(self::CONTACT_SUPPORT_MESSAGE);
            }
        }

        return $form->createView();
    }

    /**
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\Form\FormView
     */
    private function handleGenderForm(Request $request, User $user)
    {
        $form = $this->createForm(ChangeGenderType::class, $user);

        if ('POST' === $request->getMethod() && $request->request->has('change_gender')) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getEm()->flush();

                $this->addFlashSuccessMessage();
                $this->isValid = true;

            } else {
                $this->addFlashErrorMessage();
            }
        }

        return $form->createView();
    }

    /**
     * @param Request $request
     * @param UserSetting $userSetting
     * @return \Symfony\Component\Form\FormView
     */
    private function handleDescriptionForm(Request $request, UserSetting $userSetting)
    {
        $form = $this->createForm(ChangeDescriptionType::class, $userSetting, [
            'validation_groups' => 'setting_description'
        ]);

        if ('POST' === $request->getMethod() && $request->request->has('change_description')) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getEm()->flush();

                $this->addFlashSuccessMessage();
                $this->isValid = true;

            } else {
                $this->addFlashErrorMessage();
            }
        }

        return $form->createView();
    }

    /**
     * @param Request $request
     * @param UserSetting $userSetting
     * @return \Symfony\Component\Form\FormView
     */
    private function handleReferenceForm(Request $request, UserSetting $userSetting)
    {
        $form = $this->createForm(ChangeReferenceType::class, $userSetting, [
            'validation_groups' => 'setting_url'
        ]);

        if ('POST' === $request->getMethod() && $request->request->has('change_reference')) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getEm()->flush();

                $this->addFlashSuccessMessage();
                $this->isValid = true;

            } else {
                $this->addFlashErrorMessage();
            }
        }

        return $form->createView();
    }
}