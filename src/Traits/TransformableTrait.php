<?php 

namespace Aruberuto\Repository\Traits;

/**
 * Class TransformableTrait
 * @package Aruberuto\Repository\Traits
 */
trait TransformableTrait
{

    /**
     * @return array
     */
    public function transform()
    {
        return $this->toArray();
    }
}
