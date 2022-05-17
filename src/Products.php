<?php
namespace Recras;

class Products
{
    private const SHOW_DEFAULT = 'title';

    /**
     * Add the [recras-product] shortcode
     */
    public static function renderProduct(array $attributes): string
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
                if (!$product->afbeelding_href) {
                    return '';
                }
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
    public static function clearCache(): int
    {
        global $recrasPlugin;

        $subdomain = get_option('recras_subdomain');
        $errors = 0;
        if ($recrasPlugin->transients->get($subdomain . '_products_v2')) {
            $errors = $recrasPlugin->transients->delete($subdomain . '_products_v2');
        }

        return $errors;
    }


    private static function displayname(\stdClass $json): string
    {
        if ($json->weergavenaam) {
            return $json->weergavenaam;
        }
        return $json->naam;
    }


    /**
     * Get duration of a product
     */
    private static function getDuration(\stdClass $product): string
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
     * @return array|string
     */
    public static function getProducts(string $subdomain)
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
     */
    public static function getValidOptions(): array
    {
        return ['description', 'description_long', 'duration', 'image_tag', 'image_url', 'minimum_amount', 'price_excl_vat', 'price_incl_vat', 'title'];
    }


    /**
     * Show the TinyMCE shortcode generator product form
     */
    public static function showForm(): void
    {
        require_once(__DIR__ . '/../editor/form-product.php');
    }

}
