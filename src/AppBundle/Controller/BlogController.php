<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Controller;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\MVC\Symfony\View\View;
use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchAdapter;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Bundle\EzPublishCoreBundle\Controller;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogController extends Controller
{
    /**
     * Displays the list of blog_post
     * Note: This is a fully customized controller action, it will generate the response and call
     *       the view. Since it is not calling the ViewControler we don't need to match a specific
     *       method signature.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\View $view
     * @param Request $request
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\View
     */
    public function listBlogPostsAction(View $view, Request $request)
    {
        $response = new Response();
        $location = $view->getLocation();

        // Setting default cache configuration (you can override it in you siteaccess config)
        $response->setSharedMaxAge($this->getConfigResolver()->getParameter('content.default_ttl'));

        // Make the response location cache aware for the reverse proxy
        $response->headers->set('X-Location-Id', $location->id);
        $response->setVary('X-User-Hash');

        $viewParameters = $request->attributes->get('viewParameters');

        // Getting location and content from ezpublish dedicated services
        $repository = $this->getRepository();
        if ($location->invisible) {
            throw new NotFoundHttpException("Location #$location->id cannot be displayed as it is flagged as invisible.");
        }

        $content = $repository
            ->getContentService()
            ->loadContentByContentInfo($location->getContentInfo());

        // Getting language for the current siteaccess
        $languages = $this->getConfigResolver()->getParameter('languages');

        // Using the criteria helper (a demobundle custom service) to generate our query's criteria.
        // This is a good practice in order to have less code in your controller.
        $criteria = $this->get('app.criteria_helper')->generateListBlogPostCriterion(
            $location, $viewParameters, $languages
        );

        // Generating query
        $query = new Query();
        $query->query = $criteria;
        $query->sortClauses = array(
            new SortClause\Field('blog_post', 'publication_date', Query::SORT_DESC, $languages[0]),
        );

        // Initialize pagination.
        $pager = new Pagerfanta(
            new ContentSearchAdapter($query, $this->getRepository()->getSearchService())
        );
        $pager->setMaxPerPage($this->container->getParameter('app.blog.blog_post_list.limit'));
        $pager->setCurrentPage($request->get('page', 1));

        $view->addParameters([
            'location' => $location,
            'content' => $content,
            'pagerBlog' => $pager,
        ]);

        return $view;
    }

    /**
     * Action used to display a blog_post
     *  - Adds the content's author to the response.
     * Note: This is a partly customized controller action. It is executed just before the original
     *       Viewcontroller's viewLocation method. To be able to do that, we need to implement it's
     *       full signature.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\View $view
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\View
     */
    public function showBlogPostAction(View $view)
    {
        // We need the author, whatever the view type is.
        $repository = $this->getRepository();

        $author = $repository->getUserService()->loadUser($view->getLocation()->getContentInfo()->ownerId);

        $view->addParameters([
            'author' => $author,
        ]);

        return $view;
    }
}
