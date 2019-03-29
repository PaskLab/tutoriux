<?php

namespace App\Services;

use App\Entity\Locale;
use App\Library\NavigationElementInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Cache\Adapter\TraceableAdapter;

/**
 * Class LocaleSwitcher
 * @package SystemBundle\Services
 */
class LocaleSwitcher
{
    const FALLBACK_CACHE_EXPIRE = 86400;

    /**
     * @var NavigationElementInterface
     */
    protected $element;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var TraceableAdapter
     */
    protected $cache;

    /**
     * LocaleSwitcher constructor.
     * @param RequestStack $requestStack
     * @param RouterInterface $router
     * @param RegistryInterface $doctrine
     * @param LoggerInterface $logger
     */
    public function __construct(RequestStack $requestStack, RouterInterface $router,
                                RegistryInterface $doctrine, LoggerInterface $logger)
    {
        $this->request = $requestStack->getMasterRequest();
        $this->router = $router;
        $this->doctrine = $doctrine;
        $this->logger = $logger;
    }

    /**
     * @return array
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function generate()
    {
        $locales = $this->doctrine->getRepository(Locale::class)
            ->findAllActive(43200);

        $routes = $this->router->getRouteCollection();
        $currentLocale = $this->request->getLocale();
        $localizedUrls = [];
        $currentRoute = $this->request->get('_route');
        $currentParams = $this->request->request->all();
        $currentQuery = $this->request->query->all();

        /** @var Locale $locale */
        foreach ($locales as $locale) {

            if ($currentLocale == $locale->getCode()) {
                continue;
            }

            $i18nRouteName = $currentRoute.'.'.$locale->getCode();

            if ($routes->get($i18nRouteName)) {
                // Generate
                $url = $this->router->generate($i18nRouteName, array_merge($currentParams, $currentQuery));
            } elseif (!$url = $this->fallBack($currentRoute, $locale)) {
                $this->logger->info(
                    sprintf('Impossible to create %s fallback for route %s.', $locale->getName(), $i18nRouteName),
                    ['Context' => 'LocaleSwitcher.php']
                );
                continue;
            }

            $localizedUrls[$locale->getCode()] = [
                'locale' => $locale,
                'url' => $url
            ];
        }

        return $localizedUrls;
    }

    /**
     * @param string $routeName
     * @param Locale $locale
     * @return bool|mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function fallBack(string $routeName, Locale $locale)
    {
        if (!$this->element) {
            return false;
        }

        $cacheId = sprintf('localeswitcher_%s_%s_fallback', $routeName, $locale->getCode());
        $cachedUrl = $this->cache->getItem($cacheId);

        if (!$cachedUrl->isHit()) {
            $url = false;

            while ($parent = $this->element->getParentElement()) {

                try {
                    $url = $this->router->generate(
                        $parent->getRoute(),
                        $parent->getRouteParams(['_locale' => $locale->getCode()])
                    );
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage(), ['Context' => 'LocaleSwitcher.php']);
                    continue;
                }

                break;
            }

            $cachedUrl->set($url);
            $cachedUrl->expiresAfter(self::FALLBACK_CACHE_EXPIRE);
            $this->cache->save($cachedUrl);
        }

        return $cachedUrl->get();
    }

    /**
     * @param NavigationElementInterface $element
     * @return LocaleSwitcher
     */
    public function setElement(NavigationElementInterface $element): LocaleSwitcher
    {
        $this->element = $element;
        return $this;
    }

    /**
     * @param TraceableAdapter $cacheAdapter
     * @return LocaleSwitcher
     */
    public function setCacheAdapter(TraceableAdapter $cacheAdapter): LocaleSwitcher
    {
        $this->cache = $cacheAdapter;
        return $this;
    }
}
