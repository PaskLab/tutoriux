<?php

namespace App\Controller\Site\User;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\UserSetting;
use App\Library\BaseController;
use App\Entity\Document\DocumentTranslation;
use App\Entity\User;
use App\Repository\Document\DocumentTranslationRepository;

/**
 * Class ModuleController
 * @package App\Controller\Site\User
 */
class ModuleController extends BaseController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function profileSidebarModule()
    {
        $request = $this->get('request_stack')->getMasterRequest();
        $username = $request->get('username');

        $user = $this->getRepository(User::class)
            ->findOneBy(['username' => $username]);

        if (!$user && !$this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            throw new NotFoundHttpException('User with username "'.$username.'" not found');
        } elseif (!$user) {
            // Needed for user account pages (no username URL attribute)
            $user = $this->getUser();
        }

        if (!$user->getSettings()) {
            $user->setSettings(new UserSetting());
        }

        /** @var DocumentTranslationRepository $documentTransRepository */
        $documentTransRepository = $this->getRepository(DocumentTranslation::class);
        $theoryCount = $documentTransRepository->getUserPublishedCount($user, '000004');
        $guideCount = $documentTransRepository->getUserPublishedCount($user, '000005');

        return $this->render(
            'site/user/_author_sidebar_module.html.twig',
            [
                'user' => $user,
                'theoryCount' => $theoryCount,
                'guideCount' => $guideCount
            ]
        );
    }
}