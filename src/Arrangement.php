<?php
namespace Recras;


class Arrangement
{
    const SHOW_DEFAULT = 'title';

    /**
     * Add the [recras-package] shortcode
     *
     * @param array $attributes
     *
     * @return string
     */
    public static function renderPackage($attributes)
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

        $json = self::getPackage($subdomain, $attributes['id']);

        switch ($showProperty) {
            case 'description':
                //Notice: Undefined property: stdClass::$uitgebreide_omschrijving in /home/sander/src/recras-wordpress-plugin/src/Arrangement.php on line 39
                return $json->uitgebreide_omschrijving;
            case 'duration':
                return self::getDuration($json);
            case 'image_tag':
                if (!$json->image_filename) {
                    return '';
                }
                return '<img src="https://' . $subdomain . '.recras.nl' . $json->image_filename . '" alt="' . htmlspecialchars(self::displayname($json)) . '">';
            case 'image_url':
                return $json->image_filename;
            case 'location':
                return self::getLocation($json);
            case 'persons':
                return '<span class="recras-persons">' . $json->aantal_personen . '</span>';
            case 'price_pp_excl_vat':
                return Price::format($json->prijs_pp_exc);
            case 'price_pp_incl_vat':
                return Price::format($json->prijs_pp_inc);
            case 'price_total_excl_vat':
                return Price::format($json->prijs_totaal_exc);
            case 'price_total_incl_vat':
                return Price::format($json->prijs_totaal_inc);
            case 'program':
            case 'programme':
                if (!isset($json->programma)) {
                    return __('Error: programme is empty', Plugin::TEXT_DOMAIN);
                }
                if (!is_array($json->programma)) {
                    $json->programma = (array) $json->programma;
                }
                if (empty($json->programma)) {
                    return __('Error: programme is empty', Plugin::TEXT_DOMAIN);
                }

                $startTime = (isset($attributes['starttime']) ? $attributes['starttime'] : '00:00');
                $showHeader = !isset($attributes['showheader']) || Settings::parseBoolean($attributes['showheader']);
                return self::generateProgramme($json->programma, $startTime, $showHeader);
            case 'title':
                return '<span class="recras-title">' . self::displayname($json) . '</span>';
            default:
                return __('Error: unknown option', Plugin::TEXT_DOMAIN);
        }
    }


    /**
     * Clear arrangement cache (transients)
     */
    public static function clearCache()
    {
        global $recrasPlugin;

        $subdomain = get_option('recras_subdomain');
        $errors = 0;

        $arrangements = array_keys(self::getArrangements($subdomain));
        foreach ($arrangements as $id) {
            $errors += $recrasPlugin->transients->delete($subdomain . '_arrangement_' . $id);
        }
        $errors += $recrasPlugin->transients->delete($subdomain . '_arrangements');

        return $errors;
    }


    private static function displayname($json)
    {
        if ($json->weergavenaam) {
            return $json->weergavenaam;
        }
        return $json->arrangement;
    }


    /**
     * Generate the programme for an arrangement
     *
     * @param array $programme
     * @param string $startTime
     * @param bool $showHeader
     *
     * @return string
     */
    public static function generateProgramme($programme, $startTime = '00:00', $showHeader = true)
    {
        $html = '<table class="recras-programme">';

        if ($showHeader) {
            $html .= '<thead>';
            $html .= '<tr><th>' . __('From', Plugin::TEXT_DOMAIN) . '<th>' . __('Until', Plugin::TEXT_DOMAIN) . '<th>' . __('Activity', Plugin::TEXT_DOMAIN);
            $html .= '</thead>';
        }

        $first = reset($programme);
        $last = end($programme);

        // Calculate how many days this programme spans - begin and eind are ISO8601 periods/intervals
        $startDatetime = new \DateTime($startTime);
        $startDatetime->add(new \DateInterval($first->begin));
        $endDatetime = new \DateTime($startTime);
        $endDatetime->add(new \DateInterval($last->eind));
        $isMultiDay = ($endDatetime->format('Ymd') > $startDatetime->format('Ymd'));

        $html .= '<tbody>';
        $lastDate = null;
        $day = 0;

        foreach ($programme as $activity) {
            if (!$activity->omschrijving) {
                continue;
            }
            $startDate = new \DateTime($startTime);
            $endDate = new \DateTime($startTime);
            $timeBegin = new \DateInterval($activity->begin);
            $timeEnd = new \DateInterval($activity->eind);
            $lineDate = $startDate->add($timeBegin);
            $startFormatted = $lineDate->format('H:i');
            if ($isMultiDay && (is_null($lastDate) || $lineDate > $lastDate)) {
                ++$day;
                $html .= '<tr class="recras-new-day"><th colspan="3">' . sprintf(__('Day %d', Plugin::TEXT_DOMAIN), $day);
            }

            $html .= '<tr><td>' . $startFormatted;
            $html .= '<td>' . $endDate->add($timeEnd)->format('H:i');
            $html .= '<td>' . $activity->omschrijving;
            $lastDate = $lineDate;
        }
        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }


    /**
     * Get arrangements from the Recras API
     *
     * @param string $subdomain
     * @param bool $onlyOnline
     *
     * @return array|string
     */
    public static function getArrangements($subdomain, $onlyOnline = false, $includeEmpty = true)
    {
        global $recrasPlugin;

        $json = $recrasPlugin->transients->get($subdomain . '_arrangements');
        if ($json === false) {
            try {
                $json = Http::get($subdomain, 'arrangementen');
            } catch (\Exception $e) {
                return $e->getMessage();
            }
            $recrasPlugin->transients->set($subdomain . '_arrangements', $json);
        }

        $arrangements = [];
        if ($includeEmpty) {
            $arrangements[0] = (object) [
                'arrangement' => '',
                'id' => null,
                'mag_online' => false,
            ];
        }
        foreach ($json as $arrangement) {
            if (!$onlyOnline || $arrangement->mag_online) {
                $arrangements[$arrangement->id] = $arrangement;
            }
        }
        return $arrangements;
    }


    /**
     * Get arrangements for a certain contact form from the Recras API
     *
     * @param string $subdomain
     * @param int $contactformID
     *
     * @return array|string
     */
    public function getArrangementsForContactForm($subdomain, $contactformID)
    {
        $form = ContactForm::getForm($subdomain, $contactformID);
        if (is_string($form)) {
            // Not a form, but an error
            return sprintf(__('Error: %s', Plugin::TEXT_DOMAIN), $form);
        }

        $arrangements = [
            0 => '',
        ];

        foreach ($form->Arrangementen as $arrangement) {
            $arrangements[$arrangement->id] = $arrangement->arrangement;
        }
        natcasesort($arrangements);
        return $arrangements;
    }


    /**
     * Get duration of a package
     *
     * @param object $json
     *
     * @return string
     */
    private static function getDuration($json)
    {
        if (!is_array($json->programma)) {
            $json->programma = (array) $json->programma;
        }

        $first = reset($json->programma);
        $last = end($json->programma);

        $startTime = new \DateTime('00:00');
        $startTime->add(new \DateInterval($first->begin));

        $endTime = new \DateTime('00:00');
        $endTime->add(new \DateInterval($first->begin));
        $endTime->add(new \DateInterval($last->eind));
        $duration = $startTime->diff($endTime);

        $html  = '<span class="recras-duration">';
        $durations = [];
        if ($duration->d) {
            $durations[] = $duration->d;
        }
        if ($duration->h) {
            $durations[] = $duration->h;
        }
        if ($duration->i) {
            $durations[] = str_pad($duration->i, 2, '0', STR_PAD_LEFT);
        } else {
            $durations[] = '00';
        }
        if (empty($durations)) {
            $html .= __('No duration specified', Plugin::TEXT_DOMAIN);
        } else {
            $html .= implode(':', $durations);
        }
        $html .= '</span>';

        return $html;
    }


    /**
     * Get the starting location of a package
     *
     * @param object $json
     *
     * @return string
     */
    private static function getLocation($json)
    {
        if (isset($json->ontvangstlocatie)) {
            $location = $json->ontvangstlocatie;
        } else {
            $location = __('No location specified', Plugin::TEXT_DOMAIN);
        }
        return '<span class="recras-location">' . $location . '</span>';
    }


    public static function getPackage($subdomain, $id)
    {
        global $recrasPlugin;

        $json = $recrasPlugin->transients->get($subdomain . '_arrangement_' . $id);
        if ($json === false) {
            try {
                $json = Http::get($subdomain, 'arrangementen/' . $id);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
            $recrasPlugin->transients->set($subdomain . '_arrangement_' . $id, $json);
        }
        return $json;
    }


    /**
     * Get all valid options for the "show" argument
     *
     * @return array
     */
    public static function getValidOptions()
    {
        return ['description', 'duration', 'image_tag', 'image_url', 'location', 'persons', 'price_pp_excl_vat', 'price_pp_incl_vat', 'price_total_excl_vat', 'price_total_incl_vat', 'program', 'programme', 'title'];
    }


    /**
     * Show the TinyMCE shortcode generator package form
     */
    public static function showForm()
    {
        require_once(__DIR__ . '/../editor/form-arrangement.php');
    }
}
