<?php

namespace App\Entity\Media;

use Doctrine\ORM\Mapping as ORM;

use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors,
    Tutoriux\DoctrineBehaviorsBundle\Model\Tree\NodeInterface;

/**
 * Class Folder
 * @package App\Entity\Media
 */
class Folder implements NodeInterface
{
    use TutoriuxORMBehaviors\Timestampable\Timestampable,
        TutoriuxORMBehaviors\Blameable\Blameable,
        TutoriuxORMBehaviors\Tree\MaterializedPath;

    /**
     * @var integer
     */
    protected  $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $medias;

    /**
     * @return string
     */
    public function __toString()
    {
        return ($this->name) ?: 'New Folder' ;
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
     * Set name
     *
     * @param string $name
     * @return Media
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add medias
     *
     * @param \MediaBundle\Entity\Media $medias
     * @return Folder
     */
    public function addMedia(\MediaBundle\Entity\Media $medias)
    {
        $this->medias[] = $medias;
    
        return $this;
    }

    /**
     * Remove medias
     *
     * @param \MediaBundle\Entity\Media $medias
     */
    public function removeMedia(\MediaBundle\Entity\Media $medias)
    {
        $this->medias->removeElement($medias);
    }

    /**
     * Get medias
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMedias()
    {
        return $this->medias;
    }

    /**
     * toArray
     *
     * @return string
     */
    public function toArray()
    {
        $children = array();

        /* @var $child Folder */
        foreach ($this->children as $child) {
            $children[] = $child->toArray();
        }

        usort($children, function($a, $b){
            return ($a['text'] < $b['text']) ? -1 : 1;
        });

        return array(
            'id' => $this->id,
            'text' => $this->name,
            'isFolder' => true,
            'children' => $children
        );
    }
}