<?php namespace DebugBar;

use Slim\Slim;
use DebugBar\DataCollector\ConfigCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DataCollector\SlimEnvCollector;
use DebugBar\DataCollector\SlimLogCollector;
use DebugBar\DataCollector\SlimResponseCollector;
use DebugBar\DataCollector\SlimRouteCollector;
use DebugBar\DataCollector\SlimViewCollector;

class SlimDebugBar extends DebugBar
{
    public function __construct()
    {
        $this->addCollector(new TimeDataCollector());
        $this->addCollector(new RequestDataCollector());
        $this->addCollector(new MemoryCollector());
    }

    public function initCollectors(Slim $slim)
    {
        $this->addCollector(new SlimLogCollector($slim));
        $this->addCollector(new SlimEnvCollector($slim));
        $_that = $this;
        $slim->hook('slim.after.router', function() use ($slim, $_that)
        {
            $setting = $_that->prepareRenderData($slim->container['settings']);
            $data = $_that->prepareRenderData($slim->view->all());
            $_that->addCollector(new SlimResponseCollector($slim->response));
            $_that->addCollector(new ConfigCollector($setting));
            $_that->addCollector(new SlimViewCollector($data));
            $_that->addCollector(new SlimRouteCollector($slim));
        });
    }

    public function prepareRenderData(array $data = array())
    {
        $tmp = array();
        foreach ($data as $key => $val) {
            if (is_object($val)) {
                $val = "Object (". get_class($val) .")";
            }
            $tmp[$key] = $val;
        }
        return $tmp;
    }
}