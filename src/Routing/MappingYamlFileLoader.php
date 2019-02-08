<?php

namespace App\Routing;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Yaml\Yaml;
use Doctrine\DBAL\Connection;
use Symfony\Component\Config\FileLocatorInterface;


/**
 * MappingYamlFileLoader load route mapped to section entities.
 *
 * Class MappingYamlFileLoader
 * @package App\Routing
 */
class MappingYamlFileLoader extends FileLoader
{
    private const DEFAULT_ROUTE = 'app_site_text';

    /**
     * @var array
     */
    private static $availableKeys = [
        'path', 'host', 'schemes', 'methods', 'defaults', 'requirements', 'options', 'condition', 'controller'
    ];

    /**
     * @var YamlParser
     */
    private $yamlParser;

    /**
     * @var Connection
     */
    private $databaseConnection;

    /**
     * @var array
     */
    private $targetIndexedMappings;

    /**
     * @var array
     */
    private $sectionIdIndexedMappings;

    /**
     * @var array
     */
    private $parentIdIndexedMappings;

    /**
     * MappingYamlFileLoader constructor.
     * @param FileLocatorInterface $locator
     * @param Connection $databaseConnection
     */
    public function __construct(FileLocatorInterface $locator, Connection $databaseConnection)
    {
        $this->locator = $locator;
        $this->databaseConnection = $databaseConnection;

        $mappings = $this->databaseConnection->fetchAll($this->getMappingSqlQuery());

        // Index mappings to avoid looping over all entries
        $this->targetIndexedMappings = $this->indexMappingEntries($mappings, 'target');
        $this->sectionIdIndexedMappings = $this->indexMappingEntries($mappings, 'section_id');
        $this->parentIdIndexedMappings = $this->indexMappingEntries($mappings, 'parent_id');

        return parent::__construct($locator);
    }

    /**
     * Loads a Yaml file.
     *
     * @param string      $file A Yaml file path
     * @param string|null $type The resource type
     *
     * @return RouteCollection A RouteCollection instance
     *
     * @throws \InvalidArgumentException When a route can't be parsed because YAML is invalid
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);

        if (!stream_is_local($path)) {
            throw new \InvalidArgumentException(sprintf('This is not a local file "%s".', $path));
        }

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('File "%s" not found.', $path));
        }

        if (null === $this->yamlParser) {
            $this->yamlParser = new YamlParser();
        }

        try {
            $parsedConfig = $this->yamlParser->parseFile($path, Yaml::PARSE_CONSTANT);
        } catch (ParseException $e) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not contain valid YAML.', $path), 0, $e);
        }

        $collection = new RouteCollection();
        $collection->addResource(new FileResource($path));

        // empty file
        if (null === $parsedConfig) {
            return $collection;
        }

        // not an array
        if (!\is_array($parsedConfig)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $path));
        }

        foreach ($parsedConfig as $name => $config) {
            $this->validate($config, $name, $path);
            $this->parseRoute($collection, $name, $config, $path);
        }

        return $collection;
    }

    /**
     * @param mixed $resource
     * @param null $type
     * @return bool
     */
    public function supports($resource, $type = null)
    {
        // only when type is forced to 'mapping', not to conflict with other loaders
        return 'mapping' === $type;
    }

    /**
     * Validates the route configuration.
     *
     * @param array  $config A resource config
     * @param string $name   The config key
     * @param string $path   The loaded file path
     *
     * @throws \InvalidArgumentException If one of the provided config keys is not supported,
     *                                   something is missing or the combination is nonsense
     */
    private function validate($config, $name, $path)
    {
        if (!\is_array($config)) {
            throw new \InvalidArgumentException(sprintf('The definition of "%s" in "%s" must be a YAML array.', $name, $path));
        }
        if ($extraKeys = array_diff(array_keys($config), self::$availableKeys)) {
            throw new \InvalidArgumentException(sprintf('The routing file "%s" contains unsupported keys for "%s": "%s". Expected one of: "%s".', $path, $name, implode('", "', $extraKeys), implode('", "', self::$availableKeys)));
        }
        if (isset($config['controller']) && isset($config['defaults']['_controller'])) {
            throw new \InvalidArgumentException(sprintf('The routing file "%s" must not specify both the "controller" key and the defaults key "_controller" for "%s".', $path, $name));
        }
    }

