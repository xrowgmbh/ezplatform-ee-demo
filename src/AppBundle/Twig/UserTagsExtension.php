<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Twig;

use AppBundle\User\UserInterests;
use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Twig helper for logged user tags.
 */
class UserTagsExtension extends Twig_Extension
{
    /** @var \AppBundle\User\UserInterests */
    private $userInterests;

    /**
     * @param \AppBundle\User\UserInterests $userInterests
     */
    public function __construct(UserInterests $userInterests)
    {
        $this->userInterests = $userInterests;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'logged_user_tags_extension';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('get_user_tags', [$this, 'getUserTags']),
            new Twig_SimpleFunction('class', [$this, 'getClass']),
        ];
    }

    /**
     * Returns logged user tags.
     *
     * @return array
     */
    public function getUserTags()
    {
        return $this->userInterests->getListForLoggedUser();
    }

    /** TODO: delete me */
    public function getClass($object)
    {
        $namespace = (new \ReflectionClass($object))->getNamespaceName();

        $mapping = [
            'eZ\Publish\Core\FieldType\TextLine' => 'ezstring',
            'eZ\Publish\Core\FieldType\RichText' => 'ezrichtext',
            'eZ\Publish\Core\FieldType\Checkbox' => 'ezboolean',
            'eZ\Publish\Core\FieldType\Image' => 'ezimage',
            'Netgen\TagsBundle\Core\FieldType\Tags' => 'eztags',
            'Novactive\Bundle\eZSEOBundle\Core\FieldType\Metas' => 'novaseometas',
            'eZ\Publish\Core\FieldType\MapLocation' => 'ezgmaplocation',
            'eZ\Publish\Core\FieldType\Author' => 'ezauthor',
            'eZ\Publish\Core\FieldType\DateAndTime' => 'ezdatetime',
            'eZ\Publish\Core\FieldType\Rating' => 'ezsrrating',
            'eZ\Publish\Core\FieldType\RelationList' => 'ezobjectrelationlist',
        ];

        if (isset($mapping[$namespace])) {
            return $mapping[$namespace];
        }

        throw new \Exception(sprintf('Undefined field type "%s"', $namespace));
    }
}
