<?php

namespace App\Entity\Document;

use Doctrine\ORM\Mapping as ORM,
    Doctrine\Common\Collections\ArrayCollection;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;

use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;

/**
 * Class DocumentTranslation
 * @package DocumentBundle\Entity
 *
 * @Algolia\Index(
 *     algoliaName = "Document",
 *     perEnvironment = false,
 *     autoIndex = true,
 *     attributesToIndex = {"name", "categories"},
 *     customRanking = {"desc(votes)", "desc(page_views)"}
 * )
 */
class DocumentTranslation
{
    use TutoriuxORMBehaviors\Translatable\Translation,
        TutoriuxORMBehaviors\Sluggable\Sluggable,
        TutoriuxORMBehaviors\Metadatable\Metadatable,
        TutoriuxORMBehaviors\Timestampable\Timestampable,
        TutoriuxORMBehaviors\Taggable\Taggable,
        TutoriuxORMBehaviors\Blameable\Blameable;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     * @Algolia\Attribute
     */
    private $name;

    /**
     * @var boolean
     */
    private $published;

    /**
     * @var \DateTime
     */
    private $publicationDate;

    /**
     * @var boolean
     */
    private $public;

    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     * @Algolia\Attribute
     */
    private $description;

    /**
     * @var string
     * @Algolia\Attribute(algoliaName="url")
     */
    private $path;

    /**
     * @var string
     * @Algolia\Attribute
     */
    private $type;

    /**
     * @var array
     * @Algolia\Attribute
     */
    private $categories;

    /**
     * @var array
     * @Algolia\Attribute
     */
    private $treeCategories;

    /**
     * @var integer
     * @Algolia\Attribute(algoliaName="page_views")
     */
    private $pageViews;

    /**
     * @var integer
     * @Algolia\Attribute
     */
    private $votes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $voters;

    /**
     * DocumentTranslation constructor.
     */
    public function __construct()
    {
        $this->published = false;
        $this->public = false;
        $this->pageViews = 0;
        $this->votes = 0;
        $this->voters = new ArrayCollection();
        $this->categories = [];
        $this->treeCategories = [];
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
     * @return DocumentTranslation
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * @return boolean
     * @Algolia\IndexIf
     */
    public function isPublished()
    {
        return $this->published;
    }

    /**
     * @param $published
     * @return $this
     */
    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    /**
     * @param $publicationDate
     * @return $this
     */
    public function setPublicationDate($publicationDate)
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    /**
     * Set public
     *
     * @param boolean $public
     * @return DocumentTranslation
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Is public
     *
     * @return boolean
     */
    public function isPublic()
    {
        return $this->public;
    }

    /**
     * @return array
     */
    public function getContent()
    {
        return json_decode($this->content);
    }

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set categories
     *
     * @param array $categories
     * @return DocumentTranslation
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * Get categories
     *
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return array
     */
    public function getTreeCategories()
    {
        return $this->treeCategories;
    }

    /**
     * @param $treeCategories
     * @return $this
     */
    public function setTreeCategories($treeCategories)
    {
        $this->treeCategories = $treeCategories;

        return $this;
    }

    /**
     * @return int
     */
    public function getPageViews()
    {
        return $this->pageViews;
    }

    /**
     * @param $pageViews
     * @return $this
     */
    public function setPageViews($pageViews)
    {
        $this->pageViews = $pageViews;

        return $this;
    }

    /**
     * @return integer
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * @param $votes
     * @return $this
     */
    public function setVotes($votes)
    {
        $this->votes = $votes;

        return $this;
    }

    /**
     * Get Sluggable Fields
     *
     * @return array
     */
    public function getSluggableFields()
    {
        return ['name'];
    }

    /**
     * Returns the combination of fields for which the slug must be unique
     * An empty array mean that all slug will be unique
     *
     * @return array
     */
    public function getUniqueBy()
    {
        return ['name', 'section'];
    }

    /**
     * Get published
     *
     * @return boolean 
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * Get public
     *
     * @return boolean 
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Add voters
     *
     * @param DocumentVoter $voters
     * @return DocumentTranslation
     */
    public function addVoter(DocumentVoter $voters)
    {
        $this->voters[] = $voters;

        return $this;
    }

    /**
     * Remove voters
     *
     * @param DocumentVoter $voters
     */
    public function removeVoter(DocumentVoter $voters)
    {
        $this->voters->removeElement($voters);
    }

    /**
     * Get voters
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVoters()
    {
        return $this->voters;
    }

    /**
     * @return int
     * @Algolia\Attribute(algoliaName="date")
     */
    public function getIndexDate()
    {
        return strtotime($this->publicationDate->format('d-m-Y H:i:s'));
    }

    /**
     * @Algolia\Attribute(algoliaName="author")
     * @return string
     */
    public function getIndexAuthor()
    {
        return $this->getCreatedBy()->getFullName();
    }

    /**
     * @Algolia\Attribute(algoliaName="original_author")
     * @return string
     */
    public function getIndexOriginalAuthor()
    {
        return $this->translatable->getCreatedBy()->getFullName();
    }

    /**
     * @Algolia\Attribute(algoliaName="is_translation")
     * @return bool
     */
    public function isTranslation() {
        return ($this->getCreatedBy()->getId() != $this->translatable->getCreatedBy()->getId());
    }

    /**
     * @Algolia\Attribute(algoliaName="subject")
     * @return string
     */
    public function getIndexSubject()
    {
        return $this->translatable->getSection()->getTranslation($this->getLocale())->getName();
    }
}