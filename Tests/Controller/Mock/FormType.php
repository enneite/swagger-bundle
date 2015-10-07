<?php

/**
 * Created by JetBrains PhpStorm.
 * User: etienne.lejariel
 * Date: 03/07/15
 * Time: 11:54
 * To change this template use File | Settings | File Templates.
 */

namespace Enneite\SwaggerBundle\Tests\Controller\Mock;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('street', 'textarea');
    }

    /**
     * Form name getter.
     *
     * @inherit
     */
    public function getName()
    {
        return 'mock';
    }
}
