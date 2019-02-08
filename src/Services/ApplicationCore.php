<?php

namespace App\Services;


use Symfony\Bridge\Doctrine\RegistryInterface,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Session\SessionInterface,
    Symfony\Component\HttpFoundation\RequestStack,
    Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use App\Entity\Section,
    App\Repository\SectionRepository,
    App\Library\NavigationElementInterface,
    App\Library\ApplicationCoreInterface;

/**
 * Class ApplicationCore
 * @package App\Services
 */
class ApplicationCore implements ApplicationCoreInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var String
     */
    protected $locale;

    /**
     * @var Breadcrumbs
     */
    protected $breadcrumbs;

    /**
     * @var PageTitle
     */
    protected $pageTitle;

    /**
     * The current section entity
     * @var Section
     */
    protected $section;

    /**
     * The current element (can be any entity implementing NavigationElementInterface)
     * @var object
     */
    protected $element;

    /**
     * @var array Array of elements
     */
    protected $elements;

    /**
     * LazyLoaded
     *
     * @var callable
     */
    protected $doctrineInit;

    /**
     * @var bool
     */
    protected $initialized;

    /**
     * @var bool
     */
    protected $editLocaleEnabled = false;

    /**
     * ApplicationCore constructor.
     * @param RequestStack $requestStack
     * @param RegistryInterface $doctrine
     * @param SessionInterface $session
     * @param Breadcrumbs $breadcrumbs
     * @param PageTitle $pageTitle
     */
    public function __construct(RequestStack $requestStack, RegistryInterface $doctrine, SessionInterface $session,
                                Breadcrumbs $breadcrumbs, PageTitle $pageTitle)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->doctrine = $doctrine;
        $this->session = $session;
        $this->breadcrumbs = $breadcrumbs;
        $this->pageTitle = $pageTitle;
        $this->locale = null;

        $this->initialized = false;
    }

    /**
     * @return bool
     */
    public function isInitialized() : bool
    {
        return $this->initialized;
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     */
    public function init()
    {
        if (!$this->initialized) {
            $em = $this->doctrine->getManager();

            if ($sectionId = $this->getSectionId()) {

                /** @var $sectionRepository SectionRepository */
                $sectionRepository = $this->getDoctrineInit()
                    ->initRepository($em->getRepository(Section::class));

                $criteria = ['id' => $sectionId];

                if ('cms' !== $this->getRequest()->get('_tutoriuxContext', 'site')) {
                    // Avoid displaying an inactive section while browsing the public site
                    $criteria['active'] = true;
                }

                $this->setSection($sectionRepository->findOneBy($criteria));
            }

            // If a section has been found
            if ($section = $this->getSection()) {
                foreach ($section->getParents() as $parent) {
                    $this->addNavigationElement($parent);
                }

                $this->addNavigationElement($section);
            } else {
                throw new NotFoundHttpException(
                    sprintf('The section [id: %s] does not exist or is not active in the database', $sectionId)
                );
            }

            $this->initialized = true;
        }
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return RegistryInterface
     */
    public function getDoctrine(): RegistryInterface
    {
        return $this->doctrine;
    }

    /**
     * @param RegistryInterface $doctrine
     * @return $this
     */
    public function setDoctrine(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;

        return $this;
    }

    /**
     * @return SessionInterface
     */
    public function getSession(): SessionInterface
    {
        return $this->session;
    }

    /**
     * @param SessionInterface $session
     * @return $this
     */
    public function setSession(SessionInterface $session)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Add an element to the Breadcrumbs and the Page Title
     *
     * @param NavigationElementInterface $element The element to push in the navigation stack
     */
    public function addNavigationElement(NavigationElementInterface $element)
    {
        $this->breadcrumbs->addElement($element);
        $this->pageTitle->addElement($element);

        $this->elements[] = $element;
        $this->element = $element;
    }

    /**
     * Get the current Section ID
     *
     * @return integer
     */
    public function getSectionId()
    {
        $tutoriuxRequest = $this->request->attributes->get('_tutoriuxRequest');

        return $tutoriuxRequest['sectionId'] ?: 0;
    }

    /**
     * Set Section
     *
     * @param Section $section
     */
    public function setSection($section)
    {
        $this->section = $section;
    }

    /**
     * Get current section
     *
     * @return Section|null
     */
    public function getSection()
    {
        if ($this->section) {
            return $this->section;
        }

        return null;
    }

    /**
     * Get the current (last pushed) element entity.
     *
     * @return object
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Set the Breadcrumbs
     *
     * @param Breadcrumbs $breadcrumbs The Breadcrumbs management object
     */
    public function setBreadcrumbs($breadcrumbs)
    {
        $this->breadcrumbs = $breadcrumbs;
    }

    /**
     * Set the Page Title
     *
     * @param PageTitle $pageTitle
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;
    }

    /**
     * Set the locale
     *
     * @param string $locale The locale
     *
     * @return void
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Get the locale
     *
     * @return string
     */
    public function getLocale() : string
    {
        if (!$this->locale) {
            $this->locale = $this->getRequest()->getLocale();
        }

        return $this->locale;
    }

    /**
     * getEditLocale
     *
     * Returns the edit locale, only the forms use this locale, the system locale is used everywhere else.
     *
     * @return string
     */
    public function getEditLocale() : string
    {
        if ($this->request->get('edit-locale')) {
            $this->getSession()->set('edit-locale', $this->request->get('edit-locale'));
        }

        if (!$this->getSession()->get('edit-locale')) {
            $this->getSession()->set('edit-locale', $this->getLocale());
        }

        return $this->getSession()->get('edit-locale');
    }

    /**
     * @return bool
     */
    public function isEditLocaleEnabled(): bool
    {
        return $this->editLocaleEnabled;
    }

    /**
     * @param bool $editLocaleEnabled
     * @return $this
     */
    public function setEditLocaleEnabled(bool $editLocaleEnabled)
    {
        $this->editLocaleEnabled = $editLocaleEnabled;

        return $this;
    }

    /**
     * Get Elements
     *
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Remove a navigation element
     *
     * @param NavigationElementInterface $element
     */
    public function removeNavigationElement(NavigationElementInterface $element)
    {
        foreach ($this->elements as $k => $existingElement) {
            if ($element == $existingElement) {
                unset($this->elements[$k]);
                $this->breadcrumbs->removeElement($element);
                $this->pageTitle->removeElement($element);
                break;
            }
        }
    }

    /**
     * @return DoctrineInit
     */
    public function getDoctrineInit(): DoctrineInit
    {
        $callable = $this->doctrineInit;

        return $callable();
    }

    /**
     * @param DoctrineInit $doctrineInit
     * @return $this
     */
    public function setDoctrineInit(callable $doctrineInit)
    {
        $this->doctrineInit = $doctrineInit;

        return $this;
    }
}