<?php
namespace Recras;

class ContactForm
{
    /**
     * Add the [recras-contact] shortcode
     *
     * @param array $attributes
     *
     * @return string
     */
    public static function addContactShortcode($attributes)
    {
        if (!isset($attributes['id'])) {
            return __('Error: no ID set', Plugin::TEXT_DOMAIN);
        }
        if (!ctype_digit($attributes['id'])) {
            return __('Error: ID is not a number', Plugin::TEXT_DOMAIN);
        }

        $subdomain = Settings::getSubdomain($attributes);
        if (!$subdomain) {
            return __('Error: you have not set your Recras name yet', Plugin::TEXT_DOMAIN);
        }


        // Get basic info for the form
        $baseUrl = 'https://' . $subdomain . '.recras.nl/api2.php/contactformulieren/' . $attributes['id'];
        $json = get_transient('recras_' . $subdomain . '_contactform_' . $attributes['id']);
        if ($json === false) {
            $json = @file_get_contents($baseUrl);
            if ($json === false) {
                return __('Error: could not retrieve external data', Plugin::TEXT_DOMAIN);
            }
            $json = json_decode($json);
            if (is_null($json)) {
                return __('Error: could not parse external data', Plugin::TEXT_DOMAIN);
            }
            set_transient('recras_' . $subdomain . '_contactform_' . $attributes['id'], $json, 86400);
        }

        $formTitle = $json->naam;

        if (isset($attributes['showtitle']) && !Settings::parseBoolean($attributes['showtitle'])) {
            $formTitle = false;
        }
        
        $showLabels = !isset($attributes['showlabels']) || Settings::parseBoolean($attributes['showlabels']);
        $showPlaceholders = !isset($attributes['showplaceholders']) || Settings::parseBoolean($attributes['showplaceholders']);

        $element = 'dl';
        if (isset($attributes['element']) && in_array($attributes['element'], self::getValidElements())) {
            $element = $attributes['element'];
        }


        // Get fields for the form
        $json = get_transient('recras_' . $subdomain . '_contactform_' . $attributes['id'] . '_fields');
        if ($json === false) {
            $json = @file_get_contents($baseUrl . '/velden');
            if ($json === false) {
                return __('Error: could not retrieve external data', Plugin::TEXT_DOMAIN);
            }
            $json = json_decode($json);
            if (is_null($json)) {
                return __('Error: could not parse external data', Plugin::TEXT_DOMAIN);
            }
            set_transient('recras_' . $subdomain . '_contactform_' . $attributes['id'] . '_fields', $json, 86400);
        }
        $formFields = $json;


        $arrangementID = null;
        if (isset($attributes['arrangement'])) {
            $arrangementID = (int) $attributes['arrangement'];

            // Check if the contact form supports setting an arrangement
            $fieldFound = false;
            foreach ($formFields as $field) {
                if ($field->soort_invoer === 'boeking.arrangement') {
                    $fieldFound = true;
                }
            }
            if (!$fieldFound) {
                return __('Error: arrangement is set, but contact form does not support arrangements', Plugin::TEXT_DOMAIN);
            }
        }

        $submitText = __('Send', Plugin::TEXT_DOMAIN);
        if (isset($attributes['submittext'])) {
            $submitText = $attributes['submittext'];
        }

        $redirect = isset($attributes['redirect']) ? $attributes['redirect'] : false;

        $options = [
            'arrangement' => $arrangementID,
            'element' => $element,
            'formTitle' => $formTitle,
            'placeholders' => $showPlaceholders,
            'redirect' => $redirect,
            'showLabels' => $showLabels,
            'subdomain' => $subdomain,
            'submitText' => $submitText,
        ];

        return self::generateForm($attributes['id'], $formFields, $options);
    }


    /**
     * Clear contact form cache (transients)
     */
    public static function clearCache()
    {
        $formID = $_REQUEST['contactform'];
        $subdomain = get_option('recras_subdomain');

        if ($formID == 0) {
            $forms = array_keys(self::getForms($subdomain));
            foreach ($forms as $id) {
                delete_transient('recras_' . $subdomain . '_contactform_' . $id);
                delete_transient('recras_' . $subdomain . '_contactform_' . $id . '_fields');
                delete_transient('recras_' . $subdomain . '_contactform_' . $id . '_arrangements');
            }
            delete_transient('recras_' . $subdomain . '_contactforms');
        } else {
            delete_transient('recras_' . $subdomain . '_contactform_' . $formID);
        }
        header('Location: ' . admin_url('admin.php?page=recras-clear-cache'));
        exit;
    }


    /**
     * Generate a group of checkboxes
     *
     * @param object $field
     * @param array $options
     *
     * @return string
     */
    public static function generateChoices($field, $options)
    {
        $html = '';
        foreach ($options as $value => $name) {
            $html .= '<label><input type="checkbox" name="' . $field->field_identifier . '" value="' . $value . '">' . $name . '</label>';
        }
        return $html;
    }


