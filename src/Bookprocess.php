<?php
namespace Recras;

class Bookprocess
{
    /**
     * Clear book process cache (transients)
     */
    public static function clearCache()
    {
        global $recrasPlugin;

        $subdomain = get_option('recras_subdomain');
        return $recrasPlugin->transients->delete($subdomain . '_bookprocesses');
    }


    /**
     * @param string $subdomain
     */
    public static function enqueueScripts($subdomain)
    {
        wp_enqueue_script(
            'recrasreact',
            'https://' . $subdomain . '.recras.nl/bookprocess/node_modules/react/umd/react.production.min.js',
            [], false, true
        );
        wp_enqueue_script(
            'recrasreactdom',
            'https://' . $subdomain . '.recras.nl/bookprocess/node_modules/react-dom/umd/react-dom.production.min.js',
            ['recrasreact'], false, true
        );
        wp_enqueue_script(
            'recrasbookprocesses',
            'https://' . $subdomain . '.recras.nl/bookprocess/dist/main.js',
            ['recrasreact', 'recrasreactdom'], false, true
        );

        wp_enqueue_style(
            'recrasreactdatepicker',
            'https://' . $subdomain . '.recras.nl/bookprocess/node_modules/react-datepicker/dist/react-datepicker.css'
        );
        wp_enqueue_style(
            'recrasbookprocesses',
            'https://' . $subdomain . '.recras.nl/bookprocess/bookprocess_base.css'
        );
    }

    /**
     * Get book processes for a Recras instance
     *
     * @param string $subdomain
     *
     * @return array|string
     */
    public static function getProcesses($subdomain)
    {
        global $recrasPlugin;

        $json = $recrasPlugin->transients->get($subdomain . '_bookprocesses');
        if ($json === false) {
            try {
                $json = Http::get($subdomain, 'bookprocesses/book');
            } catch (\Exception $e) {
                return $e->getMessage();
            }
            $recrasPlugin->transients->set($subdomain . '_bookprocesses', $json);
        }

        $processes = [];
        foreach ($json->_embedded->bookprocess as $process) {
            $processes[$process->id] = $process->name;
        }
        return $processes;
    }

    /**
     * Add the [recras-bookprocess] shortcode
     *
     * @param array $attributes
     *
     * @return string
     */
    public static function renderBookprocess($attributes)
    {
        $subdomain = Settings::getSubdomain($attributes);
        if (!$subdomain) {
            return Plugin::getNoSubdomainError();
        }

        if (!isset($attributes['id'])) {
            return __('Error: no ID set', Plugin::TEXT_DOMAIN);
        }

        if (!ctype_digit($attributes['id']) && !is_int($attributes['id'])) {
            return __('Error: ID is not a number', Plugin::TEXT_DOMAIN);
        }

        $processes = self::getProcesses($subdomain);
        if (is_string($processes)) {
            // Not a form, but an error
            return sprintf(__('Error: %s', Plugin::TEXT_DOMAIN), $processes);
        }

        if (!isset($processes[$attributes['id']])) {
            return __('Error: book process does not exist', Plugin::TEXT_DOMAIN);
        }

        self::enqueueScripts($subdomain);
        return '<section id="bookprocess" data-id="' . $attributes['id'] . '" data-url="https://' . $subdomain . '.recras.nl">';
    }

    /**
     * Show the TinyMCE shortcode generator contact form
     */
    public static function showForm()
    {
        require_once(__DIR__ . '/../editor/form-bookprocess.php');
    }
}
