<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\Core\MVC\ConfigResolverInterface as ConfigResolver;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchAdapter;
use AppBundle\Form\Search\Simple;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\TwigBundle\TwigEngine as Templating;

class SearchController extends Controller
{
    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \AppBundle\Form\Search\Simple */
    private $simpleForm;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \Symfony\Bundle\TwigBundle\TwigEngine */
    private $templating;

    /** @var array */
    private $contentTypes;

    /** @var int */
    private $searchLimit;

    /**
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \AppBundle\Form\Search\Simple $simpleForm
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     * @param \Symfony\Bundle\TwigBundle\TwigEngine $templating
     * @param array $contentTypes
     * @param int $searchLimit
     */
    public function __construct(
        SearchService $searchService,
        Simple $simpleForm,
        ConfigResolver $configResolver,
        Templating $templating,
        $contentTypes,
        $searchLimit
    ) {
        $this->searchService = $searchService;
        $this->simpleForm = $simpleForm;
        $this->configResolver = $configResolver;
        $this->templating = $templating;
        $this->contentTypes = $contentTypes;
        $this->searchLimit = $searchLimit;
    }

    /**
     * Displays the simple search page.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showSearchResultsAction(Request $request)
    {
        $response = new Response();
        $searchCount = 0;
        $searchText = '';

        $form = $this->simpleForm->getForm();
        $form->handleRequest($request);

        $pager = null;

        if ($form->isSubmitted() && $form->isValid()) {
            if (!empty($this->simpleForm->getSearchClass()->searchText)) {
                $searchText = $this->simpleForm->getSearchClass()->searchText;
                $pager = $this->searchForPaginatedContent(
                    $searchText,
                    $request->get('page', 1),
                    $this->configResolver->getParameter('languages')
                );

                $searchCount = $pager->getNbResults();
            }
        }

        return $this->templating->renderResponse(
            ':search:search.html.twig',
            [
                'searchText' => $searchText,
                'searchCount' => $searchCount,
                'pagerSearch' => $pager,
                'form' => $form->createView(),
            ],
            $response
        );
    }

    /**
     * Search for content for a given $searchText and returns a pager.
     *
     * @param string $searchText to be looked up
     * @param int $currentPage to be displayed
     * @param array $languages to include in the search
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function searchForPaginatedContent($searchText, $currentPage, $languages)
    {
        $query = new Query();
        $query->query = new Criterion\FullText($searchText);
        $query->filter = new Criterion\LogicalAnd([
            new Criterion\Visibility(Criterion\Visibility::VISIBLE),
            new Criterion\LanguageCode($languages, true),
            new Criterion\ContentTypeIdentifier($this->contentTypes),
        ]);

        $pager = new Pagerfanta(
            new ContentSearchAdapter($query, $this->searchService)
        );
        $pager->setMaxPerPage($this->searchLimit);
        $pager->setCurrentPage($currentPage);

        return $pager;
    }

    /**
     * Displays the search box for the page header.
     *
     * @return \Symfony\Component\HttpFoundation\Response HTML code of the page
     */
    public function searchBoxAction()
    {
        $response = new Response();

        $form = $this->simpleForm->getForm();

        return $this->templating->renderResponse(
            '::page_header_searchbox.html.twig',
            ['form' => $form->createView()],
            $response
        );
    }
}
