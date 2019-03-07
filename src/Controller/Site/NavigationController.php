<?php

namespace App\Controller\Site;

use App\Entity\Navigation;
use App\Entity\Section;
use App\Entity\SectionNavigation;
use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request;

use App\Repository\NavigationRepository,
    App\Repository\SectionNavigationRepository,
    App\Library\BaseController,
    App\Repository\MappingRepository,
    App\Repository\SectionRepository,
    App\Services\NavigationBuilder,
    App\Services\PageTitle,
    App\Services\LocaleSwitcher,
    App\Services\DoctrineInit,
    App\Services\Breadcrumbs;

use Tutoriux\DoctrineBehaviorsBundle\ORM\Metadatable\MetadatableGetter;

/**
 * Class NavigationController
 * @package App\Controller\Site
 */
class NavigationController extends BaseController
{
//    /**
//     * @var SectionRepository
//     */
//    protected $sectionRepository;
//
//    /**
//     * @var MappingRepository
//     */
//    protected $mappingRepository;
//
//    /**
//     * @var NavigationRepository
//     */
//    protected $navigationRepository;
//
//    /**
//     * @var SectionNavigationRepository
//     */
//    protected $sectionNavigationRepository;

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    public function init()
    {
        $this->sectionRepository = $this->container->get(DoctrineInit::class)->initRepository(
            $this->getEm()->getRepository('SystemBundle:Section')
        );
        $this->sectionNavigationRepository = $this->getEm()->getRepository('SystemBundle:SectionNavigation');
        $this->mappingRepository = $this->getEm()->getRepository('SystemBundle:Mapping');
        $this->navigationRepository = $this->getEm()->getRepository('SystemBundle:Navigation');
    }

    /**
     * @param Request $request
     * @param NavigationBuilder $navigationBuilder
     * @param $code
     * @param int $maxLevel
     * @param bool $exploded
     * @param string $template
     * @param array $attr
     * @return Response
     * @throws \Exception
     */
    public function byCodeAction(Request $request, NavigationBuilder $navigationBuilder, $code, $maxLevel = 10, $exploded = false, $template = '', $attr = array())
    {
        /** @var SectionRepository $sectionRepository */
        $sectionRepository = $this->getRepository(Section::class);
        /** @var SectionNavigationRepository $sectionNavigationRepository */
        $sectionNavigationRepository = $this->getRepository(SectionNavigation::class);

        // Cache
        $response = new Response();
        $response->setPublic();

        $sectionLastUpdate = $sectionRepository->findLastUpdate();
        $sectionNavigationLastUpdate = $sectionNavigationRepository->findLastUpdate();

        $response->setEtag($sectionLastUpdate . $sectionNavigationLastUpdate);

        if ($response->isNotModified($request)) {
            return $response;
        }

        /** @var NavigationRepository $navigationRepository */
        $navigationRepository = $this->getRepository(Navigation::class);
        // Rebuild the cache
        $navigation = $navigationRepository->findOneByCode($code);

        if (false == $navigation) {
            throw new \Exception('Can\'t find a navigation entity using code "' . $code . '"');
        }

        $sections = $sectionRepository->findByNavigationAndApp($navigation->getId(), 2);

        $template = ($template ? '_' . $template : '');

        $navigationBuilder->setElements($sections);
        $navigationBuilder->setSelectedElement($this->getSection());
        $elements = $navigationBuilder->build();

        return $this->render(
            'site/navigation/by_code_' . $template . '.html.twig',
            [
                'code' => $code,
                'sections' => $elements,
                'maxLevel' => $maxLevel,
                'currentSection' => $this->getSection(),
                'attr' => $attr,
                'exploded' => $exploded
            ],
            $response
        );
    }

    /**
     * Render a navigation displaying children starting from a section
     *
     * @param Request $request
     * @param NavigationBuilder $navigationBuilder
     * @param $section
     * @param int $maxLevel
     * @param bool $exploded
     * @param string $template
     * @param array $attr
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     */
    public function fromSectionAction(Request $request, NavigationBuilder $navigationBuilder, $section, $maxLevel = 10, $exploded = false, $template = '', $attr = [])
    {
        // Cache
        $response = new Response();
        $response->setPublic();

        $response->setEtag($this->sectionRepository->findLastUpdate());

        if ($response->isNotModified($request)) {
            return $response;
        }

        // Rebuild the cache
        if (is_numeric($section)) {
            $section = $this->sectionRepository->find($section);
        }

        $elements = [];

        if ($parents = $section->getParents()) {
            $elements = $parents[1]->getChildren();
        } elseif (count($section->getChildren())) {
            $elements = $section->getChildren();
        }

        $template = ($template ? '_' . $template : '');

        $navigationBuilder->setElements($elements, true);
        $navigationBuilder->setSelectedElement($this->getCore()->getSection());
        $navigationBuilder->build();

        $elements = $navigationBuilder->getElements();

        return $this->render(
            'SystemBundle:Frontend/Navigation:from_section' . $template . '.html.twig',
            array(
                'sections' => $elements,
                'maxLevel' => $maxLevel,
                'currentSection' => $this->getSection(),
                'attr' => $attr,
                'exploded' => $exploded
            ),
            $response
        );
    }

    /**
     * @param Breadcrumbs $breadcrumbs
     * @return Response
     */
    public function breadcrumbsAction(Breadcrumbs $breadcrumbs)
    {
        $elementCurrent = $this->getCore()->getElement();
        $elements = $breadcrumbs->getElements();

        return $this->render(
            'SystemBundle:Frontend/Navigation:breadcrumbs.html.twig',
            array(
                'elements' => $elements,
                'elementCurrent' => $elementCurrent
            )
        );
    }

    /**
     * @param PageTitle $pageTitle
     * @param MetadatableGetter $metadatableGetter
     * @return Response
     */
    public function pageTitle(PageTitle $pageTitle, MetadatableGetter $metadatableGetter)
    {
        $elements = $pageTitle->getElements();

        $elementPageTitle = null;
        $elementOverridePageTitle = null;

        if (count($elements)) {
            $elements = array_values($elements);
            $currentElement = $elements[0];
            $elementPageTitle = $metadatableGetter->getMetadata($currentElement, 'title');
            $elementOverridePageTitle = $metadatableGetter->getMetadata($currentElement, 'titleOverride');

            if ($elementPageTitle || $elementOverridePageTitle) {
                unset($elements[0]);
            }
        }

        return $this->render(
            'global/page_title.html.twig',
            array(
                'element_page_title' => $elementPageTitle,
                'element_override_page_title' => $elementOverridePageTitle,
                'elements' => $elements
            )
        );
    }

    /**
     * @param LocaleSwitcher $localeSwitcher
     * @return Response
     */
//    public function localeSwitcherAction(LocaleSwitcher $localeSwitcher)
//    {
//        $localeSwitcher->setElement($this->getCore()->getElement());
//
//        $routes = $localeSwitcher->generate();
//
//        return $this->render(
//            'SystemBundle:Frontend/Navigation:locale_switcher.html.twig',
//            array(
//                'routes' => $routes,
//            )
//        );
//    }

}
