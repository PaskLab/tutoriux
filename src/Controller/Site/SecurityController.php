<?php

namespace App\Controller\Site;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Security\Core\Security;

use App\Entity\Login;
use App\Form\Frontend\Security\ChangePasswordType;
use App\Form\Frontend\Security\ForgotPasswordType;
use App\Form\Frontend\Security\RegisterType;
use App\Library\BaseController;
use App\Entity\User;

/**
 * Class SecurityController
 * @package App\Controller\Site
 */
class SecurityController extends BaseController
{
    /**
     * Backend main login form
     *
     * @param Request $request
     *
     * @return Response
     */
    public function login(Request $request)
    {
        $session = $request->getSession();

        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                Security::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $session->remove(Security::AUTHENTICATION_ERROR);
        }

        // Last username entered by the user
        $lastUsername = $session->get(Security::LAST_USERNAME);

        // Each login attempt is logged
        if (isset($_SERVER['REMOTE_ADDR']) && $error && $lastUsername) {
            $this->logLoginAttempt($lastUsername);
        }

        // Forgot password section

        $forgotForm = $this->createForm(ForgotPasswordType::class, null, [
            'translation_domain' => 'system'
        ]);

        // Register section

        $user = new User();

        $registerForm = $this->createForm(RegisterType::class, $user, array(
            'validation_groups' => 'new',
            'translation_domain' => 'system'
        ));

        return $this->render('SystemBundle:Frontend/Security:login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
            'forgotForm' => $forgotForm->createView(),
            'registerForm' => $registerForm->createView()
        ));
    }

    /**
     * Log a failed login attempt
     *
     * @param string $username
     */
    protected function logLoginAttempt($username)
    {
        $login = new Login();
        $login->setIp($this->get('request_stack')->getCurrentRequest()->getClientIp());
        $login->setUsername($username);
        $login->setSuccess(false);

        $this->getEm()->persist($login);
        $this->getEm()->flush();
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function passwordLost(Request $request)
    {
        $t = $this->get('translator');

        $form = $this->createForm(ForgotPasswordType::class, null, [
            'translation_domain' => 'system'
        ]);

        if ('POST' == $request->getMethod()) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $email = $form->get('email')->getData();

                $emailConstraint = new Email();
                $errorList = $this->get('validator')->validateValue(
                    $email,
                    $emailConstraint
                );

                if (count($errorList) == 0) {
                    $m = $this->getDoctrine()->getManager();

                    $user = $m->getRepository('SystemBundle:User')->findOneBy(array('email' => $email));

                    if ($user) {
                        $this->sendPasswordLostEmail($user);
                    }

                    // Same message in both cases to not give away users informations
                    $this->get('session')->getFlashBag()->add('info', $t->trans("If the email you specified exists in our system, we've sent a password reset link to it.", [], 'system'));

                } else {
                    foreach ($errorList as $error) {
                        $this->get('session')->getFlashBag()->add('error', $error->getMessage());
                    }
                }
            }

            return $this->redirect($this->generateUrl('system_frontend_login'));

        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function passwordLostChange(Request $request)
    {
        $t = $this->get('translator');

        $form = $this->createForm(ChangePasswordType::class, null, [
            'validation_groups' => 'reset_password',
            'translation_domain' => 'system'
        ]);

        if ('POST' == $request->getMethod()) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $user = $this->getDoctrine()->getRepository('SystemBundle:User')->findOneBy(array('username' => $form->get('username')->getData()));

                if ($user && $user->getId() == $request->query->get('id')) {

                    if ($user->getHash() == $request->query->get('key') && $user->getHashCreatedAt() > new \DateTime()) {

                        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
                        $user->setPassword($encoder->encodePassword($form->get('password')->getData(), $user->getSalt()));

                        $user->setHash(null);

                        $this->getDoctrine()->getManager()->flush();

                        $this->get('session')->getFlashBag()->set('success', $t->trans('Your password have been changed!', [], 'system'));

                        return $this->redirect($this->generateUrl('system_frontend_login'));
                    }
                } else {
                    $this->get('session')->getFlashBag()->add('error', $t->trans('Wrong username ...', [], 'system'));
                }
            }

            return $this->render('SystemBundle:Frontend/Security:create_new_password.html.twig', array(
                'id' => $request->query->get('id'),
                'key' => $request->query->get('key'),
                'form' => $form->createView()
            ));

        } else {
            if ($request->query->has('id') && $request->query->has('key')) {

                $user = $this->getDoctrine()->getRepository('SystemBundle:User')->find($request->query->get('id'));

                if ($user) {

                    if ($user->getHash() == $request->query->get('key')) {

                        if ($user->getHashCreatedAt() < new \DateTime()) {
                            $this->get('session')->getFlashBag()->set('error', $t->trans('Time limit expired, please make a new request.', [], 'system'));
                            return $this->redirect($this->generateUrl('system_frontend_login'));
                        }

                        return $this->render('SystemBundle:Frontend/Security:create_new_password.html.twig', array(
                            'id' => $request->query->get('id'),
                            'key' => $request->query->get('key'),
                            'form' => $form->createView()
                        ));
                    }
                }
            }
        }

        $this->get('session')->getFlashBag()->add('error', $t->trans('An error has occurred, please contact an administrator.', [], 'system'));

        return $this->redirect($this->generateUrl('system_frontend_login'));
    }

    /**
     * sendPasswordLostEmail
     *
     * @param $user User
     */
    public function sendPasswordLostEmail(User $user)
    {
        $t = $this->get('translator');

        $hash = md5(uniqid());

        $expiration = new \DateTime();

        $expiration->add(new \DateInterval('P0DT1H'));

        $user->setHash($hash)->setHashCreatedAt($expiration);

        $this->getDoctrine()->getManager()->flush();

        $message = \Swift_Message::newInstance()
            ->setSubject($t->trans('Tutoriux - Password Recovery', [], 'system'))
            ->setFrom($this->container->getParameter('system.system_email'))
            ->setTo($user->getEmail())
            ->setBody($this->renderView('SystemBundle:Frontend/Security:password_lost_email.html.twig', array(
                    'id' => $user->getId(),
                    'key' => $hash
                )),
                'text/html',
                'utf-8'
            )
        ;

        $this->get('mailer')->send($message);
    }
}