<?php

namespace App\Controller\Site;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Repository\TextRepository;
use App\Library\BaseController;
use App\Services\DoctrineInit;
use App\Entity\Text;

/**
 * Class SectionController
 * @package App\Controller\Site
 */
class SectionController extends BaseController
{
    /**
     * @return Response
     */
    public function homePage()
    {
        return $this->render(
            'site/section/home_page.html.twig',
            []
        );
    }

    /**
     * @return Response
     */
    public function quickTour()
    {
        $response = new Response();
        $response->setPublic();
        $response->setSharedMaxAge(86400); // 1 day

        return $this->render(
            'site/section/quick_tour.html.twig',
            [],
            $response
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function portlet(Request $request)
    {
        $sectionId = $this->getSection()->getId();

        /** @var TextRepository $textRepository */
        $textRepository = $this->getRepository(Text::class);
        $textLastUpdate = $textRepository->findLastUpdate(null, $sectionId);

        $response = new Response();
        $response->setPublic();
        $response->setEtag($sectionId . $textLastUpdate);

        if ($response->isNotModified($request)) {
            return $response;
        }

        $texts = $textRepository->findBy(
            ['section' => $sectionId, 'static' => false, 'active' => true],
            ['ordering' => 'ASC']
        );

        return $this->render(
            'site/section/portlet.html.twig',
            ['texts' => $texts],
            $response
        );
    }
}