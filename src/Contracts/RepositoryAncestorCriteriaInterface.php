<?php
namespace Aruberuto\Repository\Contracts;

use Illuminate\Support\Collection;


/**
 * Interface RepositoryAncestorCriteriaInterface
 * @package Aruberuto\Repository\Contracts
 */
interface RepositoryAncestorCriteriaInterface
{

    /**
     * Push Ancestor for filter the query
     *
     * @param $ancestor
     *
     * @return $this
     */
    public function pushAncestor($ancestor);

    /**
     * Pop Ancestor
     *
     * @param $ancestor
     *
     * @return $this
     */
    public function popAncestor($ancestor);

    /**
     * Get Collection of Ancestor
     *
     * @return Collection
     */
    public function getAncestor();

    /**
     * Skip Ancestor
     *
     * @param bool $status
     *
     * @return $this
     */
    public function skipAncestor($status = true);

    /**
     * Reset all Ancestors
     *
     * @return $this
     */
    public function resetAncestor();

}
