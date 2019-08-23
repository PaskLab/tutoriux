<?php

namespace App\Services;

/**
 * Class DeletableCallable
 * @package App\Services
 */
class DeletableCallable
{
    /**
     * @var Deletable
     */
    private $deletable;

    /**
     * DeletableCallable constructor.
     * @param Deletable $deletable
     */
    public function __construct(Deletable $deletable)
    {
        $this->deletable = $deletable;
    }

    /**
     * @return Deletable
     */
    public function __invoke()
    {
        return $this->deletable;
    }
}