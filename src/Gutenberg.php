<?php
namespace Recras;

class Gutenberg
{
    const ENDPOINT_NAMESPACE = 'recras';
    const GUTENBERG_SCRIPT_VERSION = '4.1.3';


    public static function addBlocks()
    {
        $globalScriptName = 'recras-gutenberg-global';
        $globalStyleName = 'recras-gutenberg';
        wp_register_script(
            $globalScriptName,
            plugins_url('js/gutenberg-global.js', __DIR__), [
                'wp-blocks',
                'wp-components',
                'wp-date',
                'wp-element',
                'wp-i18n',
            ],
            self::GUTENBERG_SCRIPT_VERSION,
            true
        );
        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations($globalScriptName, Plugin::TEXT_DOMAIN, plugin_dir_path(__DIR__) . 'lang');
        }
        wp_localize_script($globalScriptName, 'recrasOptions', [
            'settingsPage' => admin_url('admin.php?page=' . Settings::OPTION_PAGE),
            'subdomain' => get_option('recras_subdomain'),
        ]);

        wp_register_style(
            $globalStyleName,
            plugins_url('css/gutenberg.css', __DIR__),
            ['wp-edit-blocks'],
            '3.6.3'
        );

        $gutenbergBlocks = [
            'availability' => [
                'callback' => [Availability::class, 'renderAvailability'],
                'version' => '4.1.3',
            ],
            'contactform' => [
                'callback' => [ContactForm::class, 'renderContactForm'],
                'version' => '4.1.3',
            ],
            'onlinebooking' => [
                'callback' => [OnlineBooking::class, 'renderOnlineBooking'],
                'version' => '4.1.3',
            ],
            'package' => [
                'callback' => [Arrangement::class, 'renderPackage'],
                'version' => '4.1.3',
            ],
            'product' => [
                'callback' => [Products::class, 'renderProduct'],
                'version' => '4.1.3',
            ],
            'voucher-info' => [
                'callback' => [Vouchers::class, 'renderVoucherInfo'],
                'version' => '4.1.3',
            ],
            'voucher-sales' => [
                'callback' => [Vouchers::class, 'renderVoucherSales'],
                'version' => '4.4.0',
            ],
        ];
        foreach ($gutenbergBlocks as $key => $block) {
            $handle = 'recras-gutenberg-' . $key;
            wp_register_script(
                $handle,
                plugins_url('js/gutenberg-' . $key . '.js', __DIR__),
                [$globalScriptName],
                $block['version'],
                true
            );
            // Translations for these scripts are already handled by the global script

            \register_block_type('recras/' . $key, [
                'editor_script' => 'recras-gutenberg-' . $key,
                'editor_style' => $globalStyleName,
                'render_callback' => $block['callback'],
            ]);
        }
    }

    public static function addCategory($categories)
    {
        $categories[] = [
            'slug' => 'recras',
            'title' => 'Recras',
        ];
        return $categories;
    }

    public static function addEndpoints()
    {
        $routes = [
            'contactforms' => 'getContactForms',
            'packages' => 'getPackages',
            'products' => 'getProducts',
            'vouchers' => 'getVouchers',
        ];
        foreach ($routes as $uri => $callback) {
            register_rest_route(
                self::ENDPOINT_NAMESPACE,
                '/' . $uri,
                [
                    'methods' => 'GET',
                    'callback' => [__CLASS__, $callback],
                    'permission_callback' => function () {
                        return current_user_can('edit_posts');
                    },
                ]
            );
        }
    }

    public static function getContactForms()
    {
        $model = new ContactForm;
        return $model->getForms(get_option('recras_subdomain'));
    }

    public static function getPackages()
    {
        $model = new Arrangement;
        return $model->getArrangements(get_option('recras_subdomain'), false, false);
    }

    public static function getProducts()
    {
        $model = new Products;
        return $model->getProducts(get_option('recras_subdomain'));
    }

    public static function getVouchers()
    {
        $model = new Vouchers;
        return $model->getTemplates(get_option('recras_subdomain'));
    }
}
