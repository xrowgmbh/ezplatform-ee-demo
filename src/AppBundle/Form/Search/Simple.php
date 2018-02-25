<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Form\Search;

use AppBundle\Entity\SimpleSearch;
use AppBundle\Form\Type\SimpleSearchType;
use Symfony\Component\Form\FormFactory;

/**
 * Created form from factory for simple search box.
 */
class Simple
{
    /** @var \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface */
    protected $form;

    /** @var SimpleSearch */
    protected $simpleSearch;

    /**
     * @param SimpleSearch $simpleSearchClass
     * @param SimpleSearchType $simpleSearchFormType
     * @param FormFactory $formFactory
     */
    public function __construct(SimpleSearch $simpleSearchClass, SimpleSearchType $simpleSearchFormType, FormFactory $formFactory)
    {
        $this->form = $formFactory->createNamed(
            $simpleSearchFormType->getName(),
            SimpleSearchType::class,
            $simpleSearchClass
        );

        $this->simpleSearch = $simpleSearchClass;
    }

    /**
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Returns SimpleSearch object for handling the input values.
     *
     * @return SimpleSearch
     */
    public function getSearchClass()
    {
        return $this->simpleSearch;
    }
}