    /**
     * Generate an HTML end tag
     *
     * @param string $element
     *
     * @return string
     */
    private static function generateEndTag($element)
    {
        return '</' . $element . '>';
    }


    /**
     * Generate a contact form
     *
     * @param int $formID
     * @param array $formFields
     * @param array $options
     *
     * @return string
     */
    public static function generateForm($formID, $formFields, $options)
    {
        $arrangementen = [];

        $html  = '';
        if ($options['formTitle']) {
            $html .= '<h2>' . $options['formTitle'] . '</h2>';
        }

        $html .= '<form class="recras-contact" id="recras-form' . $formID . '">';
        $html .= self::generateStartTag($options['element']);
        foreach ($formFields as $field) {
            if ($field->soort_invoer !== 'header' && ($field->soort_invoer !== 'boeking.arrangement' || is_null($options['arrangement']))) { //TODO: this fails when arrangement is set but invalid
                $html .= self::generateLabel($options['element'], $field, $options['showLabels']);
                if ($field->verplicht && $options['showLabels']) {
                    $html .= '<span class="recras-required">*</span>';
                }
            }
            switch ($field->soort_invoer) {
                case 'boeking.arrangement':
                    // It is possible that an arrangement was valid for this contact form in the past, not not in the present.
                    // So we show only arrangements that are valid for this form.
                    if (empty($arrangementen)) {
                        $classArrangement = new Arrangement;
                        $arrangementen = $classArrangement->getArrangementsForContactForm($options['subdomain'], $formID);
                    }
                    // The  isset()  is in case an arrangement is set, but it is not valid
                    if (is_null($options['arrangement']) || !isset($arrangementen[$options['arrangement']])) {
                        $html .= self::generateSubTag($options['element']) . self::generateSelect($field, $arrangementen);
                    } else {
                        $html .= self::generateInput($field, [
                            'placeholder' => $options['placeholders'],
                            'type' => 'hidden',
                            'value' => $options['arrangement'],
                        ]);
                    }
                    break;
                case 'boeking.datum':
                    $html .= self::generateSubTag($options['element']) . self::generateInput($field, [
                            'class' => 'recras-input-date',
                            'pattern' => '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])',
                            'placeholder' => 'yyyy-mm-dd',
                            'type' => 'date',
                        ]);
                    break;
                case 'boeking.groepsgrootte':
                    $html .= self::generateSubTag($options['element']) . self::generateInput($field, [
                            'placeholder' => $options['placeholders'],
                            'raw' => [
                                'min' => 1,
                            ],
                            'type' => 'number',
                        ]);
                    break;
                case 'boeking.starttijd':
                    $html .= self::generateSubTag($options['element']) . self::generateInput($field, [
                            'class' => 'recras-input-time',
                            'pattern' => '(0[0-9]|1[0-9]|2[0-3])(:[0-5][0-9])',
                            'placeholder' => 'hh:mm',
                            'type' => 'time',
                        ]);
                    break;
                case 'contactpersoon.geslacht':
                    $html .= self::generateSubTag($options['element']) . self::generateSelect($field, [
                        'onbekend' => __('Unknown', Plugin::TEXT_DOMAIN),
                        'man' => __('Male', Plugin::TEXT_DOMAIN),
                        'vrouw' => __('Female', Plugin::TEXT_DOMAIN),
                    ]);
                    break;
                case 'header':
                    if (strpos($html, '<dt') !== false || strpos($html, '<li') !== false || strpos($html, '<tr') !== false) {
                        $html .= self::generateEndTag($options['element']);
                    }
                    $html .= '<h3>' . $field->naam . '</h3>';
                    if (strpos($html, '<dt') !== false || strpos($html, '<li') !== false || strpos($html, '<tr') !== false) {
                        $html .= self::generateStartTag($options['element']);
                    }
                    break;
                case 'keuze':
                    $keuzes = [];
                    foreach ($field->mogelijke_keuzes as $keuze) {
                        $keuzes[$keuze] = $keuze;
                    }
                    $html .= self::generateSubTag($options['element']) . self::generateChoices($field, $keuzes);
                    break;
                case 'veel_tekst':
                    $html .= self::generateSubTag($options['element']) . self::generateTextarea($field, [
                        'placeholder' => $options['placeholders'],
                    ]);
                    break;
                default:
                    $html .= self::generateSubTag($options['element']) . self::generateInput($field, [
                        'placeholder' => $options['placeholders'],
                    ]);
            }
            //$html .= print_r($field, true); //DEBUG
        }
        $html .= self::generateEndTag($options['element']);

        $html .= '<input type="submit" value="' . $options['submitText'] . '">';
        $html .= '</form>';
        $html .= '<script>jQuery(document).ready(function(){
    jQuery("#recras-form' . $formID . '").on("submit", function(e){
        e.preventDefault();
        return submitRecrasForm(' . $formID . ', "' . $options['subdomain'] . '", "' . plugins_url('/', dirname(__FILE__)) . '", "' . $options['redirect']. '");
    });
});</script>';

        return $html;
    }


    /**
     * Generate an input field
     *
     * @param object $field
     * @param array $options
     *
     * @return string
     */
    private static function generateInput($field, $options = [])
    {
        $options = array_merge([
            'class' => false,
            'pattern' => null,
            'placeholder' => false,
            'raw' => [],
            'required' => false,
            'type' => 'text',
            'value' => '',
        ], $options);

        $pattern = ($options['pattern'] ? ' pattern="' . $options['pattern'] . '"' : '');
        $placeholder = self::getPlaceholder($field, $options);
        $required = ($field->verplicht ? ' required' : '');
        $class = ($options['class'] ? ' class="' . $options['class'] . '"' : '');

        $raw = '';
        foreach ($options['raw'] as $rawKey => $rawValue) {
            $raw .= ' ' . $rawKey . '="' . $rawValue . '"';
        }

        return '<input id="field' . $field->id . '" type="' . $options['type'] . '" name="' . $field->field_identifier . '" value="' . $options['value'] . '"' . $required . $class . $placeholder . $pattern . $raw . '>';
    }


    /**
     * Generate a label element
     *
     * @param string $mainElement
     * @param object $field
     * @param bool $showLabel
     *
     * @return string
     */
    private static function generateLabel($mainElement, $field, $showLabel)
    {
        $html = '';
        switch ($mainElement) {
            case 'dl':
                $html .= ($showLabel ? '<dt>' : '');
                break;
            case 'ol':
                $html .= ($showLabel ? '<li>' : '');
                break;
            case 'table':
                $html .= '<tr>';
                $html .= ($showLabel ? '<td>' : '');
                break;
        }
        if ($showLabel) {
            $html .= '<label for="field' . $field->id . '">' . $field->naam . '</label>';
        }
        return $html;
    }


    /**
     * Generate a dropdown field
     *
     * @param object $field
     * @param array $options
     *
     * @return string
     */
    public static function generateSelect($field, $options)
    {
        $html = '<select id="field' . $field->id . '" name="' . $field->field_identifier . '"' . ($field->verplicht ? ' required' : '') . '>';
        foreach ($options as $value => $name) {
            $html .= '<option value="' . $value . '">' . $name;
        }
        $html .= '</select>';
        return $html;
    }


    /**
     * Generate an HTML start tag
     *
     * @param string $element
     *
     * @return string
     */
    private static function generateStartTag($element)
    {
        return '<' . $element . '>';
    }


    /**
     * Generate the element between label and input
     *
     * @param string $mainElement
     *
     * @return string
     */
    private static function generateSubTag($mainElement)
    {
        $html = '';
        switch ($mainElement) {
            case 'dl':
                $html .= '<dd>';
                break;
            case 'ol':
                break;
            case 'table':
                $html .= '<td>';
                break;
        }

        return $html;
    }


    /**
     * Generate a textarea
     *
     * @param object $field
     * @param array $options
     *
     * @return string
     */
    private static function generateTextarea($field, $options)
    {
        $placeholder = self::getPlaceholder($field, $options);
        return '<textarea id="field' . $field->id . '" name="' . $field->field_identifier . '"' . $placeholder . ($field->verplicht ? ' required' : '') . '></textarea>';
    }


    /**
     * Get forms for a Recras instance
     *
     * @param string $subdomain
     *
     * @return array|string
     */
    public static function getForms($subdomain)
    {
        $json = get_transient('recras_' . $subdomain . '_contactforms');
        if ($json === false) {
            $baseUrl = 'https://' . $subdomain . '.recras.nl/api2.php/contactformulieren';
            $json = @file_get_contents($baseUrl);
            if ($json === false) {
                return __('Error: could not retrieve external data', Plugin::TEXT_DOMAIN);
            }
            $json = json_decode($json);
            if (is_null($json)) {
                return __('Error: could not parse external data', Plugin::TEXT_DOMAIN);
            }
            set_transient('recras_' . $subdomain . '_contactforms', $json, 86400);
        }

        $forms = [];
        foreach ($json as $form) {
            $forms[$form->id] = $form->naam;
        }
        return $forms;
    }


    /**
     * Get the placeholder for a field
     *
     * @param object $field
     * @param array $options
     *
     * @return string
     */
    private static function getPlaceholder($field, $options)
    {
        $placeholder = '';
        if (is_string($options['placeholder'])) {
            $placeholder = ' placeholder="' . $options['placeholder'] . '"';
            if ($field->verplicht) {
                $placeholder .= '*';
            }
        } elseif ($options['placeholder']) {
            $placeholder = ' placeholder="' . htmlentities($field->naam, ENT_COMPAT | ENT_HTML5) . '"';
            if ($field->verplicht) {
                $placeholder .= '*';
            }
        }
        return $placeholder;
    }


    /**
     * Get a list of all valid container elements
     *
     * @return array
     */
    public static function getValidElements()
    {
        return ['dl', 'table', 'ol'];
    }


    /**
     * Show the TinyMCE shortcode generator contact form
     */
    public static function showForm()
    {
        require_once(dirname(__FILE__) . '/../editor/form-contact.php');
    }
}
