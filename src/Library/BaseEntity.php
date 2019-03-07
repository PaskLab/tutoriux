<?php

namespace App\Library;

use App\Library\Traits\NavigationElementTrait;

/**
 * Class BaseEntity
 * @package App\Library
 */
abstract class BaseEntity implements EntityInterface, NavigationElementInterface
{
    use NavigationElementTrait;

    /**
     * Returns the entity name without its path
     *
     * @return string
     */
    public function getEntityName()
    {
        $className = get_class($this);
        $classNameTokens = explode('\\', $className);

        return array_pop($classNameTokens);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return false;
    }

    /**
     * Returns true if editable
     *
     * @return bool
     */
    public function isEditable()
    {
        return true;
    }

    /**
     * Returns true if deletable
     *
     * @return bool
     */
    public function isDeletable()
    {
        if (!$this->getId()) {
            return false;
        }

        if (method_exists($this, 'getDeleteRestrictions')) {
            foreach ($this->getDeleteRestrictions() as $method) {

                $result = $this->$method();

                if ((is_bool($result) && $result == true) || (!is_bool($result) && count($result))) {
                    return false;
                }
            }
        }

        return true;
    }
}