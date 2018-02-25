<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignUpType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('post')
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'required' => true,
                'first_options' => ['label' => 'email'],
                'second_options' => ['label' => 'confirm email'],
            ])
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('country', CountryType::class)
            ->add('termsOfUse', CheckboxType::class, ['mapped' => false])
            ->add('save', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'app_signup';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => 'AppBundle\Entity\Subscription']);
    }
}
