<?php

namespace App\Library\Globals\Media;

/**
 * Interface MediaParserInterface
 * @package App\Library\Globals\Media
 */
interface MediaParserInterface
{
    /**
     * Get source
     *
     * Return media source (ie: youtube, vimeo)
     *
     * @return string
     */
    public function getSource();

    /**
     * Get Thumbnail Url
     *
     * Returns a media thumbnail URL
     *
     * @return string
     */
    public function getThumbnailUrl();

    /**
     * Get Embed Url
     *
     * Returns a media embed URL
     *
     * @return string
     */
    public function getEmbedUrl();

    /**
     * Get Id
     *
     * Returns the ID of a media from a media URL
     *
     * @return mixed
     */
    public function getId();

    /**
     * Supports
     *
     * Check if $mediaUrl is supported by this parser
     *
     * @param $mediaUrl
     *
     * @return bool|int
     */
    public function supports($mediaUrl);
}