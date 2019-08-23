<?php

namespace App\Controller\Cms\Section;

use App\Entity\Navigation;
use App\Entity\SectionNavigation;
use App\Services\ApplicationCore;
use App\Services\RouterInvalidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use App\Entity\Mapping;
use App\Repository\NavigationRepository;
use App\Repository\SectionRepository;
use App\Library\BaseController;
use App\Entity\Section;
use App\Form\Cms\Section\RootSectionType;

/**
 * Class RootController
 * @package App\Controller\Cms\Section
 */
class RootController extends BaseController
{
    /**
     * RootController constructor.
     * @param ApplicationCore $applicationCore
     * @throws \Exception
     */
    public function __construct(ApplicationCore $applicationCore)
    {
        parent::__construct($applicationCore);

        // Access restricted to ROLE_BACKEND_ADMIN
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_BACKEND_ADMIN')) {
            throw new AccessDeniedHttpException('You don\'t have the privileges to view this page.');
        }

        $this->createAndPushNavigationElement(
            'Root Sections',
            'cms_section_root',
            'fa-sitemap'
        );
    }

    /**
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function list()
    {
        /** @var NavigationRepository $navigationRepository */
        $navigationRepository = $this->getRepository(Navigation::class);
        $navigations = $navigationRepository->findHaveSections();

        /** @var SectionRepository $sectionRepository */
        $sectionRepository = $this->getRepository(Section::class);
        $withoutNavigation = $sectionRepository->findRootsWithoutNavigation();

        return $this->render('cms/section/root/list.html.twig', [
            'navigations' => $navigations,
            'withoutNavigation' => $withoutNavigation
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
        /** @var SectionRepository $sectionRepository */
        $sectionRepository = $this->getRepository(Section::class);

        $entity = $sectionRepository->find($id);

        if (false == $entity) {
            $entity = $this->getDoctrineInit()
                ->initEntity(new Section());
        }

        $this->createAndPushNavigationElement(
            $entity->getEntityName(),
            'cms_section_root_edit',
            array(
                'id' => ($entity->getId()) ?: 0
            ),
            'fa-sitemap'
        );

        $form = $this->createForm(RootSectionType::class, $entity, [
            'current_section' => $entity,
            'current_locale' => $request->getLocale(),
            'translation_domain' => 'cms'
        ]);

        if ('POST' == $request->getMethod()) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getEm()->persist($entity);

                // On insert
                if (false == $id) {

                    /** @var NavigationRepository $navigationRepository */
                    $navigationRepository = $this->getRepository(Navigation::class);
                    /** @var Navigation $sectionModuleBar */
                    $sectionModuleBar = $navigationRepository->find(NavigationRepository::SECTION_MODULE_BAR_ID);

                    $mapping = new Mapping();
                    $mapping->setSection($entity);
                    $mapping->setType('route');
                    $mapping->setTarget('cms_text');

                    $entity->addMapping($mapping);

                    $mapping = new Mapping();
                    $mapping->setSection($entity);
                    $mapping->setNavigation($sectionModuleBar);
                    $mapping->setType('render');
                    $mapping->setTarget('App\Controller\Cms\Text\NavigationController::sectionModuleBar');

                    $entity->addMapping($mapping);

                    $mapping = new Mapping();
                    $mapping->setSection($entity);
                    $mapping->setNavigation($sectionModuleBar);
                    $mapping->setType('render');
                    $mapping->setTarget('App\Controller\Cms\Section\NavigationController::sectionModuleBar');

                    $entity->addMapping($mapping);

                    $mapping = new Mapping();
                    $mapping->setSection($entity);
                    $mapping->setNavigation($sectionModuleBar);
                    $mapping->setType('render');
                    $mapping->setTarget('App\Controller\Cms\Mapping\NavigationController::sectionModuleBar');

                    $entity->addMapping($mapping);

                    // Frontend mapping
                    $mapping = new Mapping();
                    $mapping->setSection($entity);
                    $mapping->setType('route');
                    $mapping->setTarget('site_text');

                    $entity->addMapping($mapping);
                }

                $this->getEm()->flush();
                $routerInvalidator->invalidate();

                $this->addFlashSuccess($this->getTranslator()->trans(
                    '%entity% has been saved.',
                    ['%entity%' => $entity],
                    'globals'
                ));

                if ($request->request->has('save')) {
                    return $this->redirect($this->generateUrl('cms_section_root'));
                }

                return $this->redirect($this->generateUrl('cms_section_root_edit', array(
                    'id' => $entity->getId() ? : 0
                )));
            } else {
                $this->addFlashError($this->getTranslator()->trans(
                    'Some fields are invalid.', [], 'globals'
                ));
            }
        }

        return $this->render('cms/section/root/edit.html.twig', [
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
        $section = $this->getRepository(Section::class)->find($id);
        $output = $this->checkDeleteEntity($section);

        return new JsonResponse($output);
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function delete($id)
    {
        $section = $this->getRepository(Section::class)->find($id);
        $this->deleteEntity($section);

        return $this->redirect($this->generateUrl('cms_section_root'));
    }

    /**
     * @param Request $request
     * @param RouterInvalidator $routerInvalidator
     * @return Response
     */
    public function order(Request $request, RouterInvalidator $routerInvalidator)
    {
        $sectionRepo = $this->getRepository(Section::class);
        $this->orderEntities($request, $sectionRepo);
        $routerInvalidator->invalidate();

        return new Response('');
    }

    /**
     * @param Request $request
     * @param RouterInvalidator $routerInvalidator
     * @return Response
     */
    public function orderSectionNavigation(Request $request, RouterInvalidator $routerInvalidator)
    {
        $sectionNavigationRepo = $this->getRepository(SectionNavigation::class);
        $this->orderEntities($request, $sectionNavigationRepo);
        $routerInvalidator->invalidate();

        return new Response('');
    }
}
