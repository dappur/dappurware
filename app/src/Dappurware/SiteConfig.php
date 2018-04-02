<?php

namespace Dappur\Dappurware;

use Dappur\Model\ConfigGroups;

class SiteConfig
{
    public function getGlobalConfig()
    {
        $cfg = array();

        $config = ConfigGroups::whereNull('page_name')->with('config')->get();

        foreach ($config as $value) {
            foreach ($value->config as $cfgvalue) {
                $cfg[$cfgvalue->name] = $cfgvalue->value;
            }
        }

        $cfg['copyright-year'] = date("Y");

        return $cfg;
    }
}
