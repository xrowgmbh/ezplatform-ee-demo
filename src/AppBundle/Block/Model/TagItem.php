<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Block\Model;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * Tag block item.
 */
class TagItem
{
    /** @var int */
    private $tagId;

    /** @var int */
    private $contentId;

    /**
     * @param int $tagId
     * @param int $contentId
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    public function __construct($tagId, $contentId)
    {
        if (empty($tagId)) {
            throw new InvalidArgumentException('tagId', 'is not set');
        }

        if (empty($contentId)) {
            throw new InvalidArgumentException('contentId', 'is not set');
        }

        $this->tagId = $tagId;
        $this->contentId = $contentId;
    }

    /**
     * Returns item tagId.
     *
     * @return int
     */
    public function getTagId()
    {
        return $this->tagId;
    }

    /**
     * Returns item contentId.
     *
     * @return int
     */
    public function getContentId()
    {
        return $this->contentId;
    }
}
