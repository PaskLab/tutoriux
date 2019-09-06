<?php

namespace App\Entity\Media;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use App\Library\EntityInterface;
use App\Library\Traits\EntityUtils;
use Tutoriux\DoctrineBehaviorsBundle\Model as TutoriuxORMBehaviors;

/**
 * Class Media
 * @package App\Entity\Media
 */
class Media implements EntityInterface
{

    use EntityUtils,
        TutoriuxORMBehaviors\Uploadable\Uploadable,
        TutoriuxORMBehaviors\Timestampable\Timestampable,
        TutoriuxORMBehaviors\Blameable\Blameable;

    /**
     * @var integer
     */
    protected  $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    protected $media = null;

    /**
     * @var string
     */
    protected $mediaPath;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $embedId;

    /**
     * @var string
     */
    protected $credit;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $caption;

    /**
     * @var string
     */
    protected $sourceDetails;

    /**
     * @var string
     */
    protected $sourceUrl;

    /**
     * @var string
     */
    protected $mimeType;

    /**
     * @var float
     */
    protected $size;

    /**
     * @var integer
     */
    private $width;

    /**
     * @var integer
     */
    private $height;

    /**
     * @var integer
     */
    private $resizeWidth;

    /**
     * @var integer
     */
    private $resizeHeight;

    /**
     * @var string
     */
    private $cropJson;

    /**
     * @var integer
     */
    private $cropWidth;

    /**
     * @var integer
     */
    private $cropHeight;

    /**
     * @var Media
     */
    private $thumbnail;

    /**
     * @var bool
     */
    private $hidden;

    /**
     * @var bool
     */
    private $locked;

    /**
     * @var Folder
     */
    private $folder;

    /**
     * @var ArrayCollection
     */
    private $locks;

