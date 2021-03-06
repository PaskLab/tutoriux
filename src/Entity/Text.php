<?php

namespace App\Entity;

use App\Library\EntityInterface;
use App\Library\Traits\EntityUtils;
use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;
use Tutoriux\DoctrineBehaviorsBundle\Model\Translatable\TranslatableInterface;

/**
 * Class Text
 * @package App\Entity
 */
class Text implements EntityInterface, TranslatableInterface
{
    use EntityUtils,
        TutoriuxORMBehaviors\Translatable\Translatable,
        TutoriuxORMBehaviors\Timestampable\Timestampable,
        TutoriuxORMBehaviors\Localizable\Localizable;

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var boolean $collapsible
     */
    private $collapsible;

    /**
     * @var boolean $static
     */
    private $static = false;

    /**
     * @var integer $ordering
     */
    private $ordering;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set collapsible
     *
     * @param boolean $collapsible The collapsible state
     */
    public function setCollapsible($collapsible)
    {
        $this->collapsible = $collapsible;
    }

    /**
     * Get collapsible
     *
     * @return boolean
     */
    public function getCollapsible()
    {
        return $this->collapsible;
    }

    /**
     * Set static
     *
     * @param boolean $static Static state
     */
    public function setStatic($static)
    {
        $this->static = $static;
    }

    /**
     * Get static
     *
     * @return boolean
     */
    public function getStatic()
    {
        return $this->static;
    }

    /**
     * Is static
     *
     * @return boolean
     */
    public function isStatic()
    {
        return $this->static;
    }

    /**
     * Set ordering
     *
     * @param integer $ordering The ordering number
     */
    public function setOrdering($ordering)
    {
        $this->ordering = $ordering;
    }

    /**
     * Get ordering
     *
     * @return integer
     */
    public function getOrdering()
    {
        return $this->ordering;
    }

    /**
     * Return a string representing the Text
     *
     * @return string
     */
    public function __toString()
    {
        if (false == $this->id) {
            return 'New text';
        }

        if ($name = $this->translate()->getName()) {
            return $name;
        }

        if ($text = $this->translate()->getText()) {
            return html_entity_decode($text);
        }

        // No translation found in the current locale
        return '';
    }

//    /**
//     * Get Route Backend
//     *
//     * @param string $action Action
//     * @deprecated
//     * @return string
//     */
//    public function getRoute(): string
//    {
//        return $this->getRouteBackend($action);
//    }

//    /**
//     * Get Route Backend
//     *
//     * @param string $action Action
//     *
//     * @return string
//     */
//    public function getRouteBackend($action = 'edit')
//    {
//        return 'system_backend_text_' . $action;
//    }

//    /**
//     * Get Route Backend Params
//     *
//     * @param array $params Route Params
//     * @deprecated
//     * @return array
//     */
//    public function getRouteParams($params = array())
//    {
//        return $this->getRouteBackendParams($params);
//    }
//    /**
//     * Get Route Backend Params
//     *
//     * @param array $params Route Params
//     *
//     * @return array
//     */
//    public function getRouteBackendParams($params = array())
//    {
//        $defaults = array(
//            'id' => $this->id ? $this->id : 0
//        );
//        $params = array_merge($defaults, $params);
//
//        return $params;
//    }

    /**
     * List of methods to check before allowing deletion
     *
     * @return array
     */
    public function getDeleteRestrictions()
    {
        return array('isStatic');
    }
}