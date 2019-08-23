<?php

namespace App\Controller\Site\User;

use Symfony\Component\HttpFoundation\JsonResponse,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException,
    Symfony\Component\HttpKernel\Exception\NotFoundHttpException,
    Symfony\Component\Security\Core\Exception\AccessDeniedException,
    Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use App\Entity\Message,
    App\Library\BaseController,
    App\Entity\User,
    App\Form\Site\User\ComposeType,
    App\Entity\Media\Media;

/**
 * Class MessageController
 * @package App\Controller\Site\User
 */
class MessageController extends BaseController
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mailbox(Request $request)
    {
        $user = $this->getDoctrine()->getRepository('SystemBundle:User')->find($this->getUser());

        if ($request->isXmlHttpRequest()) {

            $allowedMethods = ['GET', 'POST'];

            if (in_array($request->getMethod(), $allowedMethods)
                && $request->getRequestFormat() == 'json') {

                if ('GET' == $request->getMethod()) {
                    switch ($request->query->get('action')) {
                        case 'list':
                            return $this->getMails($request, $user);
                        case 'read':
                            return $this->readMessage($request, $user);
                        default:
                            throw new BadRequestHttpException();
                    }

                }

                switch ($request->request->get('action')) {
                    case 'toggle_flag':
                        return $this->toggleFlag($request, $user);
                    case 'mark_as_read':
                        return $this->markAsRead($request, $user);
                    case 'recover':
                        return $this->recover($request, $user);
                    case 'delete':
                        return $this->deleteMessage($request, $user);
                    default:
                        throw new BadRequestHttpException();
                }

            } else {
                throw new MethodNotAllowedHttpException($allowedMethods);
            }
        }

        $this->createAndPushNavigationElement(
            $user->getFullName(),
            'section_id_35',
            ['username' => $user->getUsername()]
        );
        $this->createAndPushNavigationElement(
            'My Messages',
            'section_id_35_messages'
        );

        return $this->render(
            'SystemBundle:Frontend/User:mailbox.html.twig',
            [
                'user' => $user
            ]
        );
    }

    /**
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    private function getMails(Request $request, User $user)
    {
        if (!$request->query->has('length')
            || $request->query->get('length') < 5
            || !$request->query->has('view')
            || !$request->query->has('page')
            || $request->query->get('page') < 1
            || !$request->query->has('filter')) {
            throw new BadRequestHttpException();
        }

        $count = $this->getDoctrine()->getRepository('SystemBundle:Message')->countMessage(
            $user,
            $request->query->get('view'),
            $request->query->get('filter', null)
        );

        $messages = $this->getDoctrine()->getRepository('SystemBundle:Message')->findMessages(
            $user,
            $request->query->get('view'),
            $request->query->get('length'),
            $request->query->get('page', 1),
            $request->query->get('filter', null)
        );

        $data = [];

        /** @var Message $message */
        foreach ($messages as $message) {
            $data[] = [
                'id' => $message->getId(),
                'flag' => $message->getFlag(),
                'sender' => $message->getCreatedBy()->getFullname(),
                'recipient' => $message->getUser()->getFullName(),
                'title' => $message->getTitle(),
                'attachment' => $message->getAttachment(),
                'date' => $message->getCreatedAt()->format('d-m-Y H:i'),
                'viewed' => $message->isViewed()
            ];
        }

        return new JsonResponse([
            'data' => $data,
            'view' => $request->query->get('view', 'inbox'),
            'page' => $request->query->get('page', 1),
            'total' => $count,
            'filter' => $request->query->get('filter', null)
        ]);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    private function toggleFlag(Request $request, User $user)
    {
        if (!($id = $request->request->get('id'))) {
            throw new BadRequestHttpException();
        }

        $message = $this->getDoctrine()->getRepository('SystemBundle:Message')->find($id);

        if (!$message) {
            throw new NotFoundHttpException('Message entity not found');
        }

        if ($message->getUser()->getId() != $user->getId()) {
            throw new AccessDeniedException();
        }

        ($message->getFlag()) ? $message->setFlag(false) : $message->setFlag(true);
        $this->getEm()->flush();

        return new JsonResponse([
            'messageId' => $id,
            'flag' => $message->getFlag()
        ]);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    private function markAsRead(Request $request, User $user)
    {
        if (!($ids = $request->request->get('ids')) || !is_array($ids)) {
            throw new BadRequestHttpException();
        }

        foreach ($ids as $id) {
            $message = $this->getDoctrine()->getRepository('SystemBundle:Message')->find($id);

            if (!$message) {
                continue;
            }

            if ($message->getUser()->getId() == $user->getId()) {
                $message->setViewed(true);
            }
        }

        $this->getEm()->flush();

        return new JsonResponse([
            'messageIds' => $ids
        ]);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function deleteMessage(Request $request, User $user)
    {
        if (!($ids = $request->request->get('ids')) || !is_array($ids)) {
            throw new BadRequestHttpException();
        }

        foreach ($ids as $id) {
            $message = $this->getDoctrine()->getRepository('SystemBundle:Message')->find($id);

            if (!$message) {
                continue;
            }

            if ($message->getUser()->getId() == $user->getId()) {
                if ($message->isDeleted()) {
                    $this->getEm()->remove($message);
                } else {
                    $message->delete();
                }
            }
        }

        $this->getEm()->flush();

        return new JsonResponse([
            'messageIds' => $ids
        ]);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function recover(Request $request, User $user)
    {
        if (!($ids = $request->request->get('ids')) || !is_array($ids)) {
            throw new BadRequestHttpException();
        }

        foreach ($ids as $id) {
            $message = $this->getDoctrine()->getRepository('SystemBundle:Message')->find($id);

            if (!$message) {
                continue;
            }

            if ($message->getUser()->getId() == $user->getId()) {
                $message->restore();
            }
        }

        $this->getEm()->flush();

        return new JsonResponse([
            'messageIds' => $ids
        ]);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function readMessage(Request $request, User $user)
    {
        if (!($id = $request->query->get('id'))) {
            throw new BadRequestHttpException();
        }

        $message = $this->getDoctrine()->getRepository('SystemBundle:Message')->find($id);

        if (!$message) {
            throw new NotFoundHttpException();
        }

        if ($message->getUser()->getId() == $user->getId()) {
            $message->setViewed(true);
            $this->getEm()->flush();
        } elseif ($message->getCreatedBy()->getId() != $user->getId()) {
            throw new AccessDeniedException();
        }

        /** @var Media $media */
        if ($media = $message->getCreatedBy()->getAvatar()) {
            $avatar = $this->get('liip_imagine.cache.manager')->getBrowserPath($media->getMediaPath(), 'media_user_avatar_medium_round');
        }

        return new JsonResponse([
            'title' => $message->getTitle(),
            'sender' => $message->getCreatedBy()->getFullName(),
            'sender_username' => $message->getCreatedBy()->getUsername(),
            'date' => $message->getCreatedAt()->format('d-m-Y H:i'),
            'avatar_src' => (isset($avatar)) ? $avatar : null,
            'message' => $message->getMessage()
        ]);
    }

    /**
     * @param Request $request
     * @param $username
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function compose(Request $request, $username)
    {
        $user = $this->getDoctrine()->getRepository('SystemBundle:User')->findOneBy(
            ['username' => $username]
        );

        if (!$user) {
            throw new NotFoundHttpException('User entity not found');
        }

        $this->createAndPushNavigationElement(
            $user->getFullName(),
            'section_id_35',
            ['username' => $user->getUsername()]
        );
        $this->createAndPushNavigationElement(
            'Private message',
            'section_id_35_compose',
            ['username' => $user->getUsername()]
        );

        $message = new Message();
        $message->setUser($user);

        if ($request->query->has('reply')) {
            $message->setTitle($request->query->get('reply'));
        }

        $form = $this->createForm(ComposeType::class, $message);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {

                if (preg_match('#data-mediaid=#i', $form->get('message')->getData())) {
                    $message->setAttachment(true);
                }

                $this->getEm()->persist($message);
                $this->getEm()->flush();

                $this->addFlashSuccess('Your message has been sent!');

                $this->get('system.notifier')->build(
                    'user.new_message',
                    ['%user%' => $this->getUser()->getFullname()],
                    [$user],
                    'fa fa-envelope',
                    'section_id_35_messages'
                )->setIconColor('info')->setToastr(true)->send();

                return $this->redirect($this->generateUrl('section_id_35', [
                    'username' => $user->getUsername()
                ]));
            }
        }

        return $this->render(
            'SystemBundle:Frontend/User:compose.html.twig',
            [
                'user' => $user,
                'form' => $form->createView()
            ]
        );
    }
}