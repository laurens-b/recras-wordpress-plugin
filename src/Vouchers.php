<?php
namespace Recras;

class Vouchers
{
    const SHOW_DEFAULT = 'name';

    public static function renderVoucherInfo($attributes)
    {
        if (!isset($attributes['id'])) {
            return __('Error: no ID set', Plugin::TEXT_DOMAIN);
        }
        if (isset($attributes['id']) && !ctype_digit($attributes['id']) && !is_int($attributes['id'])) {
            return __('Error: ID is not a number', Plugin::TEXT_DOMAIN);
        }

        $subdomain = Settings::getSubdomain($attributes);
        if (!$subdomain) {
            return Plugin::getNoSubdomainError();
        }

        $model = new self;
        $templates = $model->getTemplates($subdomain);

        if (!isset($templates[$attributes['id']])) {
            return __('Error: template does not exist', Plugin::TEXT_DOMAIN);
        }

        $showProperty = self::SHOW_DEFAULT;
        if (isset($attributes['show']) && in_array($attributes['show'], self::getValidOptions())) {
            $showProperty = $attributes['show'];
        }

        $template = $templates[$attributes['id']];
        switch ($showProperty) {
            case 'name':
                return '<span class="recras-title">' . $template->name . '</span>';
            case 'price':
                return Price::format($template->price);
            case 'validity':
                return $template->expire_days;
            default:
                return __('Error: unknown option', Plugin::TEXT_DOMAIN);
        }
    }

    /**
     * Add the [recras-vouchers] shortcode
     *
     * @param array $attributes
     *
     * @return string
     */
    public static function renderVoucherSales($attributes)
    {
        if (isset($attributes['id']) && !ctype_digit($attributes['id']) && !is_int($attributes['id'])) {
            return __('Error: ID is not a number', Plugin::TEXT_DOMAIN);
        }

        $subdomain = Settings::getSubdomain($attributes);
        if (!$subdomain) {
            return Plugin::getNoSubdomainError();
        }

        $extraOptions = [];
        if (isset($attributes['id'])) {
            $extraOptions[] = 'voucher_template_id: ' . $attributes['id'];
        }

        if (isset($attributes['redirect'])) {
            $extraOptions[] = "redirect_url: '" . $attributes['redirect'] . "'";
        }

        if (Analytics::useAnalytics()) {
            $extraOptions[] = 'analytics: true';
        }

        $generatedDivID = uniqid('V');

        return "
<div id='" . $generatedDivID . "'></div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var voucherOptions = new RecrasOptions({
            recras_hostname: '" . $subdomain . ".recras.nl',
            element: document.getElementById('" . $generatedDivID . "'),
            locale: '" . Settings::externalLocale() . "',
        " . join(",\n", $extraOptions) . "});
        new RecrasVoucher(voucherOptions);
    });
</script>";
    }


    /**
     * Clear voucher template cache (transients)
     */
    public static function clearCache()
    {
        global $recrasPlugin;

        $subdomain = get_option('recras_subdomain');
        $errors = 0;
        if ($recrasPlugin->transients->get($subdomain . '_voucher_templates')) {
            $errors = $recrasPlugin->transients->delete($subdomain . '_voucher_templates');
        }

        header('Location: ' . admin_url('admin.php?page=' . Settings::PAGE_CACHE . '&msg=' . Plugin::getStatusMessage($errors)));
        exit;
    }


    public function getTemplates($subdomain)
    {
        global $recrasPlugin;

        $json = $recrasPlugin->transients->get($subdomain . '_voucher_templates');
        if ($json === false) {
            try {
                $json = Http::get($subdomain, 'voucher_templates');
            } catch (\Exception $e) {
                return $e->getMessage();
            }
            $recrasPlugin->transients->set($subdomain . '_voucher_templates', $json);
        }

        $templates = [];
        foreach ($json as $template) {
            if ($template->contactform_id) {
                $templates[$template->id] = $template;
            }
        }
        return $templates;
    }


    /**
     * Get all valid options for the "show" argument
     *
     * @return array
     */
    public static function getValidOptions()
    {
        return ['name', 'price', 'validity']; //TODO: decide on product_amount, products
    }


    /**
     * Show the TinyMCE shortcode generator forms
     */
    public static function showInfoForm()
    {
        require_once(__DIR__ . '/../editor/form-voucher-info.php');
    }
    public static function showSalesForm()
    {
        require_once(__DIR__ . '/../editor/form-voucher-sales.php');
    }
}
