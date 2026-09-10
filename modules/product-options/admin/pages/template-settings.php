<?php
/**
 * Backward-compatible loader for settings.
 * Does NOT render HTML. Call ezlens_po_render_settings_page() from menu callback.
 */
if (!defined('ABSPATH')) {
    exit;
}
require_once __DIR__ . '/settings/index.php';
