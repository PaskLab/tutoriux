<?php

namespace App\Controller\Cms\User;

use App\Repository\RoleRepository;
use App\Services\ApplicationCore;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use App\Library\BaseController;
use App\Entity\User;
use App\Form\Cms\User\UserType;
use App\Entity\Role;

/**
 * Class UserController
 * @package App\Controller\Cms\User
 */
class UserController extends BaseController
{
    /**
     * @var bool
     */
    protected $isDeveloper;

    /**
     * UserController constructor.
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

        $this->createAndPushNavigationElement('Users', 'cms_user', [], 'fa-users');

        $this->isDeveloper = $this->get('security.authorization_checker')->isGranted('ROLE_DEVELOPER');
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
            $roles = $roleRepository->findAllExcept(array('ROLE_DEVELOPER', 'ROLE_BACKEND_ACCESS'));
        } else {
            $roles = $roleRepository->findAllExcept('ROLE_BACKEND_ACCESS');
        }

        return $this->render('cms/user/list.html.twig', ['roles' => $roles]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function edit(Request $request, $id)
    {
        $user = $this->getRepository(User::class)->find($id);

        if (!$user) {
            $user = new User();
        }

        $this->createAndPushNavigationElement((string) $user, 'cms_user_edit', [
            'id' => ($user->getId()) ?: 0
        ], 'fa-user');

        $form = $this->createForm(UserType::class, $user, [
            'validation_groups' => $user->getId() ? 'edit' : 'cms_new',
            'self_edit' => $user == $this->getUser(),
            'developer' => $this->isDeveloper
        ]);

        if ($request->getMethod() == 'POST') {

            $previousEncodedPassword = $user->getPassword();

            $form->handleRequest($request);

            if ($form->isValid()) {

                // All Users are automatically granted the ROLE_BACKEND_ACCESS Role
                $backendAccessRole = $this->getRepository(Role::class)
                    ->findOneBy(['role' => 'ROLE_BACKEND_ACCESS']);
                if (!$backendAccessRole) {
                    $backendAccessRole = new Role();
                    $backendAccessRole->setRole('ROLE_BACKEND_ACCESS');
                    $this->getEm()->persist($backendAccessRole);
                }

                $user->addRole($backendAccessRole);

                // New password set
                if ($form->get('password')->getData()) {
                    $encoder = $this->get('security.encoder_factory')->getEncoder($user);
                    $encodedPassword = $encoder->encodePassword($user->getPassword(), $user->getSalt());
                } else {
                    $encodedPassword = $previousEncodedPassword;
                }

                $user->setPassword($encodedPassword);

                // Forcing the selected locale by the user
                if ($locale = $user->getLocale()) {
                    $request->setLocale($locale);
                }

                $this->getEm()->persist($user);
                $this->getEm()->flush();

                $this->addFlashSuccess($this->getTranslator()->trans(
                    '%entity% has been saved.', ['%entity%' => $user], 'globals'
                ));

                if ($request->request->has('save')) {
                    return $this->redirect($this->generateUrl('cms_user'));
                }

                return $this->redirect($this->generateUrl('cms_user_edit', [
                    'id' => $user->getId()
                ]));

            } else {
                $this->addFlashError($this->getTranslator()->trans(
                    'Some fields are invalid.', [], 'globals'
                ));
            }
        }

        return $this->render('cms/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function checkDelete($id)
    {
        $user = $this->getRepository(User::class)->find($id);
        $output = $this->checkDeleteEntity($user);

        return new JsonResponse($output);
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function delete($id)
    {
        $user = $this->getRepository(User::class)->find($id);
        $this->deleteEntity($user);

        return $this->redirect($this->generateUrl('cms_user'));
    }
}