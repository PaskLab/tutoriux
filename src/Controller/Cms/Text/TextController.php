<?php

namespace App\Controller\Cms\Text;

use App\Services\ApplicationCore;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use App\Library\BaseController;
use App\Entity\Text;
use App\Form\Cms\Text\TextMainType;
use App\Form\Cms\Text\TextStaticType;
use App\Services\RouterInvalidator;

/**
 * Class TextController
 * @package App\Controller\Cms\Text
 */
class TextController extends BaseController
{
    /**
     * TextController constructor.
     * @param ApplicationCore $applicationCore
     * @throws \Exception
     */
    public function __construct(ApplicationCore $applicationCore)
    {
        parent::__construct($applicationCore);

        $this->createAndPushNavigationElement('Text list', 'cms_text', [], 'fa-files-o');
    }

    /**
     * @return Response
     */
    public function index()
    {
        $section = $this->getSection();

        $mainEntities = $this->getRepository(Text::class)->findBy([
            'section' => $section->getId(),
            'static' => false
        ], ['ordering' => 'ASC']);

        $staticEntities = $this->getRepository(Text::class)->findBy([
            'section' => $section->getId(),
            'static' => true
        ], ['ordering' => 'ASC']);

        return $this->render('cms/text/list.html.twig', [
            'mainEntities' => $mainEntities,
            'staticEntities' => $staticEntities,
            'truncateLength' => 100
        ]);
    }

    /**
     * @param Request $request
     * @param RouterInvalidator $routerInvalidator
     * @param $id
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function edit(Request $request, RouterInvalidator $routerInvalidator, $id)
    {
        $text = $this->getRepository(Text::class)->find($id);

        if (false == $text) {
            /** @var Text $text */
            $text = $this->getDoctrineInit()->initEntity(new Text());
        }

        $this->createAndPushNavigationElement(
            $text->getEntityName(),
            $text->getRouteBackend(),
            $text->getRouteBackendParams(),
            'fa-file-text-o'
        );

        if ($text->isStatic()) {
            $formType = TextStaticType::class;
        } else {
            $formType = TextMainType::class;
        }

        $form = $this->createForm($formType, $text);

        if ('POST' == $request->getMethod()) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $em = $this->getEm();
                $em->persist($text);
                $em->flush();

                $routerInvalidator->invalidate();

                $this->addFlashSuccess($this->getTranslator()->trans(
                    'The Text has been saved.', [], 'cms'
                ));

                if ($request->request->has('save')) {
                    return $this->redirect($this->generateUrl('cms_text'));
                }

                return $this->redirect($this->generateUrl('cms_text_edit', [
                    'id' => $text->getId() ?: 0
                ]));
            } else {
                $this->addFlashError($this->getTranslator()->trans(
                    'Some fields are invalid.', [], 'globals'
                ));
            }
        }

        return $this->render('cms/text/edit.html.twig', [
            'text' => $text,
            'form' => $form->createView()
        ]);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function checkDelete($id)
    {
        $text = $this->getEm()->getRepository(Text::class)->find($id);
        $output = $this->checkDeleteEntity($text);

        return new JsonResponse($output);
    }

    /**
     * @param RouterInvalidator $routerInvalidator
     * @param $id
     * @return RedirectResponse
     */
    public function delete(RouterInvalidator $routerInvalidator, $id)
    {
        $text = $this->getRepository(Text::class)->find($id);
        $this->deleteEntity($text);
        $routerInvalidator->invalidate();

        return $this->redirect($this->generateUrl('cms_text'));
    }

    /**
     * @param Request $request
     * @param RouterInvalidator $routerInvalidator
     * @return Response
     */
    public function order(Request $request, RouterInvalidator $routerInvalidator)
    {
        $textRepo = $this->getRepository(Text::class);
        $this->orderEntities($request, $textRepo);
        $routerInvalidator->invalidate();

        return new Response('');
    }
}
