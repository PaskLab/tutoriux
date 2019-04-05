<?php

namespace App\Library;

use Algolia\AlgoliaSearchBundle\Exception\UnknownEntity,
    Algolia\AlgoliaSearchBundle\Indexer\Indexer as AlgoliaIndexer;

use Doctrine\ORM\EntityManager;

/**
 * Class Indexer
 * @package App\Library
 */
class Indexer extends AlgoliaIndexer
{
    /**
     * @var string
     */
    private $indexPrefix;

    /**
     * @var string
     */
    private $env;

    /**
     * Indexer constructor.
     */
    public function __construct()
    {
        return parent::__construct();
    }

    /**
     * Override to intercept index name prefix
     * @param mixed $indexNamePrefix
     */
    public function setIndexNamePrefix($indexNamePrefix)
    {
        $this->indexPrefix = $indexNamePrefix;
        parent::setIndexNamePrefix($indexNamePrefix);
    }

    /**
     * Override to intercept environment
     * Used by the depency injection mechanism of Symfony
     */
    public function setEnvironment($environment)
    {
        $this->env = $environment;

        return parent::setEnvironment($environment);
    }

    /**
     * Override to add support for translation
     *
     * @param $entity_or_class
     * @return string
     * @throws UnknownEntity
     * @throws \Exception
     */
    public function getAlgoliaIndexName($entity_or_class)
    {
        if (is_object($entity_or_class)) {
            $traitNames = class_uses($entity_or_class);
            if (!in_array('DoctrineBehaviorsBundle\Model\Translatable\Translation', $traitNames)) {
                return parent::getAlgoliaIndexName($entity_or_class);
            }
        } else {
            return parent::getAlgoliaIndexName($entity_or_class);
        }

        $indexSettings = $this->getIndexSettings();

        $class = $this->get_class($entity_or_class);

        if (!isset($indexSettings[$class])) {
            throw new UnknownEntity("Entity $class is not known to Algolia. This is likely an implementation bug.");
        }

        $index = $indexSettings[$class]->getIndex();
        $indexName = $index->getAlgoliaName();

        if (!empty($this->indexPrefix)) {
            $indexName = $this->indexPrefix . '_' . $indexName;
        }

        if (method_exists($entity_or_class, 'getLocale')) {
            $indexName .= '_'.$entity_or_class->getLocale();
        } else {
            throw new \Exception('Translation entity should have a getLocale method.');
        }

        if ($index->getPerEnvironment() && $this->env) {
            $indexName .= '_'.$this->env;
        }

        return $indexName;
    }

    /**
     * @param $entity
     * @return mixed|string
     */
    private function get_class($entity)
    {
        $class = get_class($entity);

        $class = $this->removeProxy($class);

        return $class;
    }

    /**
     * @param $class
     * @return mixed
     */
    private function removeProxy($class)
    {
        /* Avoid proxy class form symfony */
        return str_replace("Proxies\\__CG__\\", "", $class);
    }

    /**
     * @param EntityManager $em
     * @return ManualIndexer
     */
    public function getManualIndexer(EntityManager $em)
    {
        return new ManualIndexer($this, $em);
    }
}