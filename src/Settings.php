<?php
namespace Recras;

class Settings
{
    const OPTION_PAGE = 'recras';
    const OPTION_SECTION = 'recras';
    const PAGE_CACHE = 'recras-clear-cache';
    const PAGE_DOCS = 'recras-documentation';


    public static function addInputAnalytics($args)
    {
        self::addInputCheckbox($args);
        self::infoText(__('Enabling this will send online booking and voucher sales events to Google Analytics.', Plugin::TEXT_DOMAIN));
    }


    /**
     * Add a currency input field
     *
     * @param array $args
     */
    public static function addInputCurrency($args)
    {
        $field = $args['field'];
        $value = get_option($field);
        if (!$value) {
            $value = '€';
        }

        printf('<input type="text" name="%s" id="%s" value="%s">', $field, $field, $value);
    }


    /**
     * Add a checkbox option
     *
     * @param array $args
     */
    public static function addInputCheckbox($args)
    {
        $field = $args['field'];
        $value = get_option($field);

        printf('<input type="checkbox" name="%s" id="%s" value="1"%s>', $field, $field, ($value ? ' checked' : ''));
    }

    public static function addInputDatepicker($args)
    {
        self::addInputCheckbox($args);
        self::infoText(__('Not all browsers have a built-in date picker. Enable this to use a custom widget.', Plugin::TEXT_DOMAIN));
    }


    /**
     * Add a decimal separator input field
     *
     * @param array $args
     */
    public static function addInputDecimal($args)
    {
        $field = $args['field'];
        $value = get_option($field);
        if (!$value) {
            $value = '.';
        }

        printf('<input type="text" name="%s" id="%s" value="%s" size="2" maxlength="1">', $field, $field, $value);
        self::infoText(__('Used in prices, such as 100,00.', Plugin::TEXT_DOMAIN));
    }


    /**
     * Add a subdomain input field
     *
     * @param array $args
     */
    public static function addInputSubdomain($args)
    {
        $field = $args['field'];
        $value = get_option($field);
        if (!$value) {
            $value = 'demo';
        }

        printf('<input type="text" name="%s" id="%s" value="%s">.recras.nl', $field, $field, $value);
    }


    public static function addInputTheme($args)
    {
        $themes = self::getThemes();

        $field = $args['field'];
        $value = get_option($field);
        if (!$value) {
            $value = 'none';
        }

        $html = '<select name="' . $field . '" id="' . $field . '">';
        foreach ($themes as $key => $theme) {
            $selText = '';
            if ($value === $key) {
                $selText = ' selected';
            }
            $html .= '<option value="' . $key . '"' . $selText . '>' . $theme['name'];
        }
        $html .= '</select>';
        echo $html;
    }


