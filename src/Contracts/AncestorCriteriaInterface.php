<?php
namespace Aruberuto\Repository\Contracts;

/**
 * Interface AncestorCriteriaInterface
 * @package Aruberuto\Repository\Contracts
 */
interface AncestorCriteriaInterface
{
    /**
     * Apply ancestor in query repository
     *
     * @param                     $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository);
}
