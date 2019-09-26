<?php

namespace App\Controller\Cms\Mapping;



use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Services\ApplicationCore;
use App\Library\BaseController;
use App\Entity\Mapping;
use App\Form\Cms\Mapping\MappingType;
use App\Services\RouterInvalidator;

/**
 * Class MappingController
 * @package App\Controller\Cms\Mapping
 */
class MappingController extends BaseController
{
    /**
     * MappingController constructor.
     * @param ApplicationCore $applicationCore
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @throws \Exception
     */
    public function __construct(ApplicationCore $applicationCore, AuthorizationCheckerInterface $authorizationChecker)
    {
        parent::__construct($applicationCore);

        // Access restricted to ROLE_BACKEND_ADMIN
        if (false === $authorizationChecker->isGranted('ROLE_BACKEND_ADMIN')) {
            throw new AccessDeniedHttpException('You don\'t have the privileges to view this page.');
        }

        $this->createAndPushNavigationElement(
            'Mappings',
            'cms_mapping',
            [],
            'fa-link'
        );
    }

    /**
     * @param $sectionId
     * @return Response
     */
    public function index($sectionId)
    {
        $t = $this->getTranslator();

        $entities = $this->getRepository(Mapping::class)->createQueryBuilder('m')
            ->innerJoin('m.section', 'ms')
            ->leftJoin('m.navigation', 'mn')
            ->where('ms.id = :section_id')
            ->setParameter('section_id', $sectionId)
            ->addOrderBy('mn.name', 'ASC')
            ->addOrderBy('m.ordering', 'ASC')
            ->getQuery()->getResult();

        $mappings = [];

        foreach ($entities as $entity) {
            /** @var Mapping $entity */
            $nav = ($entity->getNavigation()) ? $entity->getNavigation()->getName() : $t->trans('Without navigation', [], 'cms');
            $key = strtolower(str_replace(' ', '', $nav));

            if (!isset($mappings[$key])) {
                $mappings[$key]['name'] = $nav;
            }

            $mappings[$key]['entities'][] = $entity;
        }

        return $this->render('cms/mapping/list.html.twig', [
            'collections' => $mappings
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
        $section = $this->getSection();

        $entity = $this->getRepository(Mapping::class)->find($id);

        if (!$entity) {
            /** @var Mapping $entity */
            $entity = $this->getDoctrineInit()
                ->initEntity(new Mapping())
                ->setSection($section);
        }

        $this->createAndPushNavigationElement((string) $entity, 'cms_mapping_edit', [
            'id' => ($entity->getId()) ?: 0
        ],
            'fa-pencil-square-o'
        );

        $form = $this->createForm(MappingType::class, $entity, [
            'translation_domain' => 'cms'
        ]);

        if ('POST' === $request->getMethod()) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getEm()->persist($entity);

                $this->getEm()->flush();

                $routerInvalidator->invalidate();

                $this->addFlashSuccess($this->getTranslator()
                    ->trans('%entity% has been saved.', ['%entity%' => $entity], 'globals'));

                if ($request->request->has('save')) {
                    return $this->redirect($this->generateUrl('cms_mapping'));
                }

                return $this->redirect($this->generateUrl('cms_mapping_edit', array(
                    'id' => $entity->getId() ?: 0
                )));

            } else {
                $this->addFlashError($this->getTranslator()
                    ->trans('Some fields are invalid.', [], 'globals'));
            }
        }

        return $this->render('cms/mapping/edit.html.twig', [
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
        $entity = $this->getRepository(Mapping::class)->find($id);
        $output = $this->checkDeleteEntity($entity);

        return new JsonResponse($output);
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function delete($id)
    {
        $entity = $this->getRepository(Mapping::class)->find($id);
        $this->deleteEntity($entity);

        return $this->redirect($this->generateUrl('cms_mapping'));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function order(Request $request)
    {
        $this->orderEntities($request, $this->getRepository(Mapping::class));

        return new Response('');
    }
}