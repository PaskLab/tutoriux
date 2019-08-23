<?php

namespace App\Controller\Cms\Locale;

use App\Services\ApplicationCore;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Library\BaseController;
use App\Entity\Locale;
use App\Form\Cms\Locale\LocaleType;

/**
 * Class LocaleController
 * @package App\Controller\Cms\Locale
 */
class LocaleController extends BaseController
{
    /**
     * LocaleController constructor.
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

        $this->createAndPushNavigationElement('Locales', 'cms_locale', array(), 'fa-comments');
    }

    /**
     * @return Response
     */
    public function list()
    {
        $locales = $this->getRepository(Locale::class)->findBy(array(), array('ordering' => 'ASC'));

        return $this->render('cms/locale/list.html.twig', array(
            'locales' => $locales,
            'default_locale' => $this->getParameter('locale')
        ));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function edit(Request $request, $id)
    {
        /**
         * @var $locale Locale
         */
        $locale = $this->getRepository(Locale::class)->find($id);

        if (false == $locale) {
            $locale = $this->getDoctrineInit()->initEntity(new Locale());
        }

        $this->createAndPushNavigationElement(
            $locale->getEntityName(),
            $locale->getRouteBackend(),
            $locale->getRouteBackendParams(),
            'fa-comment'
        );

        $form = $this->createForm(LocaleType::class, $locale);

        if ('POST' == $request->getMethod()) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getEm()->persist($locale);
                $this->getEm()->flush();

                $this->addFlashSuccess($this->getTranslator()->trans(
                    '%entity% has been saved.',
                    array('%entity%' => $locale), 'globals')
                );

                if ($request->request->has('save')) {
                    return $this->redirect($this->generateUrl('cms_locale'));
                }

                return $this->redirect($this->generateUrl($locale->getRoute(), $locale->getRouteParams()));
            } else {
                $this->addFlashError('Some fields are invalid.');
            }
        }

        return $this->render('cms/locale/edit.html.twig', array(
            'entity' => $locale,
            'default_locale' => $this->getParameter('locale'),
            'form' => $form->createView()
        ));
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function checkDelete($id)
    {
        $locale = $this->getRepository(Locale::class)->find($id);
        $output = $this->checkDeleteEntity($locale);

        return new JsonResponse($output);
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function delete($id)
    {
        $locale = $this->getRepository(Locale::class)->find($id);
        $this->deleteEntity($locale);

        return $this->redirect($this->generateUrl('cms_locale'));
    }
}
