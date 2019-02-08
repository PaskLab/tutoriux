<?php

namespace App\Library;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;


/**
 * Class BaseEntityRepository
 * @package App\Library
 */
abstract class BaseEntityRepository extends EntityRepository
{
    /**
     * @var bool $returnQueryBuilder
     */
    private $returnQueryBuilder = false;

    /**
     * Define if method processQuery should return a QueryBuilder instance or query result
     *
     * @param bool $returnQueryBuilder
     * @return $this
     */
    public function setReturnQueryBuilder(bool $returnQueryBuilder)
    {
        $this->returnQueryBuilder = $returnQueryBuilder;

        return $this;
    }

    /**
     * Define if method processQuery should return a QueryBuilder instance or query result
     *
     * @return bool
     */
    public function getReturnQueryBuilder(): bool
    {
        return $this->returnQueryBuilder;
    }

    /**
     * Returns the Query Builder or the results depending on the repository parameters
     *
     * @param QueryBuilder $queryBuilder
     * @param bool $singleResult
     * @return QueryBuilder|mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function processQuery(QueryBuilder $queryBuilder, $singleResult = false)
    {
        if ($this->returnQueryBuilder) {
            return $queryBuilder;
        }

        if ($singleResult) {
            return $queryBuilder->getQuery()->getOneOrNullResult();
        }

        return $queryBuilder->getQuery()->getResult();
    }
}