<?php

namespace App\Controller\Cms;

use Symfony\Component\HttpFoundation\Response;
use App\Library\BaseController;
use App\Entity\Section;
use App\Repository\SectionRepository;
use App\Services\Breadcrumbs;
use App\Services\NavigationBuilder;
use App\Repository\NavigationRepository;
use App\Services\SectionAuthFilter;
use App\Entity\Mapping;
use App\Entity\Locale;

/**
 * Class NavigationController
 * @package App\Controller\Cms
 */
class NavigationController extends BaseController
{
    /**
     * @param NavigationBuilder $navigationBuilder
     * @param SectionAuthFilter $sectionAuthFilter
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function sectionBar(NavigationBuilder $navigationBuilder, SectionAuthFilter $sectionAuthFilter)
    {
        $sectionCurrent = $this->getSection();

        if (false == $sectionCurrent) {
            $sectionCurrent = new Section();
        }

        /** @var SectionRepository $sectionRepository */
        $sectionRepository = $this->getRepository(Section::class);
        $sections = $sectionRepository->allWithJoinChildren();

        $navigationBuilder->setElements($sections);
        $navigationBuilder->setSelectedElement($sectionCurrent);
        $navigationBuilder->build();

        $sections = $navigationBuilder->getElements();

        $sections = $sectionAuthFilter->filterSections($sections);

        return $this->render('cms/navigation/section_bar.html.twig', [
            'sections' => $sections,
            'sectionCurrent' => $sectionCurrent
        ]);
    }

    /**
     * @return Response
     */
    public function topModuleBar()
    {
        $mappingRepository = $this->getRepository(Mapping::class);
        $mappings = $mappingRepository->findBy(
            ['navigation' => NavigationRepository::TOP_MODULE_BAR_ID],
            ['ordering' => 'ASC']
        );

        return $this->render('cms/navigation/top_module_bar.html.twig', [
            'mappings' => $mappings
        ]);
    }

    /**
     * @return Response
     */
    public function sideModuleBar()
    {
        // Access restricted to ROLE_BACKEND_ADMIN
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_BACKEND_ADMIN')) {
            return new Response();
        }

        $mappingRepository = $this->getRepository(Mapping::class);
        $mappings = $mappingRepository->findBy(
            ['navigation' => NavigationRepository::SIDE_MODULE_BAR_ID],
            ['ordering' => 'ASC']
        );

        return $this->render('cms/navigation/side_module_bar.html.twig', [
            'mappings' => $mappings
        ]);
    }

    /**
     * @param Breadcrumbs $breadcrumbs
     * @return Response
     */
    public function breadcrumbs(Breadcrumbs $breadcrumbs)
    {
        $elementCurrent = $this->getApplicationCore()->getCurrentElement();
        $elements = $breadcrumbs->getElements();

        return $this->render('cms/navigation/breadcrumbs.html.twig', [
            'elements' => $elements,
            'elementCurrent' => $elementCurrent
        ]);
    }

    /**
     * @return Response
     */
    public function pageTitle()
    {
        $elements = $this->getApplicationCore()->getPageTitle()->getElements();

        return $this->render('globals/page_title.html.twig', [
            'elements' => $elements
        ]);
    }

    /**
     * @return Response
     */
    public function sectionModuleBar()
    {
        if (false == $this->getSection()) {
            return new Response();
        }

        $mappingRepository = $this->getRepository(Mapping::class);

        $mappings = $mappingRepository->findBy(
            [
                'section' => $this->getSection(),
                'navigation' => NavigationRepository::SECTION_MODULE_BAR_ID
            ],
            ['ordering' => 'ASC']
        );

        return $this->render('cms/navigation/section_module_bar.html.twig', [
            'mappings' => $mappings
        ]);
    }

    /**
     * @return Response
     */
    public function localeBar()
    {
        $localeRepo = $this->getRepository(Locale::class);
        $locales = $localeRepo->findBy([], ['ordering' => 'ASC']);

        return $this->render('cms/navigation/locale_bar.html.twig', [
            'locales' => $locales,
            'editLocale' => $this->getApplicationCore()->getEditLocale()
        ]);
    }
}
