<?php
namespace Recras;


class Availability
{
    /**
     * Add the [recras-availability] shortcode
     */
    public static function renderAvailability(array $attributes): string
    {
        if (!isset($attributes['id'])) {
            return __('Error: no ID set', Plugin::TEXT_DOMAIN);
        }
        if (!ctype_digit($attributes['id']) && !is_int($attributes['id'])) {
            return __('Error: ID is not a number', Plugin::TEXT_DOMAIN);
        }


        $subdomain = Settings::getSubdomain($attributes);
        if (!$subdomain) {
            return Plugin::getNoSubdomainError();
        }
        $enableResize = !isset($attributes['autoresize']) || (!!$attributes['autoresize'] === true);


        // We don't need this data, but it's useful to check if the package actually exists
        Arrangement::getPackage($subdomain, $attributes['id']);


        $url = 'https://' . $subdomain . '.recras.nl/api/arrangementbeschikbaarheid/id/' . $attributes['id'];
        $iframeUID = uniqid('rpai'); // Recras Package Availability Iframe
        $html = '';
        $html .= '<iframe src="' . $url . '" style="width:100%;height:250px" frameborder=0 scrolling="auto" id="' . $iframeUID . '"></iframe>';
        if ($enableResize) {
            $html .= <<<SCRIPT
<script>
    window.addEventListener('message', function(e) {
        var origin = e.origin || e.originalEvent.origin;
        if (origin.match(/{$subdomain}\.recras\.nl/)) {
            document.getElementById('{$iframeUID}').style.height = e.data.iframeHeight + 'px';
        }
    });
</script>
SCRIPT;
        }
        return $html;
    }


    /**
     * Show the TinyMCE shortcode generator arrangement form
     */
    public static function showForm()
    {
        require_once(__DIR__ . '/../editor/form-package-availability.php');
    }
}
