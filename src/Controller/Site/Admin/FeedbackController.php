<?php

namespace App\Controller\Site\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Form\Site\Admin\FeedbackType;
use App\Library\BaseController;

/**
 * Class FeedbackController
 * @package App\Controller\Site\Admin
 */
class FeedbackController extends BaseController
{
    /**
     * @param Request $request
     * @param TranslatorInterface $t
     * @param \Swift_Mailer $mailer
     * @return RedirectResponse|Response
     */
    public function feedback(Request $request, TranslatorInterface $t, \Swift_Mailer $mailer)
    {
        $form = $this->createForm(FeedbackType::class, null, [
            'translator' => $t
        ]);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $message = (new \Swift_Message())
                    ->setSubject('Feedback - Tutoriux')
                    ->setFrom($this->getParameter('app.emails.system_email'))
                    ->setTo($this->getParameter('app.emails.feedback'))
                    ->setBody($this->renderView('site/admin/feedback/feedback_form_email.txt.twig', [
                        'fullname' => $form->get('fullname')->getData(),
                        'email' => $form->get('email')->getData(),
                        'subject' => $form->get('subject')->getData(),
                        'message' => $form->get('message')->getData(),
                        'websiteLocale' => $request->getLocale(),
                        'clientIp' => $request->getClientIp(),
                        'width' => $form->get('width')->getData(),
                        'extraInfo' => $request->headers
                    ]),
                        'text/plain',
                        'utf-8'
                    )
                ;

                $mailer->send($message);

                $this->addFlashSuccess($t->trans('Your feedback has been sent !', [], 'site'));

                return $this->redirect($this->generateUrl('section_id_1'));
            } else {
                $this->addFlashError('Some fields are invalid.');
            }
        }

        return $this->render('site/admin/feedback/feedback_form.html.twig', [
            'form' => $form->createView()
        ]);
    }
}