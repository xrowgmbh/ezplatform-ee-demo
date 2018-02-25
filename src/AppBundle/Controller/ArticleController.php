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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends Controller
{
    /**
     * Renders article with extra parameters that controls page elements visibility such as image and summary.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\View $view
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\View
     */
    public function showArticleAction(View $view)
    {
        $view->addParameters([
            'showSummary' => $this->container->getParameter('app.article.full_view.show_summary'),
            'showImage' => $this->container->getParameter('app.article.full_view.show_image'),
        ]);

        return $view;
    }

    /**
     * Displays latest article for header.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function latestArticleAction(Request $request, $template)
    {
        $repository = $this->getRepository();

        $criteria = new Criterion\LogicalAnd([
            new Criterion\ContentTypeIdentifier('article'),
            new Criterion\Visibility(Criterion\Visibility::VISIBLE),
        ]);

        $localeConverter = $this->container->get('ezpublish.locale.converter');
        $currentEzLanguage = $localeConverter->convertToEz($request->get('_locale'));
        $query = new LocationQuery();
        $query->query = $criteria;
        $query->limit = 1;
        $query->sortClauses = array(
            new Query\SortClause\Field('article', 'publish_date', LocationQuery::SORT_DESC, $currentEzLanguage),
        );

        $searchResult = $repository->getSearchService()->findLocations($query);
        if (count($searchResult->searchHits) > 0) {
            $location = $searchResult->searchHits[0]->valueObject;
            $article = $repository->getContentService()->loadContentByContentInfo($location->contentInfo);

            return $this->render(
                $template,
                array(
                    'location' => $location,
                    'article' => $article,
                )
            );
        }

        return new Response();
    }
}
