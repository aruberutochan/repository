<?php
namespace Aruberuto\Repository\Events;

/**
 * Class RepositoryEntityCreated
 * @package Aruberuto\Repository\Events
 */
class RepositoryEntityCreated extends RepositoryEventBase
{
    /**
     * @var string
     */
    protected $action = "created";
}
