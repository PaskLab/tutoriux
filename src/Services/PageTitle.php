<?php

namespace App\Services;

/**
 * Class PageTitle
 * @package App\Services
 */
class PageTitle
{
    /**
     * @var array
     */
    private $elements;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->elements = [];
    }

    /**
     * @param $element
     */
    public function addElement($element)
    {
        $this->elements[] = $element;
    }

    /**
     * @param $element
     */
    public function removeElement($element)
    {
        foreach ($this->elements as $k => $existingElement) {
            if ($element == $existingElement) {
                unset($this->elements[$k]);
                break;
            }
        }
    }

    /**
     * @return array
     */
    public function getElements()
    {
        $elements = array_reverse($this->elements, true);

        return $elements;
    }
}
