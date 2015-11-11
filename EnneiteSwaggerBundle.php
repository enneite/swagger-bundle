<?php

namespace Enneite\SwaggerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EnneiteSwaggerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);//die('on passe ici ?');
        $container->addCompilerPass(new DependencyInjection\Compiler\ApiSecurityPass());
    }
}
