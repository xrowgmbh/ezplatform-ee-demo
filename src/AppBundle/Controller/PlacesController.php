<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\MVC\Symfony\View\View;
use AppBundle\Exceptions\ContentNotFound;

class PlacesController extends Controller
{
    /**
     * @param \eZ\Publish\Core\MVC\Symfony\View\View $view
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\View
     *
     * @throws \AppBundle\Exceptions\ContentNotFound
     */
    public function showPlacesAction(View $view)
    {
        $repository = $this->getRepository();
        $query = new Query();
        $query->query = new Criterion\LogicalAnd(
            [
                new Criterion\ContentTypeIdentifier('place_list'),
                new Criterion\Visibility(Criterion\Visibility::VISIBLE),
            ]
        );

        $searchHits = $repository->getSearchService()->findContent($query)->searchHits;
        if (empty($searchHits)) {
            throw new ContentNotFound('No place_list found.');
        }

        $placeList = $searchHits[0]->valueObject;

        $query->query = new Criterion\LogicalAnd(
            [
                new Criterion\ContentTypeIdentifier('place'),
                new Criterion\Visibility(Criterion\Visibility::VISIBLE),
            ]
        );

        $searchHits = $repository->getSearchService()->findContent($query)->searchHits;

        $contentArray = [];
        foreach ($searchHits as $searchHit) {
            $content = $searchHit->valueObject;
            $contentArray[] = $content;
        }

        $view->addParameters([
            'contentArray' => $contentArray,
            'content' => $placeList,
            'id' => $placeList->versionInfo->contentInfo->id,
        ]);

        return $view;
    }

    /**
     * Displayone place.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\View $view
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\View
     */
    public function showPlaceAction(View $view)
    {
        $query = new LocationQuery();
        $query->query = new Criterion\LogicalAnd(
            [
                new Criterion\ContentTypeIdentifier('place'),
                new Criterion\Visibility(Criterion\Visibility::VISIBLE),
            ]
        );

        $repository = $this->getRepository();
        $searchHits = $repository->getSearchService()->findLocations($query)->searchHits;
        $prev = $next = null;

        foreach ($searchHits as $key => $searchHit) {
            if ($searchHit->valueObject->id == $view->getLocation()->id) {
                if (isset($searchHits[$key + 1])) {
                    $prev = $searchHits[$key + 1]->valueObject;
                }
                if (isset($searchHits[$key - 1])) {
                    $next = $searchHits[$key - 1]->valueObject;
                }
            }
        }

        $view->addParameters([
            'prev' => $prev,
            'next' => $next,
        ]);

        return $view;
    }
}
