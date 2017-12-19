<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\EventListener;

use AppBundle\User\UserInterests;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Checks if tags has been chosen when user is logged in.
 */
class Login
{
    /** @var \Symfony\Component\Security\Core\Authorization\AuthorizationChecker */
    private $authorizationChecker;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    private $dispatcher;

    /** @var \Symfony\Component\Routing\RouterInterface */
    private $router;

    /** @var \AppBundle\User\UserInterests */
    private $userInterests;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationChecker $authorizationChecker
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \AppBundle\User\UserInterests $userInterests
     */
    public function __construct(
        AuthorizationChecker $authorizationChecker,
        EventDispatcherInterface $dispatcher,
        RouterInterface $router,
        UserInterests $userInterests
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->dispatcher = $dispatcher;
        $this->router = $router;
        $this->userInterests = $userInterests;
    }

    /**
     * @param \Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $request = $event->getRequest();

        if (!$this->isUserHasJustLoggedInByFrontpageForm($request)) {
            return;
        }

        if (!empty($this->userInterests->getListForLoggedUser())) {
            return;
        }

        $this->dispatcher->addListener(KernelEvents::RESPONSE, function (FilterResponseEvent $event) {
            $event->setResponse(new RedirectResponse($this->router->generate('app.user_preferences')));
        });
    }

    /**
     * Checks if user has just logged in using frontpage form or using remember_me cookie.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return bool
     */
    private function isUserHasJustLoggedInByFrontpageForm(Request $request)
    {
        return ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')
            || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED'))
            && $request->attributes->get('is_rest_request') !== true
            && $request->attributes->get('_route') === 'login_check'
            && $request->getPathInfo() === '/login_check';
    }
}