    /**
     * Parses a route and adds it to the RouteCollection.
     *
     * @param RouteCollection $collection A RouteCollection instance
     * @param string          $name       Route name
     * @param array           $config     Route definition
     * @param string          $path       Full path of the YAML file being processed
     */
    private function parseRoute(RouteCollection $collection, $name, array $config, $path)
    {
        $defaults = isset($config['defaults']) ? $config['defaults'] : array();
        $requirements = isset($config['requirements']) ? $config['requirements'] : array();
        $options = isset($config['options']) ? $config['options'] : array();
        $host = isset($config['host']) ? $config['host'] : '';
        $schemes = isset($config['schemes']) ? $config['schemes'] : array();
        $methods = isset($config['methods']) ? $config['methods'] : array();
        $condition = isset($config['condition']) ? $config['condition'] : null;

        foreach ($requirements as $placeholder => $requirement) {
            if (\is_int($placeholder)) {
                @trigger_error(sprintf('A placeholder name must be a string (%d given). Did you forget to specify the placeholder key for the requirement "%s" of route "%s" in "%s"?', $placeholder, $requirement, $name, $path), E_USER_DEPRECATED);
            }
        }

        if (isset($config['controller'])) {
            $defaults['_controller'] = $config['controller'];
        }

        if (\is_array($config['path'])) {
            throw new \InvalidArgumentException('You cannot import mapped routing with localized prefix.');
        }

        $route = new Route($config['path'], $defaults, $requirements, $options, $host, $schemes, $methods, $condition);

        if (isset($this->targetIndexedMappings[$name])) {
            foreach ($this->targetIndexedMappings[$name] as $mapping) {
                $this->generate($mapping, $route, $collection);
            }
        }
    }

    /**
     * @param array $mapping
     * @param Route $route
     * @param RouteCollection $collection
     * @param string $name
     * @return bool
     */
    private function generate(array $mapping, Route $route, RouteCollection $collection, $name = '')
    {
        $sourceName = $mapping['target'];

        // Validate parents hierarchy
        if (false == $this->validateParents($mapping['section_id'], $mapping['locale'])) {
            return false;
        }

        // No route name specified, fallback to the auto generated one
        if (false == $name) {
            $name = 'section_id_' . $mapping['section_id'];
            if ($alias = $route->getOption('mapping_alias')) {
                $name .= '_' . $alias;
            }
            $name .= '.' . $mapping['locale'];
        }

        // If the section is mapped to the default text controller and does not have any active text,
        // use the first child as the generation starting point
        if (false == $mapping['has_text'] && $mapping['has_children'] && $mapping['target'] == self::DEFAULT_ROUTE) {
            if ($firstChildMapping = $this->findFirstChild($mapping['section_id'], $mapping['locale'])) {
                return $this->generate($firstChildMapping, $route, $collection, $name);
            }
        }

        // Compute expanded path only if required
        $sectionsPath = null;
        if (preg_match('/{sectionsPath}/', $route->getPath())) {
            // expanding section paths
            $sectionsPath = $this->computeParentSlugs($mapping['parent_id'], $mapping['locale']) . '/' . $mapping['slug'];
            // remove first slash
            $sectionsPath = substr($sectionsPath, 1);

            $expandedPath = preg_replace('/{(sectionsPath)}/', $sectionsPath, $route->getPath());

            $route->setPath($expandedPath);
        }

        // add additional parameters
        $route
            ->addDefaults([
            '_tutoriuxContext' => $mapping['context'],
            '_tutoriuxEnabled' => true,
            '_tutoriuxRequest' => [
                'sectionId' => $mapping['section_id'],
                'sectionSlug' => $mapping['slug'],
                'sectionsPath' => $sectionsPath
            ],
            '_locale' => $mapping['locale'],
            '_canonical_route' => sprintf('%s [%s]', $name, $sourceName)
        ]);

        // adding the route to the main collection
        $collection->add($name, $route);

        return true;
    }

