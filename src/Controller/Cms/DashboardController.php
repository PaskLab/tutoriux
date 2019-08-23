<?php

namespace App\Controller\Cms;

use Symfony\Component\HttpFoundation\Response;
use App\Library\BaseController;

/**
 * Class DashboardController
 * @package App\Controller\Cms
 */
class DashboardController extends BaseController
{
    /**
     * @return Response
     */
    public function index()
    {
        return $this->render('cms/dashboard/index.html.twig');
    }
}
