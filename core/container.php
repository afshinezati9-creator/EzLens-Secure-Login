<?php
namespace EzLens\Core;
if (!defined('ABSPATH')) exit;
class Container {
    private $definitions=[]; private $instances=[]; private $shared=[];
    public function set($id,$factory,$shared=true){$this->definitions[$id]=$factory;$this->shared[$id]=(bool)$shared;unset($this->instances[$id]);return $this;}
    public function instance($id,$instance){$this->instances[$id]=$instance;unset($this->definitions[$id]);$this->shared[$id]=true;return $this;}
    public function has($id){return array_key_exists($id,$this->instances)||array_key_exists($id,$this->definitions);}
    public function get($id){if(array_key_exists($id,$this->instances))return $this->instances[$id];if(!isset($this->definitions[$id]))throw new \RuntimeException('EzLens service not found: '.$id);$v=call_user_func($this->definitions[$id],$this);if(!empty($this->shared[$id]))$this->instances[$id]=$v;return $v;}
    public function remove($id){unset($this->definitions[$id],$this->instances[$id],$this->shared[$id]);return $this;}
}
