<?php

namespace App\Controller\Cms\Role;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use App\Library\BaseController;

/**
 * Class NavigationController
 * @package App\Controller\Cms\Role
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

        $selected = (0 === strpos($masterRoute, 'cms_role'));

        return $this->render('cms/role/top_bundle_bar.html.twig', [
            'selected' => $selected
        ]);
    }

    /**
     * @return Response
     */
    public function impersonatingBar()
    {
        $authorizationChecker = $this->get('security.authorization_checker');

        // Make sure you're impersonating a User
        if (!$authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
            return new Response();
        }

        $previousToken = null;

        $tokenStorage = $this->get('security.token_storage');

        // Loop through the Roles
        foreach ($tokenStorage->getToken()->getRoles() as $role) {

            // If it's a SwitchUserRole instance, we can get back the Original Token
            if ($role instanceof SwitchUserRole) {
                $previousToken = $role->getSource();
            }
        }

        return $this->render('cms/role/impersonating_bar.html.twig', [
            'previousToken' => $previousToken
        ]);
    }
}
