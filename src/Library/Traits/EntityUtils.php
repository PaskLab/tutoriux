<?php

namespace App\Library\Traits;

/**
 * Trait EntityUtils
 * @package App\Library\Traits
 */
trait EntityUtils
{
    /**
     * Returns the entity name without its path
     *
     * @return string
     */
    public function getEntityName(): string
    {
        $className = get_class($this);
        $classNameTokens = explode('\\', $className);

        return array_pop($classNameTokens);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return false;
    }

    /**
     * Returns true if editable
     *
     * @return bool
     */
    public function isEditable(): bool
    {
        return true;
    }

    /**
     * Returns true if deletable
     *
     * @return bool
     */
    public function isDeletable(): bool
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