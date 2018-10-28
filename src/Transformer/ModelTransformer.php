<?php namespace Aruberuto\Repository\Transformer;

use League\Fractal\TransformerAbstract;
use Aruberuto\Repository\Contracts\Transformable;

/**
 * Class ModelTransformer
 * @package Aruberuto\Repository\Transformer
 */
class ModelTransformer extends TransformerAbstract
{
    public function transform(Transformable $model)
    {
        return $model->transform();
    }
}
