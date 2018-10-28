<?php
namespace Aruberuto\Repository\Events;

/**
 * Class RepositoryEntityDeleted
 * @package Aruberuto\Repository\Events
 */
class RepositoryEntityDeleted extends RepositoryEventBase
{
    /**
     * @var string
     */
    protected $action = "deleted";
}
