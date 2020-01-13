<?php

namespace Dappur\TwigExtension;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;

class Menus extends \Twig_Extension
{
    protected $auth;
    protected $request;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'menus';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('getMenu', [$this, 'getMenu'])
        ];
    }

    public function getMenu($menuId)
    {
        $menu = new \Dappur\Model\Menus;
        $menu = $menu->find($menuId);
        $menu = json_decode($menu->json, true);
        $menu = $this->validateMenu($menu);
        return $menu;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function validateMenu($menu)
    {
        $user = $this->container->auth->check();

        foreach ($menu as $key => $value) {
            if ($value['auth'] == "true" && !$user) {
                unset($menu[$key]);
                continue;
            }

            if ($value['guest'] == "true" && $user) {
                unset($menu[$key]);
                continue;
            }

            if (!empty($value['permission']) && !$this->container->auth->hasAccess($value['permission'])) {
                unset($menu[$key]);
                continue;
            }

            if ($value['roles'] && !empty($value['roles'])) {
                $hasRole = false;
                if (!$user) {
                    unset($menu[$key]);
                    continue;
                }

                foreach ($value['roles'] as $role) {
                    if ($user->inRole($role)) {
                        $hasRole = true;
                    }
                }
                    
                if (!$hasRole) {
                    unset($menu[$key]);
                    continue;
                }
            }

            if (isset($value['children']) && !empty($value['children'])) {
                $menu[$key]['children'] = $this->validateMenu($value['children']);
            }

            $htmlTemp = new \Twig_Environment(
                new \Twig_Loader_Array([$value['text'] . '_html' => $value['text']])
            );
            $htmlTemp = $htmlTemp->render(
                $value['text'] . '_html',
                array("user" => $user)
            );

            $menu[$key]['text'] = $htmlTemp;
        }

        return $menu;
    }
}
