<?php
$subdomain = get_option('recras_subdomain');
if (!$subdomain) {
    \Recras\Settings::errorNoRecrasName();
    return;
}

$model = new \Recras\Arrangement();
$arrangements = $model->getPackages($subdomain);
?>
<style id="arrangement_style">
    .programme-only { display: none; }
</style>

<dl>
    <dt><label for="arrangement_id"><?php _e('Package', \Recras\Plugin::TEXT_DOMAIN); ?></label>
        <dd><?php if (is_string($arrangements)) { ?>
            <input type="number" id="arrangement_id" min="0" required>
            <?= $arrangements; ?>
        <?php } elseif (is_array($arrangements)) { ?>
            <select id="arrangement_id" required>
            <?php foreach ($arrangements as $ID => $arrangement) { ?>
                <option value="<?= $ID; ?>"><?= $arrangement->arrangement; ?>
            <?php } ?>
            </select>
            <p><?php _e('If you are not seeing certain packages, make sure in Recras "May be presented on a website (via API)" is enabled on the tab "Extra settings" of the package.', \Recras\Plugin::TEXT_DOMAIN); ?></p>
        <?php } ?>
    <dt><label for="show_what"><?php _e('Show what?', \Recras\Plugin::TEXT_DOMAIN); ?></label>
        <dd><select id="show_what" required>
            <option value="title"><?php _e('Title', \Recras\Plugin::TEXT_DOMAIN); ?>
            <option value="description"><?php _e('Description', \Recras\Plugin::TEXT_DOMAIN); ?>
            <option value="duration"><?php _e('Duration', \Recras\Plugin::TEXT_DOMAIN); ?>
            <option value="location"><?php _e('Starting location', \Recras\Plugin::TEXT_DOMAIN); ?>
            <option value="persons"><?php _e('Minimum number of persons', \Recras\Plugin::TEXT_DOMAIN); ?>
            <option value="price_pp_excl_vat"><?php _e('Price p.p. excl. VAT', \Recras\Plugin::TEXT_DOMAIN); ?>
            <option value="price_pp_incl_vat"><?php _e('Price p.p. incl. VAT', \Recras\Plugin::TEXT_DOMAIN); ?>
            <option value="price_total_excl_vat"><?php _e('Total price excl. VAT', \Recras\Plugin::TEXT_DOMAIN); ?>
            <option value="price_total_incl_vat"><?php _e('Total price incl. VAT', \Recras\Plugin::TEXT_DOMAIN); ?>
            <option value="programme"><?php _e('Programme', \Recras\Plugin::TEXT_DOMAIN); ?>
            <option value="image_tag"><?php _e('Image tag', \Recras\Plugin::TEXT_DOMAIN); ?>
            <option value="image_url"><?php _e('Relative image URL', \Recras\Plugin::TEXT_DOMAIN); ?>
        </select>
    <dt class="programme-only"><label for="starttime"><?php _e('Start time', \Recras\Plugin::TEXT_DOMAIN); ?></label>
        <dd class="programme-only"><input type="text" id="starttime" pattern="[01][0-9]:[0-5][1-9]" placeholder="<?php _e('hh:mm', \Recras\Plugin::TEXT_DOMAIN); ?>" value="00:00">
    <dt class="programme-only"><?php _e('Show header?', \Recras\Plugin::TEXT_DOMAIN); ?>
        <dd class="programme-only">
            <input type="radio" name="header" value="yes" id="header_yes" checked><label for="header_yes"><?php _e('Yes', \Recras\Plugin::TEXT_DOMAIN); ?></label><br>
            <input type="radio" name="header" value="no" id="header_no"><label for="header_no"><?php _e('No', \Recras\Plugin::TEXT_DOMAIN); ?></label>
</dl>
<button class="button button-primary" id="arrangement_submit"><?php _e('Insert shortcode', \Recras\Plugin::TEXT_DOMAIN); ?></button>

<script>
    document.getElementById('show_what').addEventListener('change', function(){
        document.getElementById('arrangement_style').innerHTML = (document.getElementById('show_what').value === 'programme' ? '' : '.programme-only { display: none; }');
    });

    document.getElementById('arrangement_submit').addEventListener('click', function(){
        let shortcode = '[recras-package id="' + document.getElementById('arrangement_id').value + '" show="' + document.getElementById('show_what').value + '"';
        if (document.getElementById('show_what').value === 'programme') {
            if (document.getElementById('starttime').value !== '00:00') {
                shortcode += ' starttime="' + document.getElementById('starttime').value + '"';
            }
            if (document.getElementById('header_no').checked) {
                shortcode += ' showheader="no"';
            }
        }
        shortcode += ']';

        tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
        tb_remove();
    });
</script>
