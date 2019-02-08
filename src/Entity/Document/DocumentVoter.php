<?php

namespace App\Entity\Document;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

/**
 * Class DocumentVoter
 * @package App\Entity\Document
 */
class DocumentVoter
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var DocumentTranslation
     */
    private $document;

    /**
     * @var User
     */
    private $user;
    
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
     * Set document
     *
     * @param DocumentTranslation $document
     * @return DocumentVoter
     */
    public function setDocument(DocumentTranslation $document = null)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document
     *
     * @return DocumentTranslation
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return DocumentVoter
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
