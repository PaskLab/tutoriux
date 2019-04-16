<?php

namespace App\Controller\Site;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

use App\Repository\TextRepository;
use App\Library\BaseController;
use App\Services\DoctrineInit;

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
     * @param Request $request
     * @return Response
     */
    public function quickTour(Request $request)
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
     * @throws \Doctrine\ORM\ORMException
     */
    public function portlet(Request $request)
    {
        $sectionId = $this->getSection()->getId();

        /** @var TextRepository $textRepository */
        $textRepository = $this->get(DoctrineInit::class)
            ->initRepository($this->getEm()->getRepository('SystemBundle:Text'));
        $textLastUpdate = $textRepository->findLastUpdate(null, $sectionId);

        $response = new Response();
        $response->setPublic();
        $response->setEtag($sectionId . $textLastUpdate);

        if ($response->isNotModified($request)) {
            return $response;
        }

        $texts = $this->getEm()->getRepository('SystemBundle:Text')->findBy(
            ['section' => $sectionId, 'static' => false, 'active' => true],
            ['ordering' => 'ASC']
        );

        return $this->render(
            'SystemBundle:Frontend/Section:portlet.html.twig',
            ['texts' => $texts],
            $response
        );
    }
}