    /**
     * Find a mapping by sectionId.
     *
     * @param $sectionId
     * @param $locale
     *
     * @return array|bool
     */
    private function find($sectionId, $locale)
    {
        if (isset($this->sectionIdIndexedMappings[$sectionId])) {
            foreach ($this->sectionIdIndexedMappings[$sectionId] as $mapping) {
                if ($locale === $mapping['locale']) {
                    return $mapping;
                }
            }
        }

        return false;
    }

    /**
     * Validate that each parents from sectionId is present in the hierarchy, if a parent is not
     * present this mean that the section is not active or not mapped to anything.
     *
     * @param $sectionId
     * @param $locale
     *
     * @return bool
     */
    private function validateParents($sectionId, $locale) : bool
    {
        $mapping = $this->find($sectionId, $locale);

        while ($mapping['parent_id']) {
            if (!$mapping = $this->find($mapping['parent_id'], $locale)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Find the first child of a given section using the mapping array.
     *
     * @param $sectionId
     * @param $locale
     *
     * @return array|bool
     */
    private function findFirstChild($sectionId, $locale)
    {
        if (isset($this->parentIdIndexedMappings[$sectionId])) {
            foreach ($this->parentIdIndexedMappings[$sectionId] as $mapping) {
                if ($locale === $mapping['locale']) {
                    return $mapping;
                }
            }
        }

        return false;
    }

    /**
     * This compute the full slug-path of a given section using the mapping array.
     *
     * Ex: "/our-company/mission/staff"
     *
     * @param $sectionId
     * @param $locale
     *
     * @return string
     */
    private function computeParentSlugs($sectionId, $locale)
    {
        if (isset($this->sectionIdIndexedMappings[$sectionId])) {
            foreach ($this->sectionIdIndexedMappings[$sectionId] as $mapping) {
                if ($locale === $mapping['locale']) {
                    $slug = (false === $mapping['remove_from_url']) ? ('/' . $mapping['slug']) : '';
                    if ($mapping['parent_id']) {
                        return $this->computeParentSlugs($mapping['parent_id'], $locale) . $slug;
                    }

                    return $slug;
                }
            }
        }

        return '';
    }

    /**
     * @param array $mappings
     * @return array
     */
    private function indexMappingEntries(array $mappings, string $indexBy)
    {
        $indexedMapping = [];

        foreach ($mappings as $mapping) {
            $indexedMapping[$mapping[$indexBy]][] = $mapping;
        }

        return $indexedMapping;
    }

    /**
     * The main query used to fetch the mappings.
     * This query is in raw SQL for performance purpose.
     *
     * @return string
     */
    private function getMappingSqlQuery() : string
    {
        return '
            SELECT m.target, m.context, s.id as section_id, s.parent_id, remove_from_url, st.locale, st.slug, (
                SELECT COUNT(t.id) FROM text t
                INNER JOIN text_translation tt ON tt.translatable_id = t.id
                WHERE t.section_id = s.id
                AND tt.active = TRUE
                AND tt.locale = st.locale
            ) AS has_text, (
                SELECT COUNT(ss.id)
                FROM section ss
                INNER JOIN section_translation sst ON sst.translatable_id = ss.id
                WHERE ss.parent_id = s.id
                AND sst.active = TRUE
                AND sst.locale = st.locale
            ) AS has_children
            FROM mapping m
            INNER JOIN section s ON s.id = m.section_id
            INNER JOIN section_translation st ON st.translatable_id = s.id
            WHERE m.type = \'route\'
            AND st.active = TRUE
            ORDER BY s.parent_id, s.ordering, m.ordering
        ';
    }
}
