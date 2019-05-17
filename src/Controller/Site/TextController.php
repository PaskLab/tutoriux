<?php

namespace App\Controller\Site;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

use App\Entity\Text,
    App\Repository\TextRepository,
    App\Library\BaseController,
    App\Services\DoctrineInit;

/**
 * Class TextController
 * @package App\Controller\Site
 */
class TextController extends BaseController
{
    /**
     * @return Response
     */
    public function index()
    {
        $response = new Response();
        $response->setPublic();
        $response->setSharedMaxAge(86400); // 1 day

        return $this->render(
            'site/text/index.html.twig',
            [],
            $response
        );
    }

    /**
     * Display the main texts of a given section.
     * If no sectionId is provided the current section is used.
     *
     * @param Request $request
     * @param int $sectionId
     *
     * @return Response
     */
    public function displayTexts(Request $request, $sectionId = null)
    {
        if (false == $sectionId) {
            $sectionId = $this->getSection()->getId();
        }

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
        ['ordering' => 'ASC']);

        return $this->render(
            'site/text/displayTexts.html.twig',
            [
                'texts' => $texts,
                'textId' => $this->get('request_stack')->getCurrentRequest()->get('bloc')
            ],
            $response
        );
    }

    /**
     * @param Request $request
     * @param $textId
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function displayTextById(Request $request, DoctrineInit $doctrineInit, $textId)
    {
        /** @var TextRepository $textRepository */
        $textRepository = $doctrineInit
            ->initRepository($this->getEm()->getRepository(Text::class));
        $textLastUpdate = $textRepository->findLastUpdate(null, null, $textId);

        $response = new Response();
        $response->setPublic();
        $response->setEtag($textId . $textLastUpdate);

        if ($response->isNotModified($request)) {
            return $response;
        }

        /** @var Text $text */
        $text = $textRepository->findOneBy([
            'id' => $textId,
            'active' => true
        ]);

        return $this->render(
            'site/text/displayTexts.html.twig',
            [
                'texts' => is_null($text) ? null : [$text],
                'textId' => $textId
            ],
            $response
        );
    }

    /**
     * Display a single text
     *
     * @param Text $text
     *
     * @return Response
     */
    public function displayText($text)
    {
        return $this->render('site/text/displayTexts.html.twig', [
            'texts' => [$text],
            'textId' => $text->getId()
        ]);
    }
}
