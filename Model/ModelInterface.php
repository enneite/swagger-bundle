<?php

namespace Enneite\SwaggerBundle\Model;

interface ModelInterface
{
    /**
     * get the model as an array.
     *
     * @return array
     */
    public function toArray();
}
