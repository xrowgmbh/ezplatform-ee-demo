<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Controller;

use eZ\Publish\Core\MVC\Symfony\View\View;
use AppBundle\Form\SignUp\Form;
use AppBundle\Form\Type\SignUpType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\TwigBundle\TwigEngine as Templating;

class SubscribeController
{
    /** @var \AppBundle\Form\Type\SignUpType */
    private $signUpType;

    /** @var \AppBundle\Form\SignUp\Form */
    private $form;

    /** @var \Symfony\Bundle\TwigBundle\TwigEngine */
    private $templating;

    /**
     * @param \AppBundle\Form\Type\SignUpType $signUpType
     * @param \AppBundle\Form\SignUp\Form $form
     * @param \Symfony\Bundle\TwigBundle\TwigEngine $templating
     */
    public function __construct(SignUpType $signUpType, Form $form, Templating $templating)
    {
        $this->signUpType = $signUpType;
        $this->form = $form;
        $this->templating = $templating;
    }

    /**
     * Displays subscription form with country list.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\View $view
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\View
     */
    public function showFormAction(View $view, Request $request)
    {
        $form = $this->form->getForm();
        $form->handleRequest($request);

        $view->addParameters([
            'form' => $form->createView(),
        ]);

        return $view;
    }

    /**
     * Displays confirmation for form submit.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirm(Request $request)
    {
        $response = new Response();
        $form = $this->form->getForm();

        $form->handleRequest($request);

        return $this->templating->renderResponse(
            ':full:subscribe_confirmation.html.twig',
            [
                'form' => $form->createView(),
                'firstName' => $form->getData()->firstName,
                'lastName' => $form->getData()->lastName,
                'email' => $form->getData()->email,
            ],
            $response
        );
    }
}
