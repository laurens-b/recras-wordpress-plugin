<?php
$subdomain = get_option('recras_subdomain');
if (!$subdomain) {
    \Recras\Settings::errorNoRecrasName();
    return;
}

$model = new \Recras\Arrangement();
$arrangements = $model->getPackages($subdomain);
?>

<dl>
    <dt><label for="arrangement_id"><?php _e('Package', \Recras\Plugin::TEXT_DOMAIN); ?></label>
    <dd><?php if (is_string($arrangements)) { ?>
            <input type="number" id="arrangement_id" min="0" required>
            <?= $arrangements; ?>
        <?php } elseif(is_array($arrangements)) { ?>
            <select id="arrangement_id" required>
            <?php
                foreach ($arrangements as $ID => $arrangement) {
                    if (!$arrangement->mag_beschikbaarheidskalender_api) {
                        continue;
                    }
                ?>
                <option value="<?= $ID; ?>"><?= $arrangement->arrangement; ?>
                <?php
                }
                ?>
            </select>
        <?php } ?>
    <dt><label for="auto_resize"><?php _e('Automatic resize?', \Recras\Plugin::TEXT_DOMAIN); ?></label>
        <dd><input type="checkbox" id="auto_resize" checked>
</dl>
<button class="button button-primary" id="arrangement_submit"><?php _e('Insert shortcode', \Recras\Plugin::TEXT_DOMAIN); ?></button>

<script>
    document.getElementById('arrangement_submit').addEventListener('click', function(){
        let shortcode = '[recras-availability id="' + document.getElementById('arrangement_id').value + '"';
        if (!document.getElementById('auto_resize').checked) {
            shortcode += ' autoresize=0';
        }
        shortcode += ']';

        tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
        tb_remove();
    });
</script>