    public static function clearCache()
    {
        if (!current_user_can('edit_pages')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        require_once('admin/cache.php');
    }


    public static function documentation()
    {
        if (!current_user_can('edit_pages')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        require_once('admin/documentation.php');
    }


    /**
     * Load the admin options page
     */
    public static function editSettings()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        require_once('admin/settings.php');
    }


    public static function errorNoRecrasName()
    {
        echo '<p class="recrasInfoText">';
        $settingsLink = admin_url('admin.php?page=' . self::OPTION_PAGE);
        printf(
            __('Please enter your Recras name in the %s before adding widgets.', Plugin::TEXT_DOMAIN),
            '<a href="' . $settingsLink . '" target="_blank">' . __('Recras → Settings menu', Plugin::TEXT_DOMAIN) . '</a>'
        );
        echo '</p>';
    }

    /**
     * This returns a valid locale, based on the locale set for WordPress, to use in "external" Recras scripts
     *
     * @return string
     */
    public static function externalLocale()
    {
        $localeShort = substr(get_locale(), 0, 2);
        switch ($localeShort) {
            case 'de':
                return 'de_DE';
            case 'fy':
            case 'nl':
                return 'nl_NL';
            default:
                return 'en_GB';
        }
    }


    /**
     * Get the Recras subdomain, which can be set in the shortcode attributes or as global setting
     *
     * @param array $attributes
     *
     * @return string
     */
    public static function getSubdomain($attributes)
    {
        if (isset($attributes['recrasname'])) {
            return $attributes['recrasname'];
        } else {
            return get_option('recras_subdomain');
        }
    }


    public static function getThemes()
    {
        return [
            'none' => [
                'name' => __('No theme', Plugin::TEXT_DOMAIN),
                'version' => null,
            ],
            'basic' => [
                'name' => __('Basic theme', Plugin::TEXT_DOMAIN),
                'version' => '3.4.3',
            ],
            'recrasblue' => [
                'name' => __('Recras Blue', Plugin::TEXT_DOMAIN),
                'version' => '3.4.3',
            ],
        ];
    }


    private static function infoText($text)
    {
        echo '<p class="description">' . $text . '</p>';
    }


    /**
     * Parse a boolean value
     *
     * @param string $value
     *
     * @return bool
     */
    public static function parseBoolean($value)
    {
        $bool = true;
        if (isset($value) && ($value == 'false' || $value == 0 || $value == 'no')) {
            $bool = false;
        }
        return $bool;
    }

    private static function registerSetting($name, $default, $type = 'string', $sanitizeCallback = null)
    {
        $options = [
            'default' => $default,
            'type' => $type,
        ];
        if ($sanitizeCallback) {
            $options['sanitize_callback'] = $sanitizeCallback;
        }
        register_setting('recras', $name, $options);
    }

    private static function addField($name, $title, $inputFn)
    {
        add_settings_field($name, $title, $inputFn, self::OPTION_PAGE, self::OPTION_SECTION, [
            'field' => $name,
            'label_for' => $name,
        ]);
    }

    /**
     * Register plugin settings
     */
    public static function registerSettings()
    {
        add_settings_section(
            self::OPTION_SECTION,
            __('Recras settings', Plugin::TEXT_DOMAIN),
            [__CLASS__, 'settingsHelp'],
            self::OPTION_PAGE
        );

        self::registerSetting('recras_subdomain', 'demo', 'string', [__CLASS__, 'sanitizeSubdomain']);
        self::registerSetting('recras_currency', '€');
        self::registerSetting('recras_decimal', ',');
        self::registerSetting('recras_datetimepicker', false, 'boolean');
        self::registerSetting('recras_theme', 'none');
        self::registerSetting('recras_enable_analytics', false, 'boolean');

        self::addField('recras_subdomain', __('Recras name', Plugin::TEXT_DOMAIN), [__CLASS__, 'addInputSubdomain']);
        self::addField('recras_currency', __('Currency symbol', Plugin::TEXT_DOMAIN), [__CLASS__, 'addInputCurrency']);
        self::addField('recras_decimal', __('Decimal separator', Plugin::TEXT_DOMAIN), [__CLASS__, 'addInputDecimal']);
        self::addField('recras_datetimepicker', __('Use calendar widget', Plugin::TEXT_DOMAIN), [__CLASS__, 'addInputDatepicker']);
        self::addField('recras_theme', __('Theme for online booking', Plugin::TEXT_DOMAIN), [__CLASS__, 'addInputTheme']);
        self::addField('recras_enable_analytics', __('Enable Google Analytics integration?', Plugin::TEXT_DOMAIN), [__CLASS__, 'addInputAnalytics']);
    }


    /**
     * Sanitize user inputted subdomain
     *
     * @param string $subdomain
     *
     * @return bool|string
     */
    public function sanitizeSubdomain($subdomain)
    {
        // RFC 1034 section 3.5 - http://tools.ietf.org/html/rfc1034#section-3.5
        if (strlen($subdomain) > 63) {
            return false;
        }
        if (! preg_match('/^[a-zA-Z0-9](?:[a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?$/', $subdomain)) {
            return false;
        }
        return $subdomain;
    }


    /**
     * Echo settings helper text
     */
    public static function settingsHelp()
    {
        printf(
            __('For more information on these options, please see the %s page.', Plugin::TEXT_DOMAIN),
            '<a href="' . admin_url('admin.php?page=' . self::PAGE_DOCS) . '">' . __('Documentation', Plugin::TEXT_DOMAIN) . '</a>'
        );
    }
}
