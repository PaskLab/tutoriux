<?php

namespace App\Library;

use App\Services\Deletable;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\ApplicationCore;
use App\Entity\Section;
use App\Services\DoctrineInit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class BaseController
 * @package App\Library
 */
abstract class BaseController extends AbstractController
{
    const FLASH_SUCCESS = 'success';
    const FLASH_ERROR = 'error';

    /**
     * @var ApplicationCore
     */
    private $applicationCore;

    /**
     * @var DoctrineInit
     */
    private $doctrineInit;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Deletable
     */
    private $deletable;

    /**
     * BaseController constructor.
     * @param ApplicationCore $applicationCore
     */
    public function __construct(ApplicationCore $applicationCore)
    {
        $this->applicationCore = $applicationCore;
    }

    /**
     * @return ApplicationCore
     */
    protected function getApplicationCore()
    {
        return $this->applicationCore;
    }

    /**
     * Get the current section entity
     *
     * @return Section
     */
    protected function getSection()
    {
        return $this->getApplicationCore()->getSection();
    }

    /**
     * @param null $manager
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getEm($manager = null)
    {
        return $this->getDoctrine()->getManager($manager);
    }

    /**
     * @param $name
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository($className)
    {
        return $this->getDoctrineInit()->initRepository(
            $this->getDoctrine()->getRepository($className)
        );
    }

    /**
     * @param $type
     * @param $message
     */
    protected function setFlash($type, $message)
    {
        $this->get('session')->getFlashBag()->set($type, $message);
    }

    /**
     * @param $type
     * @return bool
     */
    protected function hasFlash($type)
    {
        return $this->container->get('session')->getFlashBag()->has($type);
    }

    /**
     * @param $type
     * @param array $default
     * @return array
     */
    protected function getFlash($type, array $default = array())
    {
        return $this->container->get('session')->getFlashBag()->get($type, $default);
    }

    /**
     * @param $message
     */
    protected function setFlashSuccess($message)
    {
        $this->setFlash(BaseController::FLASH_SUCCESS, $message);
    }

    /**
     * @param $message
     */
    protected function addFlashSuccess($message)
    {
        $this->addFlash(BaseController::FLASH_SUCCESS, $message);
    }

    /**
     * @param $message
     */
    protected function addFlashError($message)
    {
        $this->addFlash(BaseController::FLASH_ERROR, $message);
    }

    /**
     * @param $element
     * @throws \Exception
     */
    protected function addNavigationElement($element)
    {
        trigger_error('addNavigationElement is deprecated. Use pushNavigationElement instead.', E_USER_DEPRECATED);

        $this->pushNavigationElement($element);
    }

    /**
     * @param $element
     */
    protected function pushNavigationElement($element)
    {
        $this->getApplicationCore()->addNavigationElement($element);
    }

    /**
     * Helper method to create a navigation element
     *
     * @param string $name
     * @param string $route
     * @param array  $routeParams
     *
     * @return NavigationElementInterface
     */
    protected function createNavigationElement($name, $route, $routeParams = array(), $icon = null)
    {
        $navigationElement = new NavigationElement();
        $navigationElement
            ->setElementName($name)
            ->setRoute($route)
            ->setRouteParams($routeParams)
            ->setElementIcon($icon);

        return $navigationElement;
    }

    /**
     * @param $name
     * @param $route
     * @param array $routeParams
     * @param null $icon
     * @throws \Exception
     */
    protected function createAndPushNavigationElement($name, $route, $routeParams = array(), $icon = null)
    {
        $navigationElement = $this->createNavigationElement($name, $route, $routeParams, $icon);
        $this->pushNavigationElement($navigationElement);
    }

    /**
     * @return DoctrineInit
     */
    protected function getDoctrineInit(): DoctrineInit
    {
        if (!$this->doctrineInit) {
            $this->doctrineInit = $this->applicationCore->getDoctrineInit();
        }

        return $this->doctrineInit;
    }

    /**
     * @return TranslatorInterface
     */
    protected function getTranslator(): TranslatorInterface
    {
        if (!$this->translator) {
            $this->translator = $this->applicationCore->getTranslator();
        }

        return $this->translator;
    }

    /**
     * @return Deletable
     */
    protected function getDeletable(): Deletable
    {
        if (!$this->deletable) {
            $this->deletable = $this->applicationCore->getDeletable();
        }

        return $this->deletable;
    }

    /**
     * @param $entity
     * @return array
     */
    protected function checkDeleteEntity($entity)
    {
        if (null === $entity) {
            throw new NotFoundHttpException();
        }

        $result = $this->deletable->checkDeletable($entity);
        $output = $result->toArray();
        $output['template'] = $this->renderView('globals/delete_message.html.twig',
            array(
                'entity' => $entity,
                'result' => $result
            )
        );

        return $output;
    }

    /**
     * Performs the delete action on an entity.
     *
     * The "Deletable Service" will be called to check if the entity can be deleted or not.
     * If you want to add more check for an entity, you can add listeners to the service.
     *
     * @param mixed $entity
     *
     * @throws NotFoundHttpException
     */
    protected function deleteEntity(object $entity)
    {
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find this ' . get_class($this) . ' entity.');
        }

        $result = $this->deletable->checkDeletable($entity);
        if ($result->isSuccess()) {
            $this->addFlashSuccess($this->getTranslator()->trans(
                '%entity% has been deleted.',
                array('%entity%' => $entity), 'globals'
            ));

            $this->getEm()->remove($entity);
            $this->getEm()->flush();
        } else {
            $this->addFlashError($result->getErrors());
        }
    }

    /**
     * @param Request $request
     * @param ObjectRepository $repository
     */
    protected function orderEntities(Request $request, ObjectRepository $repository)
    {
        if ($request->isXmlHttpRequest()) {

            $i = 0;
            $elements = explode(';', trim($request->get('elements'), ';'));

            foreach ($elements as $element) {

                $element = explode('-', $element);
                $entity = $repository->find($element[2]);

                if ($entity) {
                    $entity->setOrdering(++$i);
                    $this->getEm()->persist($entity);
                }

                $this->getEm()->flush();
            }
        }
    }
}
