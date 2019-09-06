<?php

namespace App\Entity\Document;

use Doctrine\ORM\Mapping as ORM;
use App\Library\EntityInterface;
use App\Library\Traits\EntityUtils;
use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;
use Tutoriux\DoctrineBehaviorsBundle\Model\Translatable\TranslatableInterface;

/**
 * Class Document
 * @package App\Entity\Document
 */
class Document implements EntityInterface, TranslatableInterface
{
    use EntityUtils,
        TutoriuxORMBehaviors\Translatable\Translatable,
        TutoriuxORMBehaviors\Localizable\Localizable,
        TutoriuxORMBehaviors\Blameable\Blameable;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $indexable;

    /**
     * Document constructor.
     */
    public function __construct()
    {
        $this->indexable = false;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (false == $this->id) {
            return 'New document';
        }

        if ($name = $this->getName()) {
            return $name;
        }

        // No translation found in the current locale
        return '';
    }

    /**
     * Get route
     *
     * @return string
     */
    public function getRoute($suffix = 'document_edit'): string
    {
        return 'section_id_35_' . $suffix;
    }

    /**
     * Get route params
     *
     * @param array $params Array of params to get
     *
     * @return array
     */
    public function getRouteParams(iterable $params = []): iterable
    {
        return array_merge([
            'documentId' => $this->id,
            'editLocale' => $this->currentLocale
        ], $params);
    }

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
     * Set indexable
     *
     * @param boolean $indexable
     * @return Document
     */
    public function setIndexable($indexable)
    {
        $this->indexable = $indexable;

        return $this;
    }

    /**
     * Get indexable
     *
     * @return boolean 
     */
    public function getIndexable()
    {
        return $this->indexable;
    }
}
