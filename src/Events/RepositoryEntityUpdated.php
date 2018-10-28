<?php
namespace Aruberuto\Repository\Events;

/**
 * Class RepositoryEntityUpdated
 * @package Aruberuto\Repository\Events
 */
class RepositoryEntityUpdated extends RepositoryEventBase
{
    /**
     * @var string
     */
    protected $action = "updated";
}
