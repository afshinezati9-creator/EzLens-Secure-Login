<?php
/**
 * Backward-compatible loader for Product Options meta box.
 * Phase 1 implementation lives in admin/meta-box/class-meta-box.php
 *
 * @package EzLens_Secure_Login
 */

if (!defined('ABSPATH')) {
    exit;
}

$path = __DIR__ . '/meta-box/class-meta-box.php';
if (is_readable($path)) {
    require_once $path;
}
