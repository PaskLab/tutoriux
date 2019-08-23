<?php

namespace App\Controller\Site;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Navigation;
use App\Entity\Section;
use App\Entity\SectionNavigation;
use App\Repository\NavigationRepository;
use App\Repository\SectionNavigationRepository;
use App\Library\BaseController;
use App\Repository\SectionRepository;
use App\Services\NavigationBuilder;
use App\Services\LocaleSwitcher;
use Tutoriux\DoctrineBehaviorsBundle\ORM\Metadatable\MetadatableGetter;

/**
 * Class NavigationController
 * @package App\Controller\Site
 */
class NavigationController extends BaseController
{
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
    public function byCode(Request $request, NavigationBuilder $navigationBuilder, $code, $maxLevel = 10, $exploded = false, $template = '', $attr = array())
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

        $sections = $sectionRepository->findByNavigation($navigation->getId());

        $template = ($template ? '_' . $template : '');

        $navigationBuilder->setElements($sections);
        $navigationBuilder->setSelectedElement($this->getSection());
        $elements = $navigationBuilder->build();

        return $this->render('site/navigation/by_code' . $template . '.html.twig',
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
    public function fromSection(Request $request, NavigationBuilder $navigationBuilder, $section, $maxLevel = 10, $exploded = false, $template = '', $attr = [])
    {
        /** @var SectionRepository $sectionRepository */
        $sectionRepository = $this->getRepository(Section::class);

        // Cache
        $response = new Response();
        $response->setPublic();

        $response->setEtag($sectionRepository->findLastUpdate());

        if ($response->isNotModified($request)) {
            return $response;
        }

        // Rebuild the cache
        if (is_numeric($section)) {
            $section = $sectionRepository->find($section);
        }

        $elements = [];

        if ($parents = $section->getParents()) {
            $elements = $parents[1]->getChildren();
        } elseif (count($section->getChildren())) {
            $elements = $section->getChildren();
        }

        $template = ($template ? '_' . $template : '');

        $navigationBuilder->setElements($elements);
        $navigationBuilder->setSelectedElement($this->getSection());
        $elements = $navigationBuilder->build();

        return $this->render('site/navigation/from_section' . $template . '.html.twig',
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
     * @return Response
     */
    public function breadcrumbs()
    {
        $breadcrumbs = $this->getApplicationCore()->getBreadcrumbs();
        $elementCurrent = $this->getApplicationCore()->getCurrentElement();
        $elements = $breadcrumbs->getElements();

        return $this->render('site/navigation/breadcrumbs.html.twig',
            [
                'elements' => $elements,
                'elementCurrent' => $elementCurrent
            ]
        );
    }

    /**
     * @param MetadatableGetter $metadatableGetter
     * @return Response
     */
    public function pageTitle(MetadatableGetter $metadatableGetter)
    {
        $pageTitle = $this->getApplicationCore()->getPageTitle();

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

        return $this->render('globals/page_title.html.twig',
            [
                'element_page_title' => $elementPageTitle,
                'element_override_page_title' => $elementOverridePageTitle,
                'elements' => $elements
            ]
        );
    }

    /**
     * @param LocaleSwitcher $localeSwitcher
     * @return Response
     * @throws \ReflectionException
     */
    public function localeSwitcher(LocaleSwitcher $localeSwitcher)
    {
        $localeSwitcher->setElement($this->getApplicationCore()->getCurrentElement());

        $routes = $localeSwitcher->generate();

        return $this->render('site/navigation/locale_switcher.html.twig', ['routes' => $routes]);
    }

}
