<?php
namespace EzLens\Core;
if (!defined('ABSPATH')) exit;
final class Plugin {
    private static $instance; private $container;
    public static function instance(){return self::$instance ?: (self::$instance=new self());}
    private function __construct(){$this->container=new Container();$this->container->instance('core',$this);$this->container->instance('container',$this->container);}
    public function boot(){do_action('ezlens_core_booted',$this);}
    public function get($id){return $this->container->get($id);}
    public function has($id){return $this->container->has($id);}
    public function service($id,$factory,$shared=true){$this->container->set($id,$factory,$shared);return $this;}
    public function container(){return $this->container;}
}
