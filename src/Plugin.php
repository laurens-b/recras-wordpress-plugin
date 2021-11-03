<?php
namespace Recras;

class Plugin
{
    const LIBRARY_VERSION = '1.10.2';
    const TEXT_DOMAIN = 'recras';

    const SHORTCODE_BOOK_PROCESS = 'recras-bookprocess';
    const SHORTCODE_ONLINE_BOOKING = 'recras-booking';
    const SHORTCODE_VOUCHER_SALES = 'recras-vouchers';
    const SHORTCODE_VOUCHER_INFO = 'recras-voucher-info';

    public $baseUrl;


    /**
     * Init all the things!
     */
    public function __construct()
    {
        $this->setBaseUrl();
        $this->transients = new Transient;

        // Init Localisation
        load_default_textdomain();
        load_plugin_textdomain($this::TEXT_DOMAIN, false, dirname(plugin_basename(__DIR__)) . '/lang');

        // Add admin menu pages
        add_action('admin_menu', [&$this, 'addMenuItems']);

        add_action('admin_init', [Settings::class, 'registerSettings']);
        add_action('admin_init', [Editor::class, 'addButtons']);

        if (function_exists('register_block_type')) {
            add_action('init', [Gutenberg::class, 'addBlocks']);
            add_action('rest_api_init', [Gutenberg::class, 'addEndpoints']);
            $wpVersion = get_bloginfo('version');
            if ($wpVersion >= '5.8') {
                add_filter('block_categories_all', [Gutenberg::class, 'addCategory']);
            } else {
                add_filter('block_categories', [Gutenberg::class, 'addCategory']);
            }
        }

        add_action('admin_enqueue_scripts', [$this, 'loadAdminScripts']);
        add_action('wp_enqueue_scripts', [$this, 'loadScripts']);

        // Clear caches
        add_action('admin_post_clear_recras_cache', [$this, 'clearCache']);

        $this->addShortcodes();

        register_uninstall_hook(__FILE__, [__CLASS__, 'uninstall']);
    }

    private function addClassicEditorSubmenuPage($title, $slug, $callable)
    {
        add_submenu_page(
            null,
            $title,
            null,
            'publish_posts',
            $slug,
            $callable
        );
    }

    /**
     * Add the menu items for our plugin
     */
    public function addMenuItems()
    {
        $mainPage = current_user_can('manage_options') ? 'recras' : Settings::PAGE_CACHE;
        add_menu_page('Recras', 'Recras', 'edit_pages', $mainPage, '', plugin_dir_url(__DIR__) . 'logo.svg', 58);

        if (current_user_can('manage_options')) {
            add_submenu_page(
                'recras',
                __('Settings', $this::TEXT_DOMAIN),
                __('Settings', $this::TEXT_DOMAIN),
                'manage_options',
                'recras',
                ['\Recras\Settings', 'editSettings']
            );
        }

        add_submenu_page(
            'recras',
            __('Cache', $this::TEXT_DOMAIN),
            __('Cache', $this::TEXT_DOMAIN),
            'edit_pages',
            Settings::PAGE_CACHE,
            ['\Recras\Settings', 'clearCache']
        );
        add_submenu_page(
            'recras',
            __('Documentation', $this::TEXT_DOMAIN),
            __('Documentation', $this::TEXT_DOMAIN),
            'edit_pages',
            Settings::PAGE_DOCS,
            ['\Recras\Settings', 'documentation']
        );
        add_submenu_page(
            'recras',
            __('Shortcodes', $this::TEXT_DOMAIN),
            __('Shortcodes', $this::TEXT_DOMAIN),
            'edit_pages',
            Settings::PAGE_SHORTCODES,
            ['\Recras\Settings', 'shortcodes']
        );

        $this->addClassicEditorSubmenuPage(__('Package', $this::TEXT_DOMAIN), 'form-arrangement', [Arrangement::class, 'showForm']);
        $this->addClassicEditorSubmenuPage(__('Book process', $this::TEXT_DOMAIN) . ' (beta)', 'form-bookprocess', [Bookprocess::class, 'showForm']);
        $this->addClassicEditorSubmenuPage(__('Contact form', $this::TEXT_DOMAIN), 'form-contact', [ContactForm::class, 'showForm']);
        $this->addClassicEditorSubmenuPage(__('Online booking', $this::TEXT_DOMAIN), 'form-booking', [OnlineBooking::class, 'showForm']);
        $this->addClassicEditorSubmenuPage(__('Product', $this::TEXT_DOMAIN), 'form-product', [Products::class, 'showForm']);
        $this->addClassicEditorSubmenuPage(__('Voucher sales', $this::TEXT_DOMAIN), 'form-voucher-sales', [Vouchers::class, 'showSalesForm']);
        $this->addClassicEditorSubmenuPage(__('Voucher info', $this::TEXT_DOMAIN), 'form-voucher-info', [Vouchers::class, 'showInfoForm']);
    }


