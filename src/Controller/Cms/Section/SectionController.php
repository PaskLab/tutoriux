<?php

namespace App\Controller\Cms\Section;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Services\ApplicationCore;
use App\Library\BaseController;
use App\Entity\Mapping;
use App\Repository\NavigationRepository;
use App\Entity\Section;
use App\Form\Cms\Section\SectionType;
use App\Entity\Navigation;
use App\Services\RouterInvalidator;

/**
 * Class SectionController
 * @package App\Controller\Cms\Section
 */
class SectionController extends BaseController
{
    /**
     * SectionController constructor.
     * @param ApplicationCore $applicationCore
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(ApplicationCore $applicationCore, AuthorizationCheckerInterface $authorizationChecker)
    {
        parent::__construct($applicationCore);

        // Access restricted to ROLE_BACKEND_ADMIN
        if (false === $authorizationChecker->isGranted('ROLE_BACKEND_ADMIN')) {
            throw new AccessDeniedHttpException('You don\'t have the privileges to view this page.');
        }
    }

    /**
     * @return Response
     */
    public function index()
    {
        $entities = $this->getRepository(Section::class)->findBy(
            ['parent' => $this->getSection()->getId()],
            ['ordering' => 'ASC']
        );

        return $this->render('cms/section/section/list.html.twig', ['entities' => $entities]);
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

        $entity = $this->getRepository(Section::class)->find($id);

        if (!$entity) {
            $entity = $this->getDoctrineInit()->initEntity(new Section());
            $entity->setParent($section);
        }

        $this->createAndPushNavigationElement((string) $entity, 'cms_section_edit', [
            'id' => ($entity->getId()) ?: 0
        ], 'fa-sitemap');

        $form = $this->createForm(SectionType::class, $entity, [
            'current_section' => $entity,
            'current_locale' => $request->getLocale(),
            'translation_domain' => 'cms'
        ]);

        if ('POST' === $request->getMethod()) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getEm()->persist($entity);

                // On insert
                if (false == $id) {

                    $sectionModuleBar = $this->getRepository(Navigation::class)
                        ->find(NavigationRepository::SECTION_MODULE_BAR_ID);

                    $mapping = new Mapping();
                    $mapping->setSection($entity);
                    $mapping->setType('route');
                    $mapping->setTarget('cms_text');

                    $entity->addMapping($mapping);

                    $mapping = new Mapping();
                    $mapping->setSection($entity);
                    $mapping->setNavigation($sectionModuleBar);
                    $mapping->setType('render');
                    $mapping->setTarget('App\\Controller\\Cms\\Text\\NavigationController::sectionModuleBar');

                    $entity->addMapping($mapping);

                    $mapping = new Mapping();
                    $mapping->setSection($entity);
                    $mapping->setNavigation($sectionModuleBar);
                    $mapping->setType('render');
                    $mapping->setTarget('App\\Controller\\Cms\\Section\\NavigationController::sectionModuleBar');

                    $entity->addMapping($mapping);

                    $mapping = new Mapping();
                    $mapping->setSection($entity);
                    $mapping->setNavigation($sectionModuleBar);
                    $mapping->setType('render');
                    $mapping->setTarget('App\\Controller\\Cms\\Mapping\\NavigationController::sectionModuleBar');

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
                    '%entity% has been saved.', ['%entity%' => $entity], 'globals'
                ));

                if ($request->request->has('save')) {
                    return $this->redirect($this->generateUrl('cms_section'));
                }

                return $this->redirect($this->generateUrl('cms_section_edit', ['id' => $entity->getId() ?: 0]));
            } else {
                $this->addFlashError($this->getTranslator()->trans(
                    'Some fields are invalid.', [], 'globals'
                ));
            }
        }

        return $this->render('cms/section/section:edit.html.twig', [
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
     * @param RouterInvalidator $routerInvalidator
     * @param $id
     * @return RedirectResponse
     */
    public function delete(RouterInvalidator $routerInvalidator, $id)
    {
        $section = $this->getRepository(Section::class)->find($id);
        $this->deleteEntity($section);
        $routerInvalidator->invalidate();

        return $this->redirect($this->generateUrl('cms_section'));
    }

    /**
     * @param Request $request
     * @param RouterInvalidator $routerInvalidator
     * @return Response
     */
    public function order(Request $request, RouterInvalidator $routerInvalidator)
    {
        $this->orderEntities($request, $this->getRepository(Section::class));
        $routerInvalidator->invalidate();

        return new Response('');
    }

    /**
     * @param Request $request
     * @param RouterInvalidator $routerInvalidator
     * @return Response
     */
    public function orderAlpha(Request $request, RouterInvalidator $routerInvalidator)
    {
        if ($request->isXmlHttpRequest()) {

            $elements = explode(';', trim($request->get('elements'), ';'));

            foreach ($elements as $element) {

                $element = explode('-', $element);
                $entity = $this->getRepository(Section::class)->find($element[2]);

                if ($entity) {
                    $entity->setOrdering(0);
                    $this->getEm()->persist($entity);
                }

                $this->getEm()->flush();
            }
        }

        $routerInvalidator->invalidate();

        return new Response('');
    }
}
