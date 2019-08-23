<?php

namespace App\Controller\Cms\Role;

use App\Repository\RoleRepository;
use App\Services\ApplicationCore;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Library\BaseController;
use App\Entity\Role;
use App\Form\Cms\Role\RoleType;

/**
 * Class RoleController
 * @package App\Controller\Cms\Role
 */
class RoleController extends BaseController
{

    /**
     * @var bool
     */
    protected $isAdmin;

    /**
     * @var bool
     */
    protected $isDeveloper;

    /**
     * @var array
     */
    protected $rolesAdmin;

    /**
     * RoleController constructor.
     * @param ApplicationCore $applicationCore
     * @throws \Exception
     */
    public function __construct(ApplicationCore $applicationCore)
    {
        parent::__construct($applicationCore);

        // Check if the current User has the privileges
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_BACKEND_ADMIN')) {
            throw new AccessDeniedHttpException();
        }

        $this->createAndPushNavigationElement('Roles', 'cms_role', [], 'fa-lock');

        // Add/remove some behaviors if Admin
        $this->isAdmin = $this->get('security.authorization_checker')->isGranted('ROLE_BACKEND_ADMIN');
        $this->isDeveloper = $this->get('security.authorization_checker')->isGranted('ROLE_DEVELOPER');
        $this->rolesAdmin = ['ROLE_BACKEND_ADMIN', 'ROLE_DEVELOPER'];
    }

    /**
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function index()
    {
        /** @var RoleRepository $roleRepository */
        $roleRepository = $this->getRepository(Role::class);

        if (!$this->isDeveloper) {
            $roles = $roleRepository->findAllExcept(['ROLE_DEVELOPER', 'ROLE_BACKEND_ACCESS']);
        } else {
            $roles = $roleRepository->findAllExcept('ROLE_BACKEND_ACCESS');
        }

        return $this->render('cms/role:list.html.twig', [
            'roles' => $roles
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
        $entity = $this->getRepository(Role::class)->find($id);

        if (!$entity) {
            $entity = $this->getDoctrineInit()->initEntity(new Role());
        }

        // Not editable
        if ($entity->getRole() == 'ROLE_DEVELOPER' && !$this->isDeveloper) {
            throw new NotFoundHttpException();
        }

        $this->createAndPushNavigationElement(
            $entity->getEntityName(),
            $entity->getRouteBackend(),
            $entity->getRouteBackendParams(),
            'fa-lock'
        );

        $form = $this->createForm(
            RoleType::class, $entity,
            ['admin' => in_array($entity->getRole(), $this->rolesAdmin)]
        );

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                // Set a Rolename for this Role
                if (!in_array($entity->getRole(), $this->rolesAdmin)) {
                    $roleName = 'ROLE_BACKEND_' . strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $entity->getName()));
                    $entity->setRole($roleName);
                }

                $this->getEm()->persist($entity);
                $this->getEm()->flush();

                $this->addFlashSuccess($this->getTranslator()->trans(
                    '%entity% has been saved.', ['%entity%' => $entity], 'globals'
                ));

                if ($request->request->has('save')) {
                    return $this->redirect($this->generateUrl('cms_role'));
                }

                return $this->redirect($this->generateUrl('cms_role_edit', ['id' => $entity->getId() ? : 0]));

            } else {
                $this->addFlashError($this->getTranslator()->trans(
                    'Some fields are invalid.', [], 'globals'
                ));
            }
        }

        return $this->render(
            'cms/role/edit.html.twig', [
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
        $role = $this->getRepository(Role::class)->find($id);
        $output = $this->checkDeleteEntity($role);

        return new JsonResponse($output);
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function delete($id)
    {
        $role = $this->getRepository(Role::class)->find($id);
        $this->deleteEntity($role);

        return $this->redirect($this->generateUrl('cms_role'));
    }
}
