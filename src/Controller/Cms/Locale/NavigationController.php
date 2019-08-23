<?php

namespace App\Controller\Cms\Locale;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

use App\Library\BaseController;

/**
 * Class NavigationController
 * @package App\Controller\Cms\Locale
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

        $selected = (0 === strpos($masterRoute, 'cms_locale'));

        return $this->render('cms/locale/top_module_bar.html.twig', [
            'selected' => $selected
        ]);
    }

}
