<?php
$subdomain = get_option('recras_subdomain');
if (!$subdomain) {
    \Recras\Settings::errorNoRecrasName();
    return;
}

$model = new \Recras\Bookprocess();
$processes = $model->getProcesses($subdomain);
?>
<dl>
    <dt><label for="bookprocess_id"><?php _e('Book process', \Recras\Plugin::TEXT_DOMAIN); ?></label>
        <dd><?php if (is_string($processes)) { ?>
            <input type="number" id="bookprocess_id" min="1" required>
            <?= $processes; ?>
        <?php } elseif(is_array($processes)) { ?>
            <select id="bookprocess_id" required>
                <?php foreach ($processes as $ID => $formName) { ?>
                <option value="<?= $ID; ?>"><?= $formName; ?>
                <?php } ?>
            </select>
        <?php } ?>
</dl>
<button class="button button-primary" id="bp_submit"><?php _e('Insert shortcode', \Recras\Plugin::TEXT_DOMAIN); ?></button>

<script>
    document.getElementById('bp_submit').addEventListener('click', function(){
        const shortcode = '[recras-bookprocess id="' + document.getElementById('bookprocess_id').value + '"]';

        tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
        tb_remove();
    });
</script>
