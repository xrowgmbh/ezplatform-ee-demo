<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Seo;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use Novactive\Bundle\eZSEOBundle\Core\CustomFallbackInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomFallback implements CustomFallbackInterface
{
    /** @var array */
    private $options = [];

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined([
            'default_keywords',
        ]);

        $this->options = $resolver->resolve($options);
    }

    public function getMetaContent($metaName, ContentInfo $contentInfo)
    {
        return $metaName == 'keywords' ? $this->options['default_keywords'] : '';
    }
}
