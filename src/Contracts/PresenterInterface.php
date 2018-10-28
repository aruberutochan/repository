<?php
namespace Aruberuto\Repository\Contracts;

/**
 * Interface PresenterInterface
 * @package Aruberuto\Repository\Contracts
 */
interface PresenterInterface
{
    /**
     * Prepare data to present
     *
     * @param $data
     *
     * @return mixed
     */
    public function present($data);
}
