<?php

namespace App\Controller\Cms\Text;

use App\Library\BaseController;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class NavigationController
 * @package App\Controller\Cms\Text
 */
class NavigationController extends BaseController
{
    /**
     * @param RequestStack $requestStack
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sectionModuleBar(RequestStack $requestStack)
    {
        $masterRoute = $requestStack->getMasterRequest()->get('_route');

        $selected = (0 === strpos($masterRoute, 'cms_text'));

        return $this->render('cms/text/section_module_bar.html.twig', [
            'selected' => $selected
        ]);
    }
}