    /**
     * Register our shortcodes
     */
    public function addShortcodes()
    {
        add_shortcode('recras-availability', [Availability::class, 'renderAvailability']);
        add_shortcode($this::SHORTCODE_ONLINE_BOOKING, [OnlineBooking::class, 'renderOnlineBooking']);
        add_shortcode($this::SHORTCODE_BOOK_PROCESS, [Bookprocess::class, 'renderBookprocess']);
        add_shortcode('recras-contact', [ContactForm::class, 'renderContactForm']);
        add_shortcode('recras-package', [Arrangement::class, 'renderPackage']);
        add_shortcode('recras-product', [Products::class, 'renderProduct']);
        add_shortcode($this::SHORTCODE_VOUCHER_SALES, [Vouchers::class, 'renderVoucherSales']);
        add_shortcode($this::SHORTCODE_VOUCHER_INFO, [Vouchers::class, 'renderVoucherInfo']);
    }


    public static function clearCache()
    {
        Arrangement::clearCache();
        ContactForm::clearCache();
        Products::clearCache();
        Vouchers::clearCache();
    }


    /**
     * Get error message if no subdomain has been entered yet
     * @return string
     */
    public static function getNoSubdomainError()
    {
        if (current_user_can('manage_options')) {
            return __('Error: you have not set your Recras name yet', Plugin::TEXT_DOMAIN);
        } else {
            return __('Error: your Recras name has not been set yet, but you do not have the permission to set this. Please ask your site administrator to do this for you.', Plugin::TEXT_DOMAIN);
        }
    }


    /**
     * @param int $errors
     *
     * @return string
     */
    public static function getStatusMessage($errors)
    {
        return ($errors === 0 ? 'success' : 'error');
    }


    /**
     * Load scripts for use in the WP admin
     */
    public function loadAdminScripts()
    {
        wp_register_script('recras-admin', $this->baseUrl . '/js/admin.js', [], '4.0.0', true);
        wp_localize_script('recras-admin', 'recras_l10n', [
            'contact_form' => __('Contact form', $this::TEXT_DOMAIN),
            'no_connection' => __('Could not connect to your Recras', $this::TEXT_DOMAIN),
            'online_booking' => __('Online booking', $this::TEXT_DOMAIN),
            'bookprocess' => __('Book process', $this::TEXT_DOMAIN) . ' (beta)',
            'package' => __('Package', $this::TEXT_DOMAIN),
            'package_availability' => __('Package availability', $this::TEXT_DOMAIN),
            'product' => __('Product', $this::TEXT_DOMAIN),
            'voucherInfo' => __('Voucher info', $this::TEXT_DOMAIN),
            'voucherSales' => __('Voucher sales', $this::TEXT_DOMAIN),
        ]);
        wp_enqueue_script('recras-admin');
        wp_enqueue_style('recras-admin-style', $this->baseUrl . '/css/admin-style.css', [], '2.5.0');
        wp_enqueue_script('wp-api');
    }

    public static function deferJSLibrary($tag, $handle)
    {
        $deferHandles = ['recrasjspolyfill', 'recrasjslibrary'];
        if (!in_array($handle, $deferHandles)) {
            return $tag;
        }
        return str_replace(' src=', ' defer src=', $tag);
    }

