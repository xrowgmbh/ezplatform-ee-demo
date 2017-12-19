<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Form\SignUp;

use AppBundle\Entity\Subscription;
use AppBundle\Form\Type\SignUpType;
use Symfony\Component\Form\FormFactory;

/**
 * Created form from factory for simple search box.
 */
class Form
{
    /** @var \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface */
    protected $form;

    /** @var \AppBundle\Entity\Subscription */
    protected $subscriptionClass;

    /**
     * @param \AppBundle\Entity\Subscription $subscriptionClass
     * @param \AppBundle\Form\Type\SignUpType $signUpType
     * @param \Symfony\Component\Form\FormFactory $formFactory
     */
    public function __construct(Subscription $subscriptionClass, SignUpType $signUpType, FormFactory $formFactory)
    {
        $this->form = $formFactory->createNamed(
            $signUpType->getName(),
            SignUpType::class,
            $subscriptionClass
        );

        $this->subscriptionClass = $subscriptionClass;
    }

    /**
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Returns SignUp object for handling the input values.
     *
     * @return \AppBundle\Entity\Subscription
     */
    public function getSubscriptionClass()
    {
        return $this->subscriptionClass;
    }
}
