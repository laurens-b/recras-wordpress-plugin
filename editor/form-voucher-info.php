<?php
$subdomain = get_option('recras_subdomain');
if (!$subdomain) {
    \Recras\Settings::errorNoRecrasName();
    return;
}

$model = new \Recras\Vouchers();
$templates = $model->getTemplates($subdomain);
?>

<dl>
    <dt><label for="template_id"><?php _e('Template', \Recras\Plugin::TEXT_DOMAIN); ?></label>
    <dd><?php if (is_string($templates)) { ?>
            <input type="number" id="template_id" min="0" required>
            <?= $templates; ?>
        <?php } elseif (is_array($templates)) { ?>
            <select id="template_id" required>
                <?php foreach ($templates as $ID => $template) { ?>
                <option value="<?= $ID; ?>"><?= $template->name; ?>
                <?php } ?>
            </select>
        <?php } ?>
    <dt><label for="show_what"><?php _e('Show what?', \Recras\Plugin::TEXT_DOMAIN); ?></label>
    <dd><select id="show_what" required>
            <option value="name"><?php _e('Name', \Recras\Plugin::TEXT_DOMAIN); ?>
            <option value="price"><?php _e('Price', \Recras\Plugin::TEXT_DOMAIN); ?>
            <option value="validity"><?php _e('Number of days valid', \Recras\Plugin::TEXT_DOMAIN); ?>
        </select>
</dl>
<button class="button button-primary" id="voucher_submit"><?php _e('Insert shortcode', \Recras\Plugin::TEXT_DOMAIN); ?></button>

<script>
    document.getElementById('voucher_submit').addEventListener('click', function(){
        let shortcode = '[<?= \Recras\Plugin::SHORTCODE_VOUCHER_INFO; ?>';
        shortcode += ' id="' + document.getElementById('template_id').value + '"';
        shortcode += ' show="' + document.getElementById('show_what').value + '"';
        shortcode += ']';

        tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
        tb_remove();
    });
</script>
