<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Twig;

use DOMDocument;
use DOMNode;
use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\Values\User\User;

/**
 * Twig helper for premium content.
 */
class PremiumContentExtension extends Twig_Extension
{
    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /** @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface */
    private $tokenStorage;

    /** @var array */
    private $allowedUserGroupsLocationIds;

    /**
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param $allowedUserGroupsLocationIds
     */
    public function __construct(
        RepositoryInterface $repository,
        TokenStorageInterface $tokenStorage,
        $allowedUserGroupsLocationIds
    ) {
        $this->repository = $repository;
        $this->tokenStorage = $tokenStorage;
        $this->allowedUserGroupsLocationIds = $allowedUserGroupsLocationIds;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'premium_content_extension';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('ez_has_access_to_premium_content', [$this, 'hasAccessToPremiumContent']),
        ];
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('previewPremiumContent', [$this, 'previewPremiumContent'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Allows to display certain number of paragraphs.
     *
     * @param string $document
     * @param int $numberOfDisplayedParagraphs
     *
     * @return string
     */
    public function previewPremiumContent($document, $numberOfDisplayedParagraphs = 2)
    {
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($document, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $childNodes = $doc->documentElement->childNodes;
        $nodes = array();

        foreach ($childNodes as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                $nodes[] = $node;
            }
        }

        $countNodes = count($nodes);

        for ($i = $countNodes - 1; $i >= 0; --$i) {
            $node = $nodes[$i];

            if (!$node instanceof DOMNode) {
                continue;
            }

            if ($i >= $numberOfDisplayedParagraphs) {
                $node->parentNode->removeChild($node);
            }
        }

        return $doc->saveHTML();
    }

    /**
     * Checks if user has access to premium content.
     *
     * @return bool
     */
    public function hasAccessToPremiumContent()
    {
        static $hasAccess;

        if (null !== $hasAccess) {
            return $hasAccess;
        }

        if (false == $token = $this->tokenStorage->getToken()) {
            return false;
        }

        if (!is_object($token->getUser())) {
            return false;
        }

        $userGroups = $this->loadUserGroups($token->getUser()->getAPIUser());

        foreach ($userGroups as $userGroup) {
            if (in_array(
                $userGroup->content->contentInfo->mainLocationId,
                $this->allowedUserGroupsLocationIds
            )) {
                return $hasAccess = true;
            }
        }

        return $hasAccess = false;
    }

    /**
     * Loads User Groups of User, regardless to user limitations.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $apiUser
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup[]
     */
    private function loadUserGroups(User $apiUser)
    {
        return $this->repository->sudo(
            function (RepositoryInterface $repository) use ($apiUser) {
                return $repository->getUserService()->loadUserGroupsOfUser($apiUser);
            }
        );
    }
}
