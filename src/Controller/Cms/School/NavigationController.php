<?php

namespace App\Controller\Cms\School;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

use App\Library\BaseController;

/**
 * Class NavigationController
 * @package App\Controller\Cms\School
 */
class NavigationController extends BaseController
{
    /**
     * @param RequestStack $requestStack
     * @return Response
     */
    public function sideModuleBar(RequestStack $requestStack)
    {
        $masterRoute = $requestStack->getMasterRequest()->get('_route');

        $selected = (0 === strpos($masterRoute, 'cms_school_criteria'));

        return $this->render('cms/school/side_module_bar.html.twig', [
            'selected' => $selected
        ]);
    }
}