<?php
namespace Recras;

class ContactForm
{
    const PATTERN_DATE = '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])';
    const PATTERN_TIME = '(0[0-9]|1[0-9]|2[0-3])(:[0-5][0-9])';

    public static function getDefaultCountry(): ?string
    {
        $locale = get_locale();

        $matches = [];
        if (preg_match('/[a-z]{2}_([A-Z]{2})/', $locale, $matches)) {
            return $matches[1]; // en_IE -> IE
        }
        return null;
    }

    /**
     * Get a single contact form
     *
     * @return object|string
     */
    public static function getForm(string $subdomain, int $id)
    {
        global $recrasPlugin;

        $form = $recrasPlugin->transients->get($subdomain . '_contactform_' . $id . '_v2');
        if ($form === false) {
            try {
                $form = Http::get($subdomain, 'contactformulieren/' . $id . '?embed=Velden');
            } catch (\Exception $e) {
                return $e->getMessage();
            }
            if (isset($form->error, $form->message)) {
                return $form->message;
            }
            $recrasPlugin->transients->set($subdomain . '_contactform_' . $id . '_v2', $form);
        }
        return $form;
    }

    /**
     * Add the [recras-contact] shortcode
     */
    public static function renderContactForm(array $attributes): string
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

        // Get basic info for the form
        $form = self::getForm($subdomain, $attributes['id']);
        if (is_string($form)) {
            // Not a form, but an error
            return sprintf(__('Error: %s', Plugin::TEXT_DOMAIN), $form);
        }

        $formTitle = $form->naam;
        $formFields = $form->Velden;

        if (isset($attributes['showtitle']) && !Settings::parseBoolean($attributes['showtitle'])) {
            $formTitle = false;
        }
        
        $showLabels = !isset($attributes['showlabels']) || Settings::parseBoolean($attributes['showlabels']);
        $showPlaceholders = !isset($attributes['showplaceholders']) || Settings::parseBoolean($attributes['showplaceholders']);

        $element = 'dl';
        if (isset($attributes['element']) && in_array($attributes['element'], self::getValidElements())) {
            $element = $attributes['element'];
        }

        $singleChoiceElement = 'select';
        if (isset($attributes['single_choice_element']) && in_array($attributes['single_choice_element'], self::getValidSingleChoiceElements())) {
            $singleChoiceElement = $attributes['single_choice_element'];
        }

        $arrangementID = isset($attributes['arrangement']) ? $attributes['arrangement'] : null;
        if (!$arrangementID && isset($_GET['package'])) {
            $arrangementID = $_GET['package'];
        }

        if ($arrangementID) {
            $arrangementID = (int) $arrangementID;

            // Check if the contact form supports setting a package
            $fieldFound = false;
            foreach ($formFields as $field) {
                if ($field->soort_invoer === 'boeking.arrangement') {
                    $fieldFound = true;
                }
            }
            if (!$fieldFound) {
                return __('Error: package is set, but contact form does not support packages', Plugin::TEXT_DOMAIN);
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
            'singleChoiceElement' => $singleChoiceElement,
            'subdomain' => $subdomain,
            'submitText' => $submitText,
        ];

        return self::generateForm($attributes['id'], $formFields, $options);
    }


    /**
     * Clear contact form cache (transients)
     */
    public static function clearCache(): int
    {
        global $recrasPlugin;

        $subdomain = get_option('recras_subdomain');
        $errors = 0;

        $forms = array_keys(self::getForms($subdomain));
        foreach ($forms as $id) {
            $errors += self::deleteTransients($subdomain, $id);
        }
        if ($recrasPlugin->transients->get($subdomain . '_contactforms')) {
            $errors += $recrasPlugin->transients->delete($subdomain . '_contactforms');
        }

        return $errors;
    }


