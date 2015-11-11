<?php

namespace Enneite\SwaggerBundle\Entity;

interface EntityInterface
{
    /**
     * get the model as an array.
     *
     * @return array
     */
    public function toArray();
}
