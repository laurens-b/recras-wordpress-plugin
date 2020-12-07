<?php
    if (isset($_GET['msg'])) {
        //if ($_GET['msg'] === 'success') {
?>
<div class="updated notice">
    <p><?php _e('The cache was cleared.'); ?></p>
</div>
<?php
        /*} elseif ($_GET['msg'] === 'error') {
            ?>
<div class="error notice">
    <p><?php _e('The selected cache could not be cleared. This could be an error, or there could be nothing to clear.'); ?></p>
</div>
            <?php
        }*/
    }
?>

<h1><?php _e('Clear Recras cache', \Recras\Plugin::TEXT_DOMAIN); ?></h1>
<?php
    $subdomain = get_option('recras_subdomain');
?>

<p><?php _e('Data coming from your Recras (contact forms, packages, products, voucher templates) is cached for up to 24 hours. If you make important changes (i.e. a price increase) it is recommended you clear the Recras cache.', \Recras\Plugin::TEXT_DOMAIN); ?></p>

<form action="<?= admin_url('admin-post.php?action=clear_recras_cache'); ?>" method="POST">
    <input type="submit" value="<?php _e('Clear Recras cache', \Recras\Plugin::TEXT_DOMAIN); ?>">
</form>
