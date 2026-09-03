<?php
if (!defined('ABSPATH')) exit;

spl_autoload_register(function($class){
    $map = [
        'EzLens\\Core\\' => EZLAUTH_PLUGIN_DIR . 'core/',
        'EzLens\\Messaging\\' => EZLAUTH_PLUGIN_DIR . 'includes/messaging/',
        'EzLens\\ProductOptions\\' => EZLAUTH_PLUGIN_DIR . 'modules/product-options/src/',
    ];

    foreach ($map as $prefix => $base) {
        if (strpos($class, $prefix) !== 0) continue;
        $relative = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        $file = $base . $relative;
        if (is_readable($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
});
