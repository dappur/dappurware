<?php

namespace Dappur\TwigExtension;

use Psr\Http\Message\RequestInterface;

class JsonDecode extends \Twig_Extension
{
    protected $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function getName()
    {
        return 'jsonDecode';
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('jsonDecode', array($this, 'jsonDecode')),
        );
    }

    public function jsonDecode($str, $options = 1)
    {
        $array = json_decode($str, $options);

        return $array;
    }
}
