<?php

namespace App\Controller\Cms\Section;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use App\Library\BaseController;

/**
 * Class NavigationController
 * @package App\Controller\Cms\Section
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

        $selected = (0 === strpos($masterRoute, 'cms_section'));

        return $this->render('cms/section/section_module_bar.html.twig', ['selected' => $selected]);
    }

    /**
     * @param RequestStack $requestStack
     * @return Response
     */
    public function sideModuleBar(RequestStack $requestStack)
    {
        $masterRoute = $requestStack->getMasterRequest()->get('_route');

        $selected = (0 === strpos($masterRoute, 'cms_section_root'));

        return $this->render('cms/section/side_module_bar.html.twig', [
            'selected' => $selected
        ]);
    }
}