    /**
     * Load the general script and localisation
     */
    public function loadScripts()
    {
        $localisation = [
            'checkboxRequired' => __('At least one choice is required', $this::TEXT_DOMAIN),
            'loading' => __('Loading...', $this::TEXT_DOMAIN),
            'sent_success' => __('Your message was sent successfully', $this::TEXT_DOMAIN),
            'sent_error' => __('There was an error sending your message', $this::TEXT_DOMAIN),
        ];

        // Add Pikaday scripts and Pikaday localisation if the site has "Use calendar widget" enabled
        if ($value = get_option('recras_datetimepicker')) {
            wp_enqueue_script('pikaday', 'https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.8.2/pikaday.min.js', [], false, true); // ver=false because it's already in the URL
            wp_enqueue_style('pikaday', 'https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.8.2/css/pikaday.min.css', [], false); // ver=false because it's already in the URL

            $localisation['pikaday'] = [
                'previousMonth' => __('Previous month', $this::TEXT_DOMAIN),
                'nextMonth' => __('Next month', $this::TEXT_DOMAIN),
                'months' => [
                    __('January', $this::TEXT_DOMAIN),
                    __('February', $this::TEXT_DOMAIN),
                    __('March', $this::TEXT_DOMAIN),
                    __('April', $this::TEXT_DOMAIN),
                    __('May', $this::TEXT_DOMAIN),
                    __('June', $this::TEXT_DOMAIN),
                    __('July', $this::TEXT_DOMAIN),
                    __('August', $this::TEXT_DOMAIN),
                    __('September', $this::TEXT_DOMAIN),
                    __('October', $this::TEXT_DOMAIN),
                    __('November', $this::TEXT_DOMAIN),
                    __('December', $this::TEXT_DOMAIN),
                ],
                'weekdays' => [
                    __('Sunday', $this::TEXT_DOMAIN),
                    __('Monday', $this::TEXT_DOMAIN),
                    __('Tuesday', $this::TEXT_DOMAIN),
                    __('Wednesday', $this::TEXT_DOMAIN),
                    __('Thursday', $this::TEXT_DOMAIN),
                    __('Friday', $this::TEXT_DOMAIN),
                    __('Saturday', $this::TEXT_DOMAIN),
                ],
                'weekdaysShort' => [
                    __('Sun', $this::TEXT_DOMAIN),
                    __('Mon', $this::TEXT_DOMAIN),
                    __('Tue', $this::TEXT_DOMAIN),
                    __('Wed', $this::TEXT_DOMAIN),
                    __('Thu', $this::TEXT_DOMAIN),
                    __('Fri', $this::TEXT_DOMAIN),
                    __('Sat', $this::TEXT_DOMAIN),
                ],
            ];
        }

        // Defer certain scripts
        add_filter('script_loader_tag', [$this, 'deferJSLibrary'], 10, 2);

        // Polyfill for old browsers
        wp_enqueue_script('recrasjspolyfill', 'https://polyfill.io/v3/polyfill.min.js?features=default,fetch,Promise,Array.prototype.includes,RegExp.prototype.flags', [], null, false);
        wp_enqueue_script('recrasjslibrary', $this->baseUrl . '/js/onlinebooking.min.js', [], $this::LIBRARY_VERSION, false);

        // Online booking theme
        $theme = get_option('recras_theme');
        if ($theme) {
            $allowedThemes = Settings::getThemes();
            if ($theme !== 'none' && array_key_exists($theme, $allowedThemes)) {
                wp_enqueue_style('theme_' . $theme, $this->baseUrl . '/css/themes/' . $theme . '.css', [], $allowedThemes[$theme]['version']);
            }
        }

        // Generic functionality & localisation script
        $scriptName = 'recras-frontend';
        wp_register_script($scriptName, $this->baseUrl . '/js/recras.js', ['jquery'], '4.1.5', true);
        wp_localize_script($scriptName, 'recras_l10n', $localisation);
        wp_enqueue_script($scriptName);
    }


    /**
     * Set plugin base dir
     */
    public function setBaseUrl()
    {
        $this->baseUrl = rtrim(plugins_url('', __DIR__), '/');
    }

    public static function uninstall()
    {
        delete_option('recras_currency');
        delete_option('recras_datetimepicker');
        delete_option('recras_decimal');
        delete_option('recras_enable_analytics');
        delete_option('recras_subdomain');
        delete_option('recras_theme');
    }
}
