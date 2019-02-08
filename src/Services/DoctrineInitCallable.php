<?php

namespace App\Services;

/**
 * Class DoctrineInitCallable
 *
 * @package \App\Services
 */
class DoctrineInitCallable
{
    /**
     * @var DoctrineInit
     */
    private $doctrineInit;

    /**
     * DoctrineInitCallable constructor.
     * @param DoctrineInit $doctrineInit
     */
    public function __construct(DoctrineInit $doctrineInit)
    {
        $this->doctrineInit = $doctrineInit;
    }

    /**
     * @return DoctrineInit
     */
    public function __invoke()
    {
        return $this->doctrineInit;
    }
}