    /**
     * Delete transients belonging to a contact form
     */
    private static function deleteTransients(string $subdomain, int $formID): int
    {
        global $recrasPlugin;

        $errors = 0;

        $name = $subdomain . '_contactform_' . $formID . '_v2';
        if ($recrasPlugin->transients->get($name)) {
            $errors += $recrasPlugin->transients->delete($name);
        }

        return $errors;
    }


    /**
     * Generate a group of checkboxes
     */
    public static function generateChoices(\stdClass $field, array $options): string
    {
        $html = '';
        foreach ($options as $value => $name) {
            $dataRequired = $field->verplicht ? 'data-required="1"' : '';
            $html .= '<label><input type="checkbox" name="' . $field->field_identifier . '" value="' . $value . '"' . $dataRequired . '>' . $name . '</label>';
        }
        return $html;
    }


    /**
     * Generate an HTML end tag
     */
    private static function generateEndTag(string $element): string
    {
        return '</' . $element . '>';
    }


    /**
     * Generate a contact form
     */
    public static function generateForm(int $formID, array $formFields, array $options): string
    {
        global $recrasPlugin;
        $arrangementen = [];

        $html  = '';
        if ($options['formTitle']) {
            $html .= '<h2>' . $options['formTitle'] . '</h2>';
        }

        // Contact forms need a unique ID, otherwise problems occur when you have multiple of the same forms on one page
        $generatedFormID = uniqid('F' . $formID);

        $html .= '<form class="recras-contact" id="recras-form' . $generatedFormID . '" data-formid="' . $formID . '">';
        $html .= self::generateStartTag($options['element']);
        foreach ($formFields as $field) {
            if ($options['showLabels'] && $field->soort_invoer !== 'header') {
                $html .= self::generateLabel($options['element'], $field);
            } else if ($options['element'] === 'table' && !$options['showLabels']) {
                $html .= '<tr>';
            }
            switch ($field->soort_invoer) {
                case 'boeking.arrangement':
                    $html .= self::generateSubTag($options['element']);

                    // It is possible that a package was valid for this contact form in the past, but not in the present.
                    // So we show only arrangements that are valid for this form.
                    if (empty($arrangementen)) {
                        $classArrangement = new Arrangement();
                        $arrangementen = $classArrangement->getPackagesForContactForm($options['subdomain'], $formID);
                    }

                    if (isset($options['arrangement']) && isset($arrangementen[$options['arrangement']]) && $options['arrangement'] !== 0) {
                        // Package is set and valid
                        $html .= self::generateInput($field, [
                            'placeholder' => $options['placeholders'],
                            'type' => 'hidden',
                            'value' => $options['arrangement'],
                        ]);
                        $html .= '<span class="recras-prefilled-package">' . $arrangementen[$options['arrangement']] . '</span>';
                    } else {
                        $selectOptions = [
                            'element' => $options['singleChoiceElement'],
                            'placeholder' => $options['placeholders'],
                        ];
                        if (count($arrangementen) === 2 && $field->verplicht) { // 1 real package + 1 empty option
                            unset($arrangementen[0]);
                            $selectOptions['selected'] = current(array_keys($arrangementen));
                        }
                        $html .= self::generateSingleChoice($field, $arrangementen, $selectOptions);
                    }
                    break;
                case 'boeking.datum':
                    $html .= self::generateSubTag($options['element']) . self::generateInput($field, [
                            'raw' => [
                                'autocomplete' => 'off',
                                'data-mindate' => date('Y-m-d'),
                            ],
                            'class' => 'recras-input-date',
                            'min' => date('Y-m-d'),
                            'pattern' => self::PATTERN_DATE,
                            'placeholder' => __('yyyy-mm-dd', Plugin::TEXT_DOMAIN),
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
                            'pattern' => self::PATTERN_TIME,
                            'placeholder' => __('hh:mm', Plugin::TEXT_DOMAIN),
                            'raw' => [
                                'step' => 300, // 300 seconds = 5 minutes
                            ],
                            'type' => 'time',
                        ]);
                    break;
                case 'contact.extra':
                    $html .= self::generateSubTag($options['element']);
                    switch ($field->input_type) {
                        case 'number':
                            $html .= self::generateInput($field, [
                                'raw' => [
                                    'autocomplete' => 'off',
                                ],
                                'placeholder' => $options['placeholders'],
                                'type' => 'number',
                            ]);
                            break;
                        case 'date':
                            $html .= self::generateInput($field, [
                                'class' => 'recras-input-date',
                                'raw' => [
                                    'autocomplete' => 'off',
                                    'data-mindate' => null,
                                    'maxlength' => 10,
                                ],
                                'pattern' => self::PATTERN_DATE,
                                'placeholder' => __('yyyy-mm-dd', Plugin::TEXT_DOMAIN),
                                'type' => 'date',
                            ]);
                            break;
                        case 'text':
                            $html .= self::generateInput($field, [
                                'raw' => [
                                    'maxlength' => 200,
                                ],
                            ]);
                            break;
                        case 'multiplechoice':
                            $choices = array_combine($field->mogelijke_keuzes, $field->mogelijke_keuzes);
                            $html .= self::generateChoices($field, $choices);
                            break;
                        case 'singlechoice':
                            $choices = array_combine($field->mogelijke_keuzes, $field->mogelijke_keuzes);
                            $html .= self::generateSingleChoice($field, $choices, [
                                'element' => $options['singleChoiceElement'],
                                'placeholder' => $options['placeholders'],
                            ]);
                            break;
                        default:
                            $html .= self::generateInput($field);
                    }
                    break;
                case 'contact.landcode':
                    $locale = get_locale();

                    $matches = [];
                    $countryCode = self::getDefaultCountry();

                    if (!file_exists(__DIR__ . '/countries/' . $locale . '.php')) {
                        $locale = 'en_GB';
                    }

                    require_once(__DIR__ . '/countries/' . $locale . '.php');
                    assert(is_array($countries));

                    $selectOptions = [];
                    if (isset($countryCode) && array_key_exists($countryCode, $countries)) {
                        $selectOptions['selected'] = $countryCode;
                    }

                    $html .= self::generateSubTag($options['element']) . self::generateSelect($field, $countries, $selectOptions);
                    break;
                //contact.soort_klant is handled below
                case 'contactpersoon.email1':
                    // Note: there is no email2 field for contact forms
                    $html .= self::generateSubTag($options['element']) . self::generateInput($field, [
                        'placeholder' => $options['placeholders'],
                        'type' => 'email',
                    ]);
                    break;
                case 'contactpersoon.telefoon1':
                case 'contactpersoon.telefoon2':
                    $html .= self::generateSubTag($options['element']) . self::generateInput($field, [
                        'placeholder' => $options['placeholders'],
                        'raw' => [
                            'maxlength' => 50,
                        ],
                        'type' => 'tel',
                    ]);
                    break;
                case 'contactpersoon.geslacht':
                    $html .= self::generateSubTag($options['element']) . self::generateSingleChoice($field, [
                        'onbekend' => __('Unknown', Plugin::TEXT_DOMAIN),
                        'man' => __('Male', Plugin::TEXT_DOMAIN),
                        'vrouw' => __('Female', Plugin::TEXT_DOMAIN),
                    ], [
                        'element' => $options['singleChoiceElement'],
                        'placeholder' => $options['placeholders'],
                    ]);
                    break;
                case 'contactpersoon.nieuwsbrieven':
                    $keuzes = [];
                    foreach ($field->newsletter_options as $id => $name) {
                        $keuzes[$id] = $name;
                    }
                    $html .= self::generateSubTag($options['element']) . self::generateChoices($field, $keuzes);
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
                    $keuzes = array_combine($field->mogelijke_keuzes, $field->mogelijke_keuzes);
                    $html .= self::generateSubTag($options['element']) . self::generateChoices($field, $keuzes);
                    break;
                case 'keuze_enkel':
                case 'contact.soort_klant':
                    $keuzes = array_combine($field->mogelijke_keuzes, $field->mogelijke_keuzes);
                    $html .= self::generateSubTag($options['element']) . self::generateSingleChoice($field, $keuzes, [
                        'element' => $options['singleChoiceElement'],
                        'placeholder' => $options['placeholders'],
                    ]);
                    break;
                case 'veel_tekst':
                    $html .= self::generateSubTag($options['element']) . self::generateTextarea($field, [
                        'placeholder' => $options['placeholders'],
                    ]);
                    break;
                case 'contact.website':
                    /* We deliberately do not use `input[type=url]` because it's not very user friendly.
                     * It requires a protocol and we cannot expect "regular people" to enter this.
                     * Parsing a website (which can be a domain, a subdomain, or a page on a domain)
                     *   is very tricky so we're just using a regular text field without any constraints
                     */
                    $html .= self::generateSubTag($options['element']) . self::generateInput($field, [
                        'autocomplete' => 'url',
                        'placeholder' => $options['placeholders'],
                    ]);
                    break;
                default:
                    $html .= self::generateSubTag($options['element']) . self::generateInput($field, [
                        'placeholder' => $options['placeholders'],
                        'raw' => [
                            'maxlength' => 50,
                        ],
                    ]);
            }
            //$html .= print_r($field, true); //DEBUG
        }
        $html .= self::generateEndTag($options['element']);

        $html .= '<input type="submit" value="' . $options['submitText'] . '">';
        $html .= '</form>';
        $html .= '<script>document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("recras-form' . $generatedFormID . '").addEventListener("submit", function(e) {
        e.preventDefault();
        return submitRecrasForm(
            "' . $generatedFormID . '",
            "' . $options['subdomain'] . '",
            "' . $recrasPlugin->baseUrl . '/",
            "' . $options['redirect']. '"
        );
    });
    var clearRadioEls = document.querySelectorAll(".clearRadioChoice");
    if (clearRadioEls.length) {
        for (var i = 0; i < clearRadioEls.length; i++) {
            clearRadioEls[i].addEventListener("click", function() {
                var radioElChecked = this.parentNode.querySelector("input[type=\'radio\']:checked");
                if (radioElChecked) {
                    radioElChecked.checked = false;
                }
            });
        }
    }
});</script>';

        return $html;
    }


    /**
     * Generate an input field
     */
    private static function generateInput(\stdClass $field, array $options = []): string
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

    private static function generateLabel(string $mainElement, \stdClass $field): string
    {
        $html = '';
        switch ($mainElement) {
            case 'dl':
                $html .= '<dt>';
                break;
            case 'ol':
                $html .= '<li>';
                break;
            case 'table':
                $html .= '<tr>';
                $html .= '<td>';
                break;
        }
        $html .= '<label for="field' . $field->id . '">' . $field->naam;
        if ($field->verplicht) {
            $html .= '<span class="recras-required" aria-label="' . __('(required)', Plugin::TEXT_DOMAIN) . '">*</span>';
        }
        $html .= '</label>';

        return $html;
    }


    /**
     * Generate a set of radio buttons
     */
    public static function generateRadio(\stdClass $field, array $selectItems, array $options = []): string
    {
        $html = '';
        $required = ($field->verplicht ? ' required' : '');
        foreach ($selectItems as $value => $name) {
            if (isset($options['selected']) && $options['selected'] == $value) {
                $selText = ' checked';
            } else {
                $selText = '';
            }
            $html .= '<label><input type="radio" name="' . $field->field_identifier . '" value="' . $value . '"' . $required . $selText . '>' . $name . '</label>';
        }

        if (!$field->verplicht) {
            $html .= '<button type="button" class="clearRadioChoice">' . __('Clear choice', Plugin::TEXT_DOMAIN) . '</button>';
        }

        return $html;
    }


    /**
     * Generate a dropdown field
     */
    public static function generateSelect(\stdClass $field, array $selectItems, array $options = []): string
    {
        $html  = '<select id="field' . $field->id . '" name="' . $field->field_identifier . '"' . ($field->verplicht ? ' required' : '') . '>';
        $html .= self::getSelectPlaceholder($field, $options);

        foreach ($selectItems as $value => $name) {
            if (isset($options['selected']) && $options['selected'] == $value) {
                $selText = ' selected';
            } else {
                $selText = '';
            }
            $html .= '<option value="' . $value . '"' . $selText . '>' . $name;
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * Generate a dropdown field or radio buttons, depending on the element of choice
     */
    public static function generateSingleChoice(\stdClass $field, array $selectItems, array $options = []): string
    {
        if (isset($options['element']) && $options['element'] === 'radio') {
            unset($selectItems[0]);
            return self::generateRadio($field, $selectItems, $options);
        } else {
            return self::generateSelect($field, $selectItems, $options);
        }
    }


    /**
     * Generate an HTML start tag
     */
    private static function generateStartTag(string $element): string
    {
        return '<' . $element . '>';
    }


    /**
     * Generate the element between label and input
     */
    private static function generateSubTag(string $mainElement): string
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
     */
    private static function generateTextarea(\stdClass $field, array $options): string
    {
        $placeholder = self::getPlaceholder($field, $options);
        return '<textarea id="field' . $field->id . '"' .
            'name="' . $field->field_identifier . '"' .
            $placeholder .
            ($field->verplicht ? ' required' : '') .
            '></textarea>';
    }


    /**
     * Get forms for a Recras instance
     *
     * @return array|string
     */
    public static function getForms(string $subdomain)
    {
        global $recrasPlugin;

        $json = $recrasPlugin->transients->get($subdomain . '_contactforms');
        if ($json === false) {
            try {
                $json = Http::get($subdomain, 'contactformulieren');
            } catch (\Exception $e) {
                return $e->getMessage();
            }
            $recrasPlugin->transients->set($subdomain . '_contactforms', $json);
        }

        $forms = [];
        foreach ($json as $form) {
            $forms[$form->id] = $form->naam;
        }
        return $forms;
    }


    /**
     * Get the placeholder for a field
     */
    private static function getPlaceholder(\stdClass $field, array $options): string
    {
        if (is_string($options['placeholder'])) {
            $txt = $options['placeholder'];
            return ' placeholder="' . $txt . '"';
        } elseif ($options['placeholder']) {
            $txt = htmlentities($field->naam, ENT_COMPAT | ENT_HTML5);
            if ($field->verplicht) {
                $txt .= '*';
            }
            return ' placeholder="' .  $txt . '"';
        }
        return '';
    }


    private static function getSelectPlaceholder(\stdClass $field, array $options): string
    {
        $placeholder = '';
        if (isset($options['placeholder']) && $options['placeholder']) {
            $placeholder = '<option value="" selected disabled>';
            if (is_string($options['placeholder'])) {
                $placeholder .= $options['placeholder'];
            } else {
                $placeholder .= htmlentities($field->naam, ENT_COMPAT | ENT_HTML5);
            }
            if ($field->verplicht) {
                $placeholder .= '*';
            }
        }
        return $placeholder;
    }


    /**
     * Get a list of all valid container elements
     */
    public static function getValidElements(): array
    {
        return ['dl', 'table', 'ol'];
    }


    /**
     * Get a list of all valid single choice elements
     */
    public static function getValidSingleChoiceElements(): array
    {
        return ['select', 'radio'];
    }


    /**
     * Show the TinyMCE shortcode generator contact form
     */
    public static function showForm(): void
    {
        require_once(__DIR__ . '/../editor/form-contact.php');
    }
}
