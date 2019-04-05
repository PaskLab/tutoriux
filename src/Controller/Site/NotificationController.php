<?php

namespace App\Controller\Site;

use App\Entity\Message;
use Symfony\Component\HttpFoundation\JsonResponse,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

use App\Entity\UserNotification,
    App\Library\BaseController;

/**
 * Class NotificationController
 * @package App\Controller\Site
 */
class NotificationController extends BaseController
{
    /**
     * @return Response
     */
    public function headerNotification()
    {
        $repository = $this->getDoctrine()->getRepository(UserNotification::class);

        $newCount = $repository->getNewNotificationCount($this->getUser());
        $userNotifications = $repository->getNotificationsForUser($this->getUser());

        return $this->render(
            'site/notification/header_dropdown.html.twig',
            [
                'newCount' => $newCount,
                'userNotifications' => $userNotifications
            ]
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function viewed(Request $request)
    {
        $allowedMethods = ['PUT'];

        if ($request->isXmlHttpRequest() && in_array($request->getMethod(), $allowedMethods)) {

            $userNotifications = $this->getDoctrine()->getRepository(UserNotification::class)
                ->findBy(['user' => $this->getUser(), 'viewed' => false]);

            /** @var UserNotification $userNotification */
            foreach ($userNotifications as $userNotification) {
                $userNotification->setViewed(true);
                $userNotification->getNotification()->setToastr(false);
            }

            $this->getDoctrine()->getManager()->flush();

            return new JsonResponse();
        }

        throw new MethodNotAllowedHttpException($allowedMethods);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function toastr(Request $request)
    {
        $allowedMethods = ['PUT'];

        if ($request->isXmlHttpRequest() && in_array($request->getMethod(), $allowedMethods)) {

            $userNotifications = $this->getDoctrine()->getRepository(UserNotification::class)
                ->findBy(['user' => $this->getUser()]);

            /** @var UserNotification $userNotification */
            foreach ($userNotifications as $userNotification) {
                $userNotification->getNotification()->setToastr(false);
            }

            $this->getDoctrine()->getManager()->flush();

            return new JsonResponse();
        }

        throw new MethodNotAllowedHttpException($allowedMethods);
    }

    /**
     * @return JsonResponse
     */
    public function badges()
    {
        $badges = [
            'unreadMessages' => null
        ];

        if ($this->isGranted('ROLE_USER')) {
            $unreadMessages = $this->getDoctrine()->getRepository(Message::class)->countUnreadMessage(
                $this->getUser()
            );
            $badges['unreadMessages'] = $unreadMessages;
        }

        return new JsonResponse($badges);
    }
}