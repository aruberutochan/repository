<?php
namespace Aruberuto\Repository\Contracts;

/**
 * Interface Presentable
 * @package Aruberuto\Repository\Contracts
 */
interface Presentable
{
    /**
     * @param PresenterInterface $presenter
     *
     * @return mixed
     */
    public function setPresenter(PresenterInterface $presenter);

    /**
     * @return mixed
     */
    public function presenter();
}
