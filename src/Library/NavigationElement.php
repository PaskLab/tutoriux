<?php

namespace App\Library;

use App\Library\Traits\NavigationElementTrait;

/**
 * Class NavigationElement
 *
 * @package \App\Library
 */
class NavigationElement implements NavigationElementInterface
{
    use NavigationElementTrait;

    /**
     * @var
     */
    protected $id;

    /**
     * NavigationElement constructor.
     */
    public function __construct()
    {
        $this->id = uniqid();
    }

    /**
     * @return mixed|string
     */
    public function getId()
    {
        return $this->id;
    }
}