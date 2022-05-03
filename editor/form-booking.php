<?php
$subdomain = get_option('recras_subdomain');
if (!$subdomain) {
    \Recras\Settings::errorNoRecrasName();
    return;
}

$model = new \Recras\Arrangement();
$arrangements = $model->getPackages($subdomain, true);
?>
<dl>
    <dt><label><?php _e('Integration method', \Recras\Plugin::TEXT_DOMAIN); ?></label>
        <dd>
            <label>
                <input type="radio" id="use_new_library_yes" name="integration_method" value="jslibrary" checked>
                <?php _e('Seamless (recommended)', \Recras\Plugin::TEXT_DOMAIN); ?>
            </label>
            <br>
            <label>
                <input type="radio" id="use_new_library_no" name="integration_method" value="iframe">
                <?php _e('iframe (uses setting in your Recras)', \Recras\Plugin::TEXT_DOMAIN); ?>
            </label>
        <p class="recras-notice">
            <?php
            _e('Seamless integration uses the styling of your website. At Recras → Settings in the menu on the left, you can set an optional theme.', \Recras\Plugin::TEXT_DOMAIN);
            ?>
            <br>
            <?php
            _e('iframe integration uses the styling set in your Recras. You can change the styling in Recras via Settings → Other settings → Custom CSS.', \Recras\Plugin::TEXT_DOMAIN);
            ?>
        </p>

    <dt id="pack_sel_label">
        <label for="package_selection"><?php _e('Package selection', \Recras\Plugin::TEXT_DOMAIN); ?></label>
    <dd id="pack_sel_input">
        <?php unset($arrangements[0]); ?>
        <select multiple id="package_selection">
            <?php foreach ($arrangements as $ID => $arrangement) { ?>
            <option value="<?= $ID; ?>"><?= $arrangement->arrangement; ?>
            <?php } ?>
        </select>
        <p class="recras-notice">
            <?php
            _e('To (de)select multiple packages, hold Ctrl and click (Cmd on Mac)', \Recras\Plugin::TEXT_DOMAIN);
            ?>
        </p>
    <dt id="pack_one_label" style="display: none;">
        <label for="arrangement_id"><?php _e('Package', \Recras\Plugin::TEXT_DOMAIN); ?></label>
    <dd id="pack_one_input" style="display: none;">
        <?php if (is_string($arrangements)) { ?>
            <input type="number" id="arrangement_id" min="0">
            <?= $arrangements; ?>
        <?php } elseif(is_array($arrangements)) { ?>
            <?php unset($arrangements[0]); ?>
            <select id="arrangement_id" required>
                <option value="0"><?php _e('No pre-filled package', \Recras\Plugin::TEXT_DOMAIN); ?>
                <?php foreach ($arrangements as $ID => $arrangement) { ?>
                <option value="<?= $ID; ?>"><?= $arrangement->arrangement; ?>
                <?php } ?>
            </select>
        <?php } ?>

    <dt><label for="show_times"><?php _e('Preview times in programme', \Recras\Plugin::TEXT_DOMAIN); ?></label>
        <dd><input type="checkbox" id="show_times">
    <dt><label><?php _e('Pre-fill amounts (requires pre-filled package)', \Recras\Plugin::TEXT_DOMAIN); ?></label>
        <dd><strong><?php _e('Sorry, this is only available using the Gutenberg editor.', \Recras\Plugin::TEXT_DOMAIN); ?></strong>
    <dt><label for="prefill_date"><?php _e('Pre-fill date (requires exactly 1 package selected)',\Recras\Plugin::TEXT_DOMAIN ); ?></label>
        <dd><input
            type="date"
            id="prefill_date"
            min="<?= date('Y-m-d') ?>"
            pattern="<?= \Recras\ContactForm::PATTERN_DATE; ?>"
            placeholder="<?= __('yyyy-mm-dd', \Recras\Plugin::TEXT_DOMAIN); ?>"
            disabled
        >
    <dt><label for="prefill_time"><?php _e('Pre-fill time (requires exactly 1 package selected)',\Recras\Plugin::TEXT_DOMAIN ); ?></label>
        <dd><input
            type="time"
            id="prefill_time"
            pattern="<?= \Recras\ContactForm::PATTERN_TIME; ?>"
            step="300"
            placeholder="<?= __('hh:mm', \Recras\Plugin::TEXT_DOMAIN); ?>"
            disabled
        >
    <dt><label for="redirect_page"><?php _e('Thank-you page', \Recras\Plugin::TEXT_DOMAIN); ?></label>
        <dd><select id="redirect_page">
            <option value=""><?php _e("Don't redirect", \Recras\Plugin::TEXT_DOMAIN); ?>
            <optgroup label="<?php _e('Pages', \Recras\Plugin::TEXT_DOMAIN); ?>">
                <?php foreach (get_pages() as $page) { ?>
                <option value="<?= get_permalink($page->ID); ?>"><?= htmlspecialchars($page->post_title); ?>
                    <?php } ?>
            </optgroup>
            <optgroup label="<?php _e('Posts', \Recras\Plugin::TEXT_DOMAIN); ?>">
                <?php foreach (get_posts() as $post) { ?>
                <option value="<?= get_permalink($post->ID); ?>"><?= htmlspecialchars($post->post_title); ?>
                    <?php } ?>
            </optgroup>
        </select>
    <dt><label for="show_discounts"><?php _e('Show discount fields', \Recras\Plugin::TEXT_DOMAIN); ?></label>
        <dd><input type="checkbox" id="show_discounts" checked>
    <dt><label for="auto_resize"><?php _e('Automatic resize?', \Recras\Plugin::TEXT_DOMAIN); ?></label>
        <dd><input type="checkbox" id="auto_resize" disabled>

</dl>
<button class="button button-primary" id="booking_submit"><?php _e('Insert shortcode', \Recras\Plugin::TEXT_DOMAIN); ?></button>

<script>
    [...document.querySelectorAll('[name="integration_method"]')].forEach(function(el) {
        el.addEventListener('change', function(){
            const useLibrary = document.getElementById('use_new_library_yes').checked;
            document.getElementById('auto_resize').disabled = useLibrary;
            document.getElementById('redirect_page').disabled = !useLibrary;
            document.getElementById('show_times').disabled = !useLibrary;
            document.getElementById('show_discounts').disabled = !useLibrary;

            document.getElementById('pack_sel_label').style.display = useLibrary ? 'block' : 'none';
            document.getElementById('pack_sel_input').style.display = useLibrary ? 'block' : 'none';
            document.getElementById('pack_one_label').style.display = useLibrary ? 'none' : 'block';
            document.getElementById('pack_one_input').style.display = useLibrary ? 'none' : 'block';
        });
    });
    document.getElementById('arrangement_id').addEventListener('change', function() {
        const hasPackage = this.value > 0;
        document.getElementById('prefill_date').disabled = !hasPackage;
        document.getElementById('prefill_time').disabled = !hasPackage;
    });
    document.getElementById('package_selection').addEventListener('change', function() {
        const selectedPackages = document.querySelectorAll('#package_selection option:checked');
        const hasPackage = selectedPackages.length === 1;
        document.getElementById('prefill_date').disabled = !hasPackage;
        document.getElementById('prefill_time').disabled = !hasPackage;
    });

    document.getElementById('booking_submit').addEventListener('click', function() {
        const useNewLibrary = document.getElementById('use_new_library_yes').checked;

        let arrangementID;
        let packageIDsMultiple = [];
        const selectedPackages = document.querySelectorAll('#package_selection option:checked');
        if (selectedPackages.length === 1) {
            arrangementID = selectedPackages[0].value;
        } else {
            packageIDsMultiple = [...selectedPackages].map(el => el.value);
        }
        let shortcode = '[<?= \Recras\Plugin::SHORTCODE_ONLINE_BOOKING; ?>';
        if (packageIDsMultiple.length > 0 && useNewLibrary) {
            shortcode += ' package_list="' + packageIDsMultiple.join(',') + '"';
        } else if (arrangementID) {
            shortcode += ' id="' + arrangementID + '"';
        }

        if (useNewLibrary) {
            shortcode += ' use_new_library=1';
            if (document.getElementById('redirect_page').value !== '') {
                shortcode += ' redirect="' + document.getElementById('redirect_page').value + '"';
            }
            if (document.getElementById('show_times').checked) {
                shortcode += ' show_times=1';
            }
            if (!document.getElementById('show_discounts').checked) {
                shortcode += ' showdiscount=0';
            }
        } else {
            if (!document.getElementById('auto_resize').checked) {
                shortcode += ' autoresize=0';
            }
        }

        if (arrangementID) {
            if (document.getElementById('prefill_date').value) {
                shortcode += ' prefill_date="' + document.getElementById('prefill_date').value + '"';
            }
            if (document.getElementById('prefill_time').value) {
                shortcode += ' prefill_time="' + document.getElementById('prefill_time').value + '"';
            }
        }
        shortcode += ']';

        tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
        tb_remove();
    });
</script>
