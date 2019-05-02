<?php

namespace App\Controller\Site\User;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

use App\Library\BaseController;
use App\Entity\User;
use App\Entity\Role;
use App\Form\Site\Security\RegisterType;
use App\Form\Site\Security\ForgotPasswordType;

/**
 * Class RegisterController
 * @package App\Controller\Site\User
 */
class RegisterController extends BaseController
{
    /**
     * @param Request $request
     * @param EncoderFactoryInterface $encoderFactory
     * @param TranslatorInterface $translator
     * @param \Swift_Mailer $mailer
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function register(Request $request, EncoderFactoryInterface $encoderFactory,
                             TranslatorInterface $translator, \Swift_Mailer $mailer)
    {
        $user = new User();
        $user->setActive(false)
            ->setLocale($request->getLocale());

        // Forgot password section

        $forgotForm = $this->createForm(ForgotPasswordType::class, null, [
            'translation_domain' => 'site'
        ]);

        // Register section

        $form = $this->createForm(RegisterType::class, $user, array(
            'validation_groups' => 'new',
            'translation_domain' => 'site'
        ));

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                // All Users are automatically granted the ROLE_USER Role
                $userRole = $this->getRepository(Role::class)
                    ->findOneBy(array('role' => 'ROLE_USER'));
                if (!$userRole) {
                    $userRole = new Role();
                    $userRole->setRole('ROLE_USER');
                    $userRole->translate('fr')->setName('Utilisateur');
                    $userRole->translate('en')->setName('User');
                    $this->getEm()->persist($userRole);
                }

                $user->setActive(false)
                     ->addRole($userRole);

                // New password set
                $encoder = $encoderFactory->getEncoder($user);
                $encodedPassword = $encoder->encodePassword($user->getPassword(), $user->getSalt());

                $user->setPassword($encodedPassword);


                $this->getEm()->persist($user);
                $this->getEm()->flush();

                $this->sendRegisterConfirmEmail($user, $translator, $mailer);

                $this->get('session')->getFlashBag()->add('info', $translator->trans(
                    'A confirmation request has been sent to %email%.',
                    ['%email%' => $user->getEmail()],
                    'site'
                ));

                return $this->redirect($this->generateUrl('site_login'));

            } else {
                $this->addFlashError($translator->trans('Some fields are invalid.', [], 'site'));
            }
        }

        return $this->render('site/security/login.html.twig', [
            'last_username' => null,
            'error' => $request->getSession()->get(Security::AUTHENTICATION_ERROR),
            'registerForm' => $form->createView(),
            'forgotForm' => $forgotForm->createView()
        ]);
    }

    /**
     * @param User $user
     * @param TranslatorInterface $translator
     * @param \Swift_Mailer $mailer
     * @throws \Exception
     */
    public function sendRegisterConfirmEmail(User $user, TranslatorInterface $translator, \Swift_Mailer $mailer)
    {
        $hash = md5(uniqid());

        $expiration = new \DateTime();

        $expiration->add(new \DateInterval('P1D'));

        $user->setHash($hash)->setHashCreatedAt($expiration);

        $this->getDoctrine()->getManager()->flush();

        $message = (new \Swift_Message())
            ->setSubject('Tutoriux - ' . $translator->trans('Account Creation Confirmation', [], 'site'))
            ->setFrom($this->getParameter('app.system_email'))
            ->setTo($user->getEmail())
            ->setBody($this->renderView('site/security/register_confirm_email.html.twig', [
                'id' => $user->getId(),
                'key' => $hash
            ]),
                'text/html',
                'utf-8'
            )
        ;

        $mailer->send($message);
    }

    /**
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return RedirectResponse
     * @throws \Exception
     */
    public function registerConfirm(Request $request, TranslatorInterface $translator)
    {
        if ($request->query->has('id') && $request->query->has('key')) {

            $user = $this->getDoctrine()->getRepository(User::class)->find($request->query->get('id'));

            if ($user) {

                if ($user->getHash() == $request->query->get('key')) {

                    if ($user->getHashCreatedAt() < new \DateTime()) {
                        $this->get('session')->getFlashBag()->set('error', $translator->trans('Time limit expired, please make a new request.', [], 'site'));
                        return $this->redirect($this->generateUrl('site_login'));
                    }

                    $user->setActive(true)
                         ->setHash(null);

                    $this->getDoctrine()->getManager()->flush();

                    $this->get('session')->getFlashBag()->set('success', $translator->trans('Your account have been created! Thank you for your interest.', [], 'site'));

                    return $this->redirect($this->generateUrl('site_login'));
                }
            }
        }

        $this->get('session')->getFlashBag()->add('error', $translator->trans('An error has occurred, please contact an administrator.', [], 'site'));

        return $this->redirect($this->generateUrl('site_login'));
    }
}