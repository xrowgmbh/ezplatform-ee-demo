<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Controller;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\MVC\Symfony\View\View;
use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchAdapter;
use eZ\Bundle\EzPublishCoreBundle\Controller;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

class FolderController extends Controller
{
    /**
     * Displays the list of article.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\View $view
     * @param \Symfony\Component\HttpFoundation\Request $request request object
     *
     * @throws NotFoundHttpException $location is flagged as invisible
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\View
     */
    public function showChildrenAction(View $view, Request $request)
    {
        $location = $view->getLocation();
        $content = $view->getContent();

        if (null === $location->id) {
            return $view;
        }

        if ($location->invisible) {
            throw new NotFoundHttpException("Location #$location->id cannot be displayed as it is flagged as invisible.");
        }

        $languages = $this->getConfigResolver()->getParameter('languages');

        $excludedContentTypes = $this->container->getParameter('app.folder.folder_view.excluded_content_types');

        $criteria = $this->get('app.criteria_helper')->generateListFolderCriterion(
            $content, $excludedContentTypes, $languages
        );

        $query = new LocationQuery();
        $query->query = $criteria;
        $query->sortClauses = array(
            new SortClause\DatePublished(),
        );

        $pager = new Pagerfanta(
            new ContentSearchAdapter($query, $this->getRepository()->getSearchService())
        );

        $pager->setMaxPerPage($this->container->getParameter('app.folder.folder_list.limit'));
        $pager->setCurrentPage($request->get('page', 1));

        $includedContentTypeIdentifiers = $this->container->getParameter('app.folder.folder_tree.included_content_types');

        $subContentCriteria = $this->get('app.criteria_helper')->generateSubContentCriterion(
            $content, $includedContentTypeIdentifiers, $languages
        );

        $subContentQuery = new Query();
        $subContentQuery->query = $subContentCriteria;
        $subContentQuery->sortClauses = array(
            new SortClause\ContentName(),
        );

        $searchService = $this->getRepository()->getSearchService();
        $subContent = $searchService->findContent($subContentQuery);

        $treeItems = array();
        foreach ($subContent->searchHits as $hit) {
            $treeItems[] = $hit->valueObject;
        }

        $view->addParameters([
            'pagerFolder' => $pager,
            'treeItems' => $treeItems,
        ]);

        return $view;
    }
}
