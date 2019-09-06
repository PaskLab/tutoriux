<?php

namespace App\Form\Globals\Type\ChoiceList;

use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;
use App\Library\Component\TreeEntitySorter;

/**
 * Class ORMSortedQueryBuilderLoader
 * @package App\Form\Component\Type\ChoiceList
 */
class ORMSortedQueryBuilderLoader extends ORMQueryBuilderLoader
{
    /**
     * Contains the query builder that builds the query for fetching the
     * entities
     *
     * This property should only be accessed through queryBuilder.
     *
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var TreeEntitySorter
     */
    protected $treeEntitySorter;

    /**
     * @var bool
     */
    protected $automaticSorting;

    /**
     * ORMSortedQueryBuilderLoader constructor.
     * @param $queryBuilder
     * @param TreeEntitySorter $treeEntitySorter
     * @param ObjectManager|null $manager
     * @param null $class
     * @param null $automaticSorting
     */
    public function __construct($queryBuilder, TreeEntitySorter $treeEntitySorter, ObjectManager $manager = null, $class = null, $automaticSorting = null)
    {
        // If a query builder was passed, it must be a closure or QueryBuilder instance
        if (!($queryBuilder instanceof QueryBuilder || $queryBuilder instanceof \Closure)) {
            throw new UnexpectedTypeException($queryBuilder, 'Doctrine\ORM\QueryBuilder or \Closure');
        }

        if ($queryBuilder instanceof \Closure) {
            $queryBuilder = $queryBuilder($manager->getRepository($class));

            if (!$queryBuilder instanceof QueryBuilder) {
                throw new UnexpectedTypeException($queryBuilder, 'Doctrine\ORM\QueryBuilder');
            }
        }

        $this->queryBuilder = $queryBuilder;

        $this->treeEntitySorter = $treeEntitySorter;
        $this->automaticSorting = $automaticSorting;

        parent::__construct($queryBuilder);
    }

    /**
     * {@inheritDoc}
     *
     * This function overwrites the original function to add the "automaticSorting" functionality
     */
    public function getEntities()
    {
        $entities = $this->queryBuilder->getQuery()->execute();

        // If automaticSorting is On
        if ($this->automaticSorting) {
            // Sort the entities
            $entities = $this->treeEntitySorter->sortEntities($entities);
        }

        return $entities;
    }

}
