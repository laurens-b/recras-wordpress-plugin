<?php
namespace Recras;

class OnlineBooking
{
    /**
     * Add the [recras-booking] shortcode
     *
     * @param array $attributes
     *
     * @return string
     */
    public static function renderOnlineBooking($attributes)
    {
        if (isset($attributes['id']) && !ctype_digit($attributes['id']) && !is_int($attributes['id'])) {
            return __('Error: ID is not a number', Plugin::TEXT_DOMAIN);
        }

        $subdomain = Settings::getSubdomain($attributes);
        if (!$subdomain) {
            return Plugin::getNoSubdomainError();
        }

        $arrangementID = isset($attributes['id']) ? $attributes['id'] : null;
        if (!$arrangementID && isset($_GET['package'])) {
            $arrangementID = $_GET['package'];
        }

        $useJSLibrary = isset($attributes['use_new_library']) ? (!!$attributes['use_new_library']) : false;

        if (!$useJSLibrary) {
            $enableResize = !isset($attributes['autoresize']) || (!!$attributes['autoresize'] === true);
            return self::generateIframe($subdomain, $arrangementID, $enableResize);
        }

        if (isset($attributes['prefill_date'])) {
            if (!preg_match('/^\d{4}-([0]\d|1[0-2])-([0-2]\d|3[01])$/', $attributes['prefill_date'])) {
                return __('Error: "prefill_date" is not a valid ISO 8601 string', Plugin::TEXT_DOMAIN);
            }

            $today = date('Y-m-d');
            if ($attributes['prefill_date'] < $today) {
                return __('Error: "prefill_date" is a date in the past', Plugin::TEXT_DOMAIN);
            }
        }
        if (isset($attributes['prefill_time'])) {
            if (!preg_match('/^(2[0-3]|[01]?[0-9]):([0-5]?[0-9])$/', $attributes['prefill_time'])) {
                return __('Error: "prefill_time" is not a valid time string (e.g. 14:30)', Plugin::TEXT_DOMAIN);
            }
        }

        $libraryOptions = [
            'previewTimes' => isset($attributes['show_times']) ? (!!$attributes['show_times']) : false,
            'redirect' => isset($attributes['redirect']) ? $attributes['redirect'] : null,
            'prefillDate' => isset($attributes['prefill_date']) ? $attributes['prefill_date'] : null,
            'prefillTime' => isset($attributes['prefill_time']) ? $attributes['prefill_time'] : null,
        ];

        if ((int) $arrangementID === 0 && isset($attributes['package_list'])) {
            if (is_string($attributes['package_list'])) {
                $packages = json_decode($attributes['package_list']);
                if (!is_array($packages)) {
                    $packages = explode(',', $attributes['package_list']);
                }
                if (is_array($packages) && count($packages) > 0) {
                    $libraryOptions['packageList'] = $packages;
                }
            } else if (is_array($attributes['package_list'])) {
                if (count($attributes['package_list']) > 0) {
                    $libraryOptions['packageList'] = $attributes['package_list'];
                }
            }
        }
        if ((int) $arrangementID !== 0 || (isset($libraryOptions['packageList'])) && count($libraryOptions['packageList']) === 1) {
            if (isset($attributes['product_amounts'])) {
                $preFillAmounts = json_decode($attributes['product_amounts'], true);
                if (!$preFillAmounts) {
                    return __('Error: "product_amounts" is invalid', Plugin::TEXT_DOMAIN);
                }
                $libraryOptions['preFillAmounts'] = $preFillAmounts;
            }
        }

        return self::generateBookingForm($subdomain, $arrangementID, $libraryOptions);
    }


    private static function generateBookingForm($subdomain, $arrangementID, $libraryOptions)
    {
        $generatedDivID = uniqid('V');
        $extraOptions = [];

        $countryCode = ContactForm::getDefaultCountry();
        if (is_string($countryCode)) {
            $extraOptions[] = "defaultCountry: '" . $countryCode . "'";
        }

        if ($arrangementID) {
            $extraOptions[] = 'package_id: ' . $arrangementID;
        } else if (isset($libraryOptions['packageList'])) {
            $extraOptions[] = 'package_id: [' . join(',', $libraryOptions['packageList']) . ']';
        }

        if ($libraryOptions['redirect']) {
            $extraOptions[] = "redirect_url: '" . $libraryOptions['redirect'] . "'";
        }

        if (isset($libraryOptions['preFillAmounts']) && count($libraryOptions['preFillAmounts']) > 0) {
            $extraOptions[] = 'productAmounts: ' . json_encode($libraryOptions['preFillAmounts']);
        }
        if (isset($libraryOptions['prefillDate'])) {
            $extraOptions[] = 'date: "' . $libraryOptions['prefillDate'] . '"';
        }
        if (isset($libraryOptions['prefillTime'])) {
            $extraOptions[] = 'time: "' . $libraryOptions['prefillTime'] . '"';
        }

        if (Analytics::useAnalytics()) {
            $extraOptions[] .= 'analytics: true';
        }

        return "
<div id='" . $generatedDivID . "'></div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var bookingOptions = new RecrasOptions({
        recras_hostname: '" . $subdomain . ".recras.nl',
        element: document.getElementById('" . $generatedDivID . "'),
        locale: '" . Settings::externalLocale() . "',
        autoScroll: false,
        previewTimes: " . ($libraryOptions['previewTimes'] ? 'true' : 'false') . ",
    " . join(",\n", $extraOptions) . "});
    new RecrasBooking(bookingOptions);
});
</script>";
    }

    private static function generateIframe($subdomain, $arrangementID, $enableResize)
    {
        $url = 'https://' . $subdomain . '.recras.nl/onlineboeking';
        if ($arrangementID) {
            $url .= '/step1/arrangement/' . $arrangementID;
        }

        $iframeUID = uniqid('robi'); // Recras Online Boeking Iframe
        $html = '';
        $html .= '<iframe src="' . $url . '" style="width:100%;height:450px" frameborder=0 scrolling="auto" id="' . $iframeUID . '"></iframe>';
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
     * Show the TinyMCE shortcode generator contact form
     */
    public static function showForm()
    {
        require_once(__DIR__ . '/../editor/form-booking.php');
    }
}
