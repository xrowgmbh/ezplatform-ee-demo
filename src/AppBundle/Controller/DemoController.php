<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\API\Repository\Values\Content\Location;

class DemoController extends Controller
{
    /**
     * Displays description, tagcloud, tags, ezarchive and calendar
     * of the parent's of a given location.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewParentContentTypeAction(Location $location)
    {
        $repository = $this->getRepository();
        $parentLocation = $repository->getLocationService()->loadLocation($location->parentLocationId);

        return $this->render(
            ':parts/blog:parent_contenttype.html.twig',
            array('location' => $parentLocation)
        );
    }
}
