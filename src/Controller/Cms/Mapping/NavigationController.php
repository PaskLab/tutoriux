<?php

namespace App\Controller\Cms\Mapping;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use App\Library\BaseController;

/**
 * Class NavigationController
 * @package App\Controller\Cms\Mapping
 */
class NavigationController extends BaseController
{
    /**
     * @param RequestStack $requestStack
     * @return Response
     */
    public function sectionModuleBar(RequestStack $requestStack)
    {
        $masterRoute = $requestStack->getMasterRequest()->get('_route');

        $selected = (0 === strpos($masterRoute, 'cms_mapping'));

        return $this->render('cms/mapping/section_module_bar.html.twig', [
            'selected' => $selected
        ]);
    }
}