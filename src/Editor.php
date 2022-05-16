<?php
namespace Recras;

class Editor
{
    /**
     * Add the shortcode generator buttons to TinyMCE
     */
    public static function addButtons(): void
    {
        add_filter('mce_buttons', [__CLASS__, 'registerButtons'], 999, 2);
        add_filter('mce_external_plugins', [__CLASS__, 'addScripts'], 999);
        add_thickbox();
    }


    /**
     * Load the script needed for TinyMCE
     */
    public static function addScripts(array $plugins): array
    {
        global $recrasPlugin;

        $plugins['recras'] = $recrasPlugin->baseUrl . '/editor/plugin.js';
        return $plugins;
    }



    /**
     * Register TinyMCE buttons
     */
    public static function registerButtons(array $buttons, string $editorId): array
    {
        array_push(
            $buttons,
            'recras-arrangement',
            'recras-availability',
            'recras-booking',
            'recras-bookprocess',
            'recras-contact',
            'recras-product',
            'recras-voucher-info',
            'recras-voucher-sales'
        );
        return $buttons;
    }
}
