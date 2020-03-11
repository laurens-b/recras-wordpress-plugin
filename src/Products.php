<?php
namespace Recras;

class Products
{
    const SHOW_DEFAULT = 'title';

    /**
     * Add the [recras-product] shortcode
     *
     * @param array $attributes
     *
     * @return string
     */
    public static function renderProduct($attributes)
    {
        if (!isset($attributes['id'])) {
            return __('Error: no ID set', Plugin::TEXT_DOMAIN);
        }
        if (!ctype_digit($attributes['id']) && !is_int($attributes['id'])) {
            return __('Error: ID is not a number', Plugin::TEXT_DOMAIN);
        }
        $showProperty = self::SHOW_DEFAULT;
        if (isset($attributes['show']) && in_array($attributes['show'], self::getValidOptions())) {
            $showProperty = $attributes['show'];
        }

        $subdomain = Settings::getSubdomain($attributes);
        if (!$subdomain) {
            return Plugin::getNoSubdomainError();
        }

        $products = self::getProducts($subdomain);
        if (!isset($products[$attributes['id']])) {
            return __('Error: product does not exist', Plugin::TEXT_DOMAIN);
        }
        $product = $products[$attributes['id']];

        switch ($showProperty) {
            case 'description':
                return '<span class="recras-description">' . $product->beschrijving_klant . '</span>';
            case 'description_long':
                if ($product->uitgebreide_omschrijving) {
                    return '<span class="recras-description">' . $product->uitgebreide_omschrijving . '</span>';
                } else {
                    return '';
                }
            case 'duration':
                return self::getDuration($product);
            case 'image_tag':
                if (!$product->afbeelding_href) {
                    return '';
                }
                return '<img src="' . $product->afbeelding_href . '" alt="' . htmlspecialchars(self::displayname($product)) . '">';
            case 'image_url':
                return $product->afbeelding_href;
            case 'minimum_amount':
                return '<span class="recras-amount">' . $product->minimum_aantal . '</span>';
            case 'price_excl_vat':
                return 'Price excl. VAT is not supported anymore. Price incl. VAT is ' . Price::format($product->verkoop);
            case 'price_incl_vat':
                return Price::format($product->verkoop);
            case 'title':
                return '<span class="recras-title">' . self::displayname($product) . '</span>';
            default:
                return __('Error: unknown option', Plugin::TEXT_DOMAIN);
        }
    }


    /**
     * Clear product cache (transients)
     */
    public static function clearCache()
    {
        global $recrasPlugin;

        $subdomain = get_option('recras_subdomain');

        $errors = 0;
        if ($recrasPlugin->transients->get($subdomain . '_products_v2')) {
            $errors = $recrasPlugin->transients->delete($subdomain . '_products_v2');
        }

        header('Location: ' . admin_url('admin.php?page=' . Settings::PAGE_CACHE . '&msg=' . Plugin::getStatusMessage($errors)));
        exit;
    }


    private static function displayname($json)
    {
        if ($json->weergavenaam) {
            return $json->weergavenaam;
        }
        return $json->naam;
    }


    /**
     * Get duration of a product
     *
     * @param object $product
     *
     * @return string
     */
    private static function getDuration($product)
    {
        if (!$product->duur) {
            return '';
        }

        try {
            $duration = new \DateInterval($product->duur);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return '<span class="recras-duration">' . $duration->format('%h:%I') . '</span>';
    }


    /**
     * Get products from the Recras API
     *
     * @param string $subdomain
     *
     * @return array|string
     */
    public static function getProducts($subdomain)
    {
        global $recrasPlugin;

        $json = $recrasPlugin->transients->get($subdomain . '_products_v2');
        if ($json === false) {
            try {
                $json = Http::get($subdomain, 'producten');
            } catch (\Exception $e) {
                return $e->getMessage();
            }

            $recrasPlugin->transients->set($subdomain . '_products_v2', $json);
        }

        $products = [];
        foreach ($json as $product) {
            $products[$product->id] = $product;
        }
        // Sort alphabetically
        uksort($products, function($a, $b) use ($products) {
            return strcmp($products[$a]->weergavenaam, $products[$b]->weergavenaam);
        });
        return $products;
    }


    /**
     * Get all valid options for the "show" argument
     *
     * @return array
     */
    public static function getValidOptions()
    {
        return ['description', 'description_long', 'duration', 'image_tag', 'image_url', 'minimum_amount', 'price_excl_vat', 'price_incl_vat', 'title'];
    }


    /**
     * Show the TinyMCE shortcode generator product form
     */
    public static function showForm()
    {
        require_once(__DIR__ . '/../editor/form-product.php');
    }

}
