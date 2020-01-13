<?php

namespace Dappur\Dappurware;

use Psr\Container\ContainerInterface;

class Dappurware
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __get($property)
    {
        return $this->container->get($property);
    }
}