    /**
     * Media constructor.
     */
    public function __construct()
    {
        $this->hidden = false;
        $this->locks = new ArrayCollection();
        $this->locked = false;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return ($this->name) ?: 'New media' ;
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
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set source
     *
     * @param $source
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set the media path
     * @param $path
     */
    public function setMediaPath($path)
    {
        $this->mediaPath = $path;
    }

    /**
     * @return string|null
     */
    public function getMediaPath()
    {
        switch ($this->type) {
            case 'embedvideo':
                return $this->mediaPath;
            default:
                return $this->getWebPath('media');
        }
    }

    /**
     * Get mediaPath field value
     *
     * @return string
     */
    public function getMediaPathField()
    {
        return $this->mediaPath;
    }

    /**
     * Set url
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set embedId
     *
     * @param $embedId
     * @return $this
     */
    public function setEmbedId($embedId)
    {
        $this->embedId = $embedId;

        return $this;
    }

    /**
     * Get embedId
     *
     * @return string
     */
    public function getEmbedId()
    {
        return $this->embedId;
    }

    /**
     * Set media
     *
     * @param $media
     */
    public function setMedia($media)
    {
        $this->setUploadedFile($media, 'media');
    }

    /**
     * Get media
     *
     * @return UploadedFile
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @return string
     */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * @param $credit
     * @return $this
     */
    public function setCredit($credit)
    {
        $this->credit = $credit;

        return $this;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set caption
     *
     * @param string $caption
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * @return string
     */
    public function getSourceDetails()
    {
        return $this->sourceDetails;
    }

    /**
     * @param $sourceDetails
     * @return $this
     */
    public function setSourceDetails($sourceDetails)
    {
        $this->sourceDetails = $sourceDetails;

        return $this;
    }

    /**
     * @return string
     */
    public function getSourceUrl()
    {
        return $this->sourceUrl;
    }

    /**
     * @param $sourceUrl
     * @return $this
     */
    public function setSourceUrl($sourceUrl)
    {
        $this->sourceUrl = $sourceUrl;

        return $this;
    }

    /**
     * Set mimeType
     *
     * @param string $mimeType
     * @return Media
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    
        return $this;
    }

    /**
     * Get mimeType
     *
     * @return string 
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * setSize
     *
     * @param $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * getSize
     *
     * @return float
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set width
     *
     * @param $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * Get width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * Get height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param $resizeHeight
     * @return $this
     */
    public function setResizeHeight($resizeHeight)
    {
        $this->resizeHeight = $resizeHeight;

        return $this;
    }

    /**
     * @return int
     */
    public function getResizeHeight()
    {
        return ($this->resizeHeight) ?: $this->height;
    }

    /**
     * @param $resizeWidth
     * @return $this
     */
    public function setResizeWidth($resizeWidth)
    {
        $this->resizeWidth = $resizeWidth;

        return $this;
    }

    /**
     * @return int
     */
    public function getResizeWidth()
    {
        return ($this->resizeWidth) ?: $this->width;
    }

    /**
     * @param $cropJson
     * @return $this
     */
    public function setCropJson($cropJson)
    {
        $this->cropJson = $cropJson;

        return $this;
    }

    /**
     * @return string
     */
    public function getCropJson()
    {
        return $this->cropJson;
    }

    /**
     * @param $cropWidth
     * @return $this
     */
    public function setCropWidth($cropWidth)
    {
        $this->cropWidth = $cropWidth;

        return $this;
    }

    /**
     * @return int
     */
    public function getCropWidth()
    {
        return ($this->cropWidth) ?: $this->getResizeWidth();
    }

    /**
     * @param $cropHeight
     * @return $this
     */
    public function setCropHeight($cropHeight)
    {
        $this->cropHeight = $cropHeight;

        return $this;
    }

    /**
     * @return int
     */
    public function getCropHeight()
    {
        return ($this->cropHeight) ?: $this->getResizeHeight();
    }

    /**
     * Set hidden
     *
     * @param $hidden
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * Get hidden
     *
     * @return bool
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * @return boolean
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * @param $locked
     * @return $this
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Set folder
     *
     * @param Folder $folder
     * @return Media
     */
    public function setFolder(Folder $folder = null)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Get folder
     *
     * @return Folder
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * @return ArrayCollection
     */
    public function getLocks()
    {
        return $this->locks;
    }

    /**
     * @param $locks
     * @return $this
     */
    public function setLocks($locks)
    {
        $this->locks = $locks;

        return $this;
    }

    /**
     * @param Lock $lock
     * @return $this
     */
    public function addLock(Lock $lock)
    {
        $this->locks->add($lock);

        return $this;
    }

    /**
     * @param Lock $lock
     * @return $this
     */
    public function removeLock(Lock $lock)
    {
        $this->locks->removeElement($lock);

        return $this;
    }

    /**
     * Set thumbnail
     *
     * @param Media $thumbnail
     */
    public function setThumbnail(Media $thumbnail = null)
    {
        $this->thumbnail = $thumbnail;
    }

    /**
     * Get thumbnail
     *
     * @return Media
     */
    public function getThumbnail()
    {
        if ('embedvideo' != $this->type) {
            return $this;
        }

        return $this->thumbnail;
    }

    /**
     * Get the thumbnail url
     * @return string
     */
    public function getThumbnailUrl()
    {
        if ('image' == $this->type) {
            return $this->getMediaPath();
        }

        return $this->thumbnail->getMediaPath();
    }

    /**
     * Get media html tag
     *
     * @return string
     */
    public function getHtmlTag()
    {
        $stamp = '?' . rand(1, 9999);

        switch ($this->type) {
            case 'image': // TODO: HtmlTag ne devrait pas être une méthode de l'entité....
                $url = null; //$this->container->get('liip_imagine.cache.manager')->getBrowserPath($this->getMediaPath(), 'media');
                return '<img data-mediaid="' . $this->id . '" src="' . $url . $stamp . '" class="img-responsive">';
            case 'video':
                return '<iframe data-mediaid="' . $this->id . '" width="560" height="315" frameborder="0"  allowfullscreen src="' . $this->getMediaPath() . '"></iframe>';
            case 'embedvideo':
                return '<span class="iframe-wrapper" data-mediaid="' . $this->id . '"><iframe allowfullscreen="true" src="' . $this->getMediaPath() . '"></iframe></span>';
            default:
                return '<a data-mediaid="' . $this->id . '" href="' . $this->getMediaPath() . '">' . $this->name . '</a>';
        }
    }

    /**
     * Get the list of uploabable fields and their respective upload directory in a key => value array format.
     *
     * @return array
     */
    public function getUploadableFields()
    {
        return [
            'media' => 'medias/' . $this->getCreatedBy()->getId()
        ];
    }
}