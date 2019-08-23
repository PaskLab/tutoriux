<?php

namespace App\Controller\Cms\School;

use App\Services\ApplicationCore;
use Symfony\Component\HttpFoundation\JsonResponse,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\RedirectResponse;

use App\Library\BaseController;
use App\Form\Cms\School\RequestCriteriaType;
use App\Entity\School\RequestCriteria;
use App\Services\RouterInvalidator;

/**
 * Class RequestController
 * @package App\Controller\Cms\School
 */
class RequestController extends BaseController
{
    /**
     * RequestController constructor.
     * @param ApplicationCore $applicationCore
     * @throws \Exception
     */
    public function __construct(ApplicationCore $applicationCore)
    {
        parent::__construct($applicationCore);

        $this->createAndPushNavigationElement(
            'Request Criteria',
            'cms_school_criteria',
            [],
            'fa-list-alt'
        );
    }

    /**
     * @return Response
     */
    public function list()
    {
        $entities = $this->getRepository(RequestCriteria::class)->findAll();

        return $this->render('cms/school/list.html.twig', [
            'entities' => $entities
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function edit(Request $request, $id)
    {
        $entity = $this->getRepository(RequestCriteria::class)->find($id);

        if (false == $entity) {
            $entity = $this->getDoctrineInit()->initEntity(new RequestCriteria());
        }

        $this->createAndPushNavigationElement(
            $entity->getEntityName(),
            'cms_school_criteria_edit',
            [
                'id' => ($entity->getId()) ?: 0
            ],
            'fa-check-square-o'
        );

        $form = $this->createForm(RequestCriteriaType::class, $entity, [
            'translation_domain' => 'admin'
        ]);

        if ('POST' == $request->getMethod()) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getEm()->persist($entity);
                
                $this->getEm()->flush();

                $this->addFlashSuccess($this->getTranslator()->trans(
                    '%entity% has been saved.',
                    ['%entity%' => $entity], 'globals'
                ));

                if ($request->request->has('save')) {
                    return $this->redirect($this->generateUrl('cms_school_criteria'));
                }

                return $this->redirect($this->generateUrl('cms_school_criteria_edit', [
                    'id' => $entity->getId() ? : 0
                ]));

            } else {
                $this->addFlashError($this->getTranslator()->trans(
                    'Some fields are invalid.', [], 'globals'
                ));
            }
        }

        return $this->render('cms/school/edit.html.twig', [
            'entity' => $entity,
            'form' => $form->createView()
        ]);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function checkDelete($id)
    {
        $entity = $this->getRepository(RequestCriteria::class)->find($id);
        $output = $this->checkDeleteEntity($entity);

        return new JsonResponse($output);
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function delete($id)
    {
        $entity = $this->getRepository(RequestCriteria::class)->find($id);
        $this->deleteEntity($entity);

        return $this->redirect($this->generateUrl('cms_school_criteria'));
    }

    /**
     * @param Request $request
     * @param RouterInvalidator $routerInvalidator
     * @return Response
     */
    public function order(Request $request, RouterInvalidator $routerInvalidator)
    {
        $entitiesRepo = $this->getRepository(RequestCriteria::class);
        $this->orderEntities($request, $entitiesRepo);
        $routerInvalidator->invalidate();

        return new Response('');
    }
}
