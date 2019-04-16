<?php

namespace App\Controller\Site\Admin;

use Symfony\Component\HttpFoundation\Request;

use App\Form\Site\Admin\FeedbackType,
    App\Library\BaseController;

class FeedbackController extends BaseController
{
    public function feedback(Request $request)
    {
        $t = $this->get('translator');

        $form = $this->createForm(FeedbackType::class, null, [
            'translator' => $t
        ]);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $message = (new \Swift_Message())
                    ->setSubject('Feedback - Tutoriux')
                    ->setFrom($this->getParameter('system.system_email'))
                    ->setTo($this->getParameter('admin.emails.feedback'))
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

                $this->get('mailer')->send($message);

                $this->addFlashSuccess($t->trans('Your feedback has been sent !', [], 'admin'));

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