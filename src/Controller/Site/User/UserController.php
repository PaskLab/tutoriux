<?php

namespace App\Controller\Site\User;

use Symfony\Component\HttpFoundation\JsonResponse,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException,
    Symfony\Component\HttpKernel\Exception\NotFoundHttpException,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use App\Library\BaseController,
    App\Entity\User;

/**
 * Class UserController
 * @package App\Controller\Site\User
 */
class UserController extends BaseController
{
    /**
     * @param Request $request
     * @return Response|MethodNotAllowedHttpException
     */
    public function users(Request $request)
    {
        switch ($request->getMethod()) {
            case 'GET':
                return $this->usersGET($request);
                break;
            default:
                return new MethodNotAllowedHttpException(['GET']);
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function usersGET(Request $request)
    {
        if ($request->get('username')) {
            $user = $this->getRepository('SystemBundle:User')->findOneBy(
                ['username' => $request->get('username')]
            );

            if (!$user) {
                throw new NotFoundHttpException('This member does not exist or has been disabled');
            }

            $this->createAndPushNavigationElement(
                (string) $user,
                'section_id_35',
                ['username' => $user->getUsername()],
                'fa fa-user'
            );

            return $this->render('SystemBundle:Frontend/User:profile.html.twig', [
                'user' => $user
            ]);
        }

        if ($request->isXmlHttpRequest()) {

            $data = $this->getDoctrine()->getRepository('SystemBundle:User')->dataTableResult(
                $request->query->all(),
                $request->getLocale()
            );

            $formattedData = [];

            /** @var User $user */
            foreach ($data['data'] as $user) {
                $formattedData[] = [
                    $this->getColumnContent($user, 0),
                    $this->getColumnContent($user, 1)
                ];
            }

            $data['data'] = $formattedData;

            return new JsonResponse($data);
        }

        return $this->render('SystemBundle:Frontend/User:users.html.twig');
    }

    /**
     * @param User $entity
     * @param $columnIndex
     * @return string
     */
    private function getColumnContent(User $entity, $columnIndex)
    {
        return $this->renderView(
            'SystemBundle:Frontend/User:_users_items.html.twig',
            ['entity' => $entity, 'column' => $columnIndex]
        );
    }

    /**
     * @param Request $request
     * @param $username
     * @return JsonResponse
     */
    public function follow(Request $request, $username)
    {
        $allowedMethods = ['PUT'];

        if ($request->isXmlHttpRequest() && in_array($request->getMethod(), $allowedMethods)) {

            $user = $this->getRepository('SystemBundle:User')->find($this->getUser()->getId());

            $targetUser = $this->getRepository('SystemBundle:User')->findOneBy([
                'username' => $username
            ]);

            if (!$targetUser) {
                throw new NotFoundHttpException('Sorry, we do not find this user');
            }

            $notificationFlag = false;

            if ($targetUser->getFollowers()->contains($user)) {
                $targetUser->getFollowers()->removeElement($user);
            } else {
                $targetUser->addFollower($user);
                $notificationFlag = true;
            }

            $this->getDoctrine()->getManager()->flush();

            if ($notificationFlag) {

                $this->get('system.notifier')->build(
                    'user.follow',
                    ['%user%' => $user->getFullName()],
                    [$targetUser],
                    'fa fa-eye',
                    'section_id_35',
                    ['username' => $user->getUsername()]
                )
                    ->setIconColor('primary')
                    ->setToastr(true)
                    ->send();

                $this->get('system.logger')->build(
                    'user.follow',
                    [
                        '%me%' => $user->getFullName(),
                        '%user%' => $targetUser->getFullName()
                    ],
                    $user,
                    'fa fa-eye'
                )
                    ->setRoute('section_id_35')
                    ->setRouteParameters(['username' => $targetUser->getUsername()])
                    ->setFollowersRoute('section_id_35')
                    ->setFollowersRouteParameters(['username' => $user->getUsername()])
                    ->setPublic(true)
                    ->save();
            }

            return new JsonResponse();
        }

        throw new MethodNotAllowedHttpException($allowedMethods);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function activities(Request $request)
    {
        $allowedMethods = ['GET'];

        if ($request->isXmlHttpRequest() && in_array($request->getMethod(), $allowedMethods)) {

            if (!$request->query->has('length')
                || !$request->query->has('page')
                || !$request->query->has('filter')) {
                throw new BadRequestHttpException();
            }

            $user = $this->getRepository('SystemBundle:User')->find($this->getUser()->getId());

            if (!$user) {
                throw new NotFoundHttpException('Sorry, we do not find this user');
            }

            if ($request->query->get('filter') == 'personal') {
                $count = $this->getDoctrine()
                    ->getRepository('SystemBundle:Log')->getCount($user, 'personal');
                if ($count) {
                    $activities = $this->getDoctrine()
                        ->getRepository('SystemBundle:Log')->findBy(
                            ['user' => $this->getUser()],
                            ['createdAt' => 'DESC'],
                            $request->query->get('length'),
                            ($request->query->get('page') - 1) * $request->query->get('length')
                        );
                } else {
                    $activities = [];
                }
            } else {
                $count = $this->getDoctrine()
                    ->getRepository('SystemBundle:Log')->getCount($user);
                if ($count) {
                    $activities = $this->getDoctrine()
                        ->getRepository('SystemBundle:Log')->findAllLogs(
                            $user,
                            $request->query->get('length', 20),
                            $request->query->get('page', 1)
                        );
                } else {
                    $activities = [];
                }
            }

            $data = [];
            foreach ($activities as $activity) {

                if ($activity->getUser()->getId() == $user->getId()) {
                    $url = ($activity->getRoute())
                        ? $this->generateUrl($activity->getRoute(), $activity->getRouteParameters())
                        : null;
                } else {
                    $url = ($activity->getRoute())
                        ? $this->generateUrl($activity->getFollowersRoute(), $activity->getFollowersRouteParameters())
                        : null;
                }

                $data[] = [
                    'id' => $activity->getId(),
                    'url' => $url,
                    'icon' => $activity->getIcon(),
                    'iconColor' => $activity->getIconColor(),
                    'text' => $this->get('translator')->trans(
                        $activity->getToken(),
                        $activity->getParameters(),
                        'log'
                    ),
                    'date' => $this->get('salavert.twig.time_ago')
                        ->timeAgoInWordsFilter($activity->getCreatedAt(), true, true)
                ];
            }

            $result = [
                'data' => $data,
                'page' => $request->query->get('page'),
                'total' => $count
            ];

            return new JsonResponse($result);
        }

        throw new MethodNotAllowedHttpException($allowedMethods);
    }

    /**
     * This render the modules that are associated with $mappingCode navigation
     *
     * @param string $mappingCode ie: "_profile_module", "_profile_sidebar_module"
     *
     * @return Response
     */
    public function profileModule($mappingCode)
    {
        $mappings = $this->getDoctrine()->getRepository('SystemBundle:Mapping')
            ->createQueryBuilder('m')
            ->select('m')
            ->innerJoin('m.navigation', 'n')
            ->where('n.code = :code')
            ->setParameter('code', $mappingCode)
            ->orderBy('m.ordering', 'ASC')
            ->getQuery()->getResult();

        return $this->render(
            'SystemBundle:Frontend/User/:_profile_module.html.twig',
            [
                'mappings' => $mappings
            ]
        );
    }
}