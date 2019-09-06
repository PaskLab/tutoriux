<?php

namespace App\Library\Component\Media;

/**
 * Class YoutubeVideoParser
 */
class YoutubeVideoParser extends MediaParser implements MediaParserInterface
{
    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return 'youtube';
    }

    /**
     * Get Thumbnail Url
     *
     * Returns a video thumbnail URL
     *
     * @return string $url
     */
    public function getThumbnailUrl()
    {
        if ($this->getId()) {
            return 'https://img.youtube.com/vi/' . $this->getId() . '/0.jpg';
        }

        return null;
    }

    /**
     * Get Embed Url
     *
     * Returns a video embed URL
     *
     * @return string $url
     */
    public function getEmbedUrl()
    {
        if ($this->getId()) {
            return 'https://www.youtube.com/embed/' . $this->getId();
        }

        return null;
    }

    /**
     * Get Id
     *
     * Returns the ID of a video from a video URL
     *
     * @return mixed $id
     */
    public function getId()
    {
        if (preg_match('/youtube.com/i', $this->getMediaUrl())) {
            return preg_replace('#^(http://|https://)?(www\.)?youtube.com/embed/([^/]+)#i', '$3', $this->getMediaUrl());
        } elseif (preg_match('/youtu.be/i', $this->getMediaUrl())) {
            return preg_replace('#^(http://|https://)?(www\.)?youtu.be/([^/]+)#i', '$3', $this->getMediaUrl());
        }

        return null;
    }

    /**
     * Supports
     *
     * Check if $mediaUrl is supported by this parser
     *
     * @param $mediaUrl
     *
     * @return bool|int
     */
    public function supports($mediaUrl)
    {
        return (preg_match('#youtube.com/embed/[^/]+$#i', $mediaUrl) || preg_match('#youtu.be/[^/]+$#i', $mediaUrl));
    }
}