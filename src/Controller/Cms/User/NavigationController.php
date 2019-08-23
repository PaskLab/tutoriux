<?php

namespace App\Controller\Cms\User;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use App\Library\BaseController;

/**
 * Class NavigationController
 * @package App\Controller\Cms\User
 */
class NavigationController extends BaseController
{
    /**
     * @param RequestStack $requestStack
     * @return Response
     */
    public function topModuleBar(RequestStack $requestStack)
    {
        // Access restricted to ROLE_BACKEND_ADMIN
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_BACKEND_ADMIN')) {
            return new Response();
        }

        $masterRoute = $requestStack->getMasterRequest()->get('_route');

        $selected = (0 === strpos($masterRoute, 'cms_user'));

        return $this->render('cms/user/top_bundle_bar.html.twig', [
            'selected' => $selected
        ]);
    }
}
