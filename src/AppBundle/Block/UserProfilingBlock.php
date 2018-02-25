<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Block;

use DOMDocument;
use DOMElement;
use DOMNode;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use EzSystems\LandingPageFieldTypeBundle\Exception\InvalidBlockAttributeException;
use EzSystems\LandingPageFieldTypeBundle\FieldType\LandingPage\Definition\BlockDefinition;
use EzSystems\LandingPageFieldTypeBundle\FieldType\LandingPage\Definition\BlockAttributeDefinition;
use EzSystems\LandingPageFieldTypeBundle\FieldType\LandingPage\Model\AbstractBlockType;
use EzSystems\LandingPageFieldTypeBundle\FieldType\LandingPage\Model\BlockValue;
use eZ\Publish\API\Repository\ContentService;
use AppBundle\Block\Model\TagItem;
use Netgen\TagsBundle\Core\SignalSlot\TagsService;

class UserProfilingBlock extends AbstractBlockType
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \Netgen\TagsBundle\Core\SignalSlot\TagsService */
    private $tagsService;

    /**
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \Netgen\TagsBundle\Core\SignalSlot\TagsService $tagsService
     */
    public function __construct(ContentService $contentService, TagsService $tagsService)
    {
        $this->contentService = $contentService;
        $this->tagsService = $tagsService;
    }

    /**
     * Returns array of parameters required to render block template.
     *
     * @param BlockValue $blockValue Block value attributes
     *
     * @return array Template parameters
     */
    public function getTemplateParameters(BlockValue $blockValue)
    {
        $attributes = $blockValue->getAttributes();
        $items = [];

        foreach ($attributes['items']->getItems() as $item) {
            try {
                $items[] = [
                    'tag' => $this->tagsService->loadTag($item->getTagId()),
                    'content' => $this->contentService->loadContent($item->getContentId()),
                ];
            } catch (NotFoundException $e) {
            }
        }

        return [
            'items' => $items,
            'defaultContent' => $attributes['defaultContent'],
        ];
    }

    /**
     * Creates BlockDefinition object for block type.
     *
     * @return \EzSystems\LandingPageFieldTypeBundle\FieldType\LandingPage\Definition\BlockDefinition
     */
    public function createBlockDefinition()
    {
        return new BlockDefinition(
            'userprofiling',
            'User Profiling',
            'default',
            'bundles/app/images/userprofiling-block.svg',
            [],
            [
                new BlockAttributeDefinition(
                    'items',
                    'items',
                    'items',
                    '/[^\\s]/',
                    'The content value should be a text',
                    false,
                    false,
                    [],
                    []
                ),
                new BlockAttributeDefinition(
                    'defaultContent',
                    'Default content',
                    'embed',
                    '',
                    '',
                    true,
                    false,
                    []
                ),
            ]
        );
    }

    /**
     * Converts array from JSON to attributes array.
     *
     * @param object $attributes
     *
     * @return array $result
     */
    public function attributesFromJson($attributes)
    {
        $result = [
            'items' => new ItemCollection($this->itemsFromHash($attributes)),
        ];

        if (isset($attributes->defaultContent)) {
            $result['defaultContent'] = $attributes->defaultContent;
        }

        return $result;
    }

    /**
     * Converts hash to array of user profiler block item objects.
     *
     * @param object $attributes
     *
     * @return \AppBundle\Block\Model\TagItem[] $items
     */
    private function itemsFromHash($attributes)
    {
        if (!isset($attributes->items)) {
            return [];
        }

        $items = [];

        foreach ($attributes->items as $item) {
            if (isset($item->tagId, $item->contentId)) {
                $items[] = new TagItem(
                    $item->tagId,
                    $item->contentId
                );
            } else {
                throw new InvalidBlockAttributeException('tag', 'item', 'tagId or contentId is not set');
            }
        }

        return $items;
    }

    /**
     * Converts DOMElement to attributes array.
     *
     * @param \DOMElement $element
     *
     * @return array $result
     */
    public function attributesFromXml(DOMElement $element)
    {
        $result = [];

        foreach ($element->childNodes as $node) {
            if (XML_ELEMENT_NODE === $node->nodeType && 'items' === $node->nodeName) {
                $items = $this->itemsFromXML($node);
                $result['items'] = new ItemCollection($items);
            }

            if (XML_ELEMENT_NODE === $node->nodeType && 'defaultContent' === $node->nodeName) {
                $result['defaultContent'] = $node->nodeValue;
            }
        }

        return $result;
    }

    /**
     * Converts DOMElement to items array.
     *
     * @param \DOMElement $element
     *
     * @return \AppBundle\Block\Model\TagItem[]
     */
    private function itemsFromXML(DOMElement $element)
    {
        $items = [];

        foreach ($element->childNodes as $node) {
            if (XML_ELEMENT_NODE === $node->nodeType && 'item' === $node->nodeName) {
                $items[] = new TagItem(
                    (int) $node->getAttribute('tag_id'),
                    (int) $node->getAttribute('content_id')
                );
            }
        }

        return $items;
    }

    /**
     * Converts attributes array to DOMElement.
     *
     * @param \DOMDocument $document
     * @param array $attributes
     *
     * @return \DOMNode
     */
    public function attributesToXml(DOMDocument $document, array $attributes)
    {
        $attributesElement = $document->appendChild(
            $document->createElement('attributes')
        );

        $itemsElement = $attributesElement->appendChild(
            $document->createElement('items')
        );

        $this->itemsToXML($document, $itemsElement, $attributes['items']);

        $attributesElement->appendChild(
            $document->createElement('defaultContent', $attributes['defaultContent'])
        );

        return $attributesElement;
    }

    /**
     * Converts items array to DOMElement and attaches it to parent element.
     *
     * @param \DOMDocument $document
     * @param \DOMNode $itemsElement
     * @param \AppBundle\Block\ItemCollection $itemCollection
     */
    private function itemsToXML(DOMDocument $document, DOMNode $itemsElement, ItemCollection $itemCollection)
    {
        foreach ($itemCollection->getItems() as $item) {
            $itemElement = $itemsElement->appendChild(
                $document->createElement('item')
            );

            $itemElement->setAttribute('content_id', $item->getContentId());
            $itemElement->setAttribute('tag_id', $item->getTagId());
        }
    }

    /**
     * Converts attributes array to JSON string.
     *
     * @param array $attributes
     *
     * @return array
     */
    public function attributesToJson(array $attributes)
    {
        return [
            'items' => $this->itemsToHash($attributes['items']),
            'defaultContent' => $attributes['defaultContent'],
        ];
    }

    /**
     * Converts array of user profiling block items to hash.
     *
     * @param \AppBundle\Block\ItemCollection $input
     *
     * @return array
     */
    private function itemsToHash(ItemCollection $input)
    {
        $items = [];

        foreach ($input->getItems() as $item) {
            try {
                $tag = $this->tagsService->loadTag($item->getTagId());
                $content = $this->contentService->loadContentInfo($item->getContentId());

                $items[] = [
                    'tagId' => $item->getTagId(),
                    'contentId' => $item->getContentId(),
                    'contentName' => $content->name,
                    'tagName' => $tag->getKeyword(),
                    'tagPid' => $tag->parentTagId,
                    'tagLocate' => $tag->mainLanguageCode,
                ];
            } catch (NotFoundException $e) {
            }
        }

        return $items;
    }

    /**
     * Validates user input from the block configuration form.
     *
     * @param array $attributes
     */
    public function checkAttributesStructure(array $attributes)
    {
        if (!isset($attributes['items'])) {
            throw new InvalidBlockAttributeException(
                $this->getBlockDefinition()->getName(),
                'items',
                'Items must be set.'
            );
        }

        if (!isset($attributes['defaultContent'])) {
            throw new InvalidBlockAttributeException(
                $this->getBlockDefinition()->getName(),
                'defaultContent',
                'defaultContent must be set.'
            );
        }
    }
}
