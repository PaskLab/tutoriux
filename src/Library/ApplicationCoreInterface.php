<?php

namespace App\Library;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\Section;
use App\Services\DoctrineInit;
use App\Services\PageTitle;
use App\Services\Breadcrumbs;
use App\Services\Deletable;

/**
 * Interface ApplicationCoreInterface
 * @package App\Library
 */
interface ApplicationCoreInterface
{
    /**
     * @return bool
     */
    public function isReady(): bool;

    /**
     * @return bool
     */
    public function isSectionNavInitialized() : bool;

    /**
     * @return void
     */
    public function initSectionNav(): void;

    /**
     * @return Request
     */
    public function getRequest(): Request;

    /**
     * @param Request $request
     * @return ApplicationCoreInterface
     */
    public function setRequest(Request $request): ApplicationCoreInterface;

    /**
     * @return RegistryInterface
     */
    public function getDoctrine(): RegistryInterface;

    /**
     * @param RegistryInterface $doctrine
     * @return ApplicationCoreInterface
     */
    public function setDoctrine(RegistryInterface $doctrine): ApplicationCoreInterface;

    /**
     * @return SessionInterface
     */
    public function getSession(): SessionInterface;

    /**
     * @param SessionInterface $session
     * @return ApplicationCoreInterface
     */
    public function setSession(SessionInterface $session): ApplicationCoreInterface;

    /**
     * @return int
     */
    public function getSectionId(): int;

    /**
     * @param $section
     * @return ApplicationCoreInterface
     */
    public function setSection($section): ApplicationCoreInterface;

    /**
     * @return Section|null
     */
    public function getSection(): ?Section;

    /**
     * @return NavigationElementInterface
     */
    public function getCurrentElement(): ?NavigationElementInterface;

    /**
     * @param Breadcrumbs $breadcrumbs
     * @return ApplicationCoreInterface
     */
    public function setBreadcrumbs(Breadcrumbs $breadcrumbs): ApplicationCoreInterface;

    /**
     * @return Breadcrumbs
     */
    public function getBreadcrumbs(): Breadcrumbs;

    /**
     * @param PageTitle $pageTitle
     * @return ApplicationCoreInterface
     */
    public function setPageTitle(PageTitle $pageTitle): ApplicationCoreInterface;

    /**
     * @return PageTitle
     */
    public function getPageTitle(): PageTitle;

    /**
     * @param string $locale
     * @return ApplicationCoreInterface
     */
    public function setLocale(string $locale): ApplicationCoreInterface;

    /**
     * @return string
     */
    public function getLocale(): string;

    /**
     * @return string
     */
    public function getEditLocale(): string;

    /**
     * @return bool
     */
    public function isEditLocaleEnabled(): bool;

    /**
     * @param bool $editLocaleEnabled
     * @return ApplicationCoreInterface
     */
    public function setEditLocaleEnabled(bool $editLocaleEnabled): ApplicationCoreInterface;

    /**
     * @return array
     */
    public function getElements(): array;

    /**
     * @param NavigationElementInterface $element
     * @return ApplicationCoreInterface
     */
    public function addNavigationElement(NavigationElementInterface $element): ApplicationCoreInterface;

    /**
     * @param NavigationElementInterface $element
     * @return ApplicationCoreInterface
     */
    public function removeNavigationElement(NavigationElementInterface $element): ApplicationCoreInterface;

    /**
     * @return DoctrineInit
     */
    public function getDoctrineInit(): DoctrineInit;

    /**
     * @param callable $doctrineInit
     * @return ApplicationCoreInterface
     */
    public function setDoctrineInit(callable $doctrineInit): ApplicationCoreInterface;

    /**
     * @return Deletable
     */
    public function getDeletable(): Deletable;

    /**
     * @param callable $deletable
     * @return ApplicationCoreInterface
     */
    public function setDeletable(callable $deletable): ApplicationCoreInterface;

    /**
     * @return TranslatorInterface
     */
    public function getTranslator(): TranslatorInterface;

    /**
     * @param callable $translator
     * @return ApplicationCoreInterface
     */
    public function setTranslator(callable $translator): ApplicationCoreInterface;
}
