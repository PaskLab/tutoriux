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
     * @var NavigationElementInterface
     */
    protected $currentElement;

    /**
     * @var array Array of element
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
        $this->currentElement = null;
        $this->elements = [];

        $this->initialized = false;
    }

    /**
     * @return bool
     */
    public function isReady(): bool
    {
        return isset(
            $this->request,
            $this->doctrine,
            $this->session,
            $this->breadcrumbs,
            $this->pageTitle
        );
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
     */
    public function init(): void
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
     * @return ApplicationCoreInterface
     */
    public function setRequest(Request $request): ApplicationCoreInterface
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
     * @return ApplicationCoreInterface
     */
    public function setDoctrine(RegistryInterface $doctrine): ApplicationCoreInterface
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
     * @return ApplicationCoreInterface
     */
    public function setSession(SessionInterface $session): ApplicationCoreInterface
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Get the current Section ID
     *
     * @return integer
     */
    public function getSectionId(): int
    {
        $sectionId = 0;

        if ($this->request) {
            $tutoriuxRequest = $this->request->attributes->get('_tutoriuxRequest', ['sectionId' => 0]);
            $sectionId = $tutoriuxRequest['sectionId'];
        }

        return $sectionId;
    }

    /**
     * @param $section
     * @return ApplicationCoreInterface
     */
    public function setSection($section): ApplicationCoreInterface
    {
        $this->section = $section;

        return $this;
    }

    /**
     * @return Section|null
     */
    public function getSection(): ?Section
    {
        if ($this->section) {
            return $this->section;
        }

        return null;
    }

    /**
     * Get the current (last pushed) navigation element.
     *
     * @return NavigationElementInterface
     */
    public function getCurrentElement(): NavigationElementInterface
    {
        return $this->currentElement;
    }

    /**
     * @param Breadcrumbs $breadcrumbs
     * @return ApplicationCoreInterface
     */
    public function setBreadcrumbs(Breadcrumbs $breadcrumbs): ApplicationCoreInterface
    {
        $this->breadcrumbs = $breadcrumbs;

        return $this;
    }

    /**
     * @return Breadcrumbs
     */
    public function getBreadcrumbs(): Breadcrumbs
    {
        return $this->breadcrumbs;
    }

    /**
     * @param PageTitle $pageTitle
     * @return ApplicationCoreInterface
     */
    public function setPageTitle(PageTitle $pageTitle): ApplicationCoreInterface
    {
        $this->pageTitle = $pageTitle;

        return $this;
    }

    /**
     * @return PageTitle
     */
    public function getPageTitle(): PageTitle
    {
        return $this->pageTitle;
    }

    /**
     * @param string $locale
     * @return ApplicationCoreInterface
     */
    public function setLocale(string $locale): ApplicationCoreInterface
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get the locale
     *
     * @return string
     */
    public function getLocale(): string
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
    public function getEditLocale(): string
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
     * @return ApplicationCoreInterface
     */
    public function setEditLocaleEnabled(bool $editLocaleEnabled): ApplicationCoreInterface
    {
        $this->editLocaleEnabled = $editLocaleEnabled;

        return $this;
    }

    /**
     * Get Elements
     *
     * @return array
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    /**
     * @param NavigationElementInterface $element
     * @return ApplicationCoreInterface
     */
    public function addNavigationElement(NavigationElementInterface $element): ApplicationCoreInterface
    {
        $this->breadcrumbs->addElement($element);
        $this->pageTitle->addElement($element);

        $this->elements[] = $element;
        $this->currentElement = $element;

        return $this;
    }

    /**
     * @param NavigationElementInterface $element
     * @return ApplicationCoreInterface
     */
    public function removeNavigationElement(NavigationElementInterface $element): ApplicationCoreInterface
    {
        foreach ($this->elements as $k => $existingElement) {
            if ($element == $existingElement) {
                unset($this->elements[$k]);
                $this->breadcrumbs->removeElement($element);
                $this->pageTitle->removeElement($element);
                $this->currentElement = end($this->elements);
                break;
            }
        }

        return $this;
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
     * @param callable $doctrineInit
     * @return ApplicationCoreInterface
     */
    public function setDoctrineInit(callable $doctrineInit): ApplicationCoreInterface
    {
        $this->doctrineInit = $doctrineInit;

        return $this;
    }
}