<?php
$subdomain = get_option('recras_subdomain');
if (!$subdomain) {
    \Recras\Settings::errorNoRecrasName();
    return;
}

$model = new \Recras\Arrangement;
$arrangements = $model->getArrangements($subdomain);

$model = new \Recras\ContactForm;
$forms = $model->getForms($subdomain);
?>
<dl>
    <dt><label for="contactform_id"><?php _e('Contact form', \Recras\Plugin::TEXT_DOMAIN); ?></label>
        <dd><?php if (is_string($forms)) { ?>
            <input type="number" id="contactform_id" min="0" required>
            <?= $forms; ?>
        <?php } elseif(is_array($forms)) { ?>
            <select id="contactform_id" required>
                <?php foreach ($forms as $ID => $formName) { ?>
                <option value="<?= $ID; ?>"><?= $formName; ?>
                <?php } ?>
            </select>
        <?php } ?>
    <dt><label for="showtitle"><?php _e('Show title?', \Recras\Plugin::TEXT_DOMAIN); ?></label>
        <dd><input type="checkbox" id="showtitle" checked>
    <dt><label for="showlabels"><?php _e('Show labels?', \Recras\Plugin::TEXT_DOMAIN); ?></label>
        <dd><input type="checkbox" id="showlabels" checked>
    <dt><label for="showplaceholders"><?php _e('Show placeholders?', \Recras\Plugin::TEXT_DOMAIN); ?></label>
        <dd><input type="checkbox" id="showplaceholders" checked>
    <dt><label for="arrangement_id"><?php _e('Package', \Recras\Plugin::TEXT_DOMAIN); ?></label>
        <dd><?php if (is_string($arrangements)) { ?>
            <input type="number" id="arrangement_id" min="0" required>
            <?= $arrangements; ?>
        <?php } elseif(is_array($arrangements)) { ?>
            <select id="arrangement_id" required>
                <?php foreach ($arrangements as $ID => $arrangement) { ?>
                <option value="<?= $ID; ?>"><?= $arrangement->arrangement; ?>
                <?php } ?>
            </select>
        <?php } ?>
        <p class="recras-notice">
            <?php _e('Some packages may not be available for all contact forms. You can change this by editing your contact forms in Recras.', \Recras\Plugin::TEXT_DOMAIN); ?><br>
            <?php _e('If you are still missing packages, make sure in Recras "May be presented on a website (via API)" is enabled on the tab "Extra settings" of the package.', \Recras\Plugin::TEXT_DOMAIN); ?>
        </p>
    <dt><label for="container_element"><?php _e('HTML element', \Recras\Plugin::TEXT_DOMAIN); ?></label>
        <dd><select id="container_element">
                <option value="dl" selected><?php _e('Definition list', \Recras\Plugin::TEXT_DOMAIN); ?> (&lt;dl&gt;)
                <option value="ol"><?php _e('Ordered list', \Recras\Plugin::TEXT_DOMAIN); ?> (&lt;ol&gt;)
                <option value="table"><?php _e('Table', \Recras\Plugin::TEXT_DOMAIN); ?> (&lt;table&gt;)
            </select>
    <dt><label for="single_choice_element"><?php _e('Element for single choices', \Recras\Plugin::TEXT_DOMAIN); ?></label>
        <dd><select id="single_choice_element">
                <option value="select" selected><?php _e('Drop-down list (Select)', \Recras\Plugin::TEXT_DOMAIN); ?>
                <option value="radio"><?php _e('Radio buttons', \Recras\Plugin::TEXT_DOMAIN); ?>
            </select>
        <p class="recras-notice">
            <?php _e('This relates to: customer type, package selection, gender, and single choice', \Recras\Plugin::TEXT_DOMAIN); ?><br>
        </p>
    <dt><label for="submit_text"><?php _e('Submit button text', \Recras\Plugin::TEXT_DOMAIN); ?></label>
        <dd><input type="text" id="submit_text" value="<?php _e('Send', \Recras\Plugin::TEXT_DOMAIN); ?>">
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
</dl>
<button class="button button-primary" id="contact_submit"><?php _e('Insert shortcode', \Recras\Plugin::TEXT_DOMAIN); ?></button>

<script>
    const DEFAULT_ELEMENT = 'dl';
    const DEFAULT_SINGLE_CHOICE_ELEMENT = 'select';

    // Check which arrangements are available
    getContactFormArrangements(document.getElementById('contactform_id').value, '<?php echo $subdomain; ?>');
    document.getElementById('contactform_id').addEventListener('change', function(){
        getContactFormArrangements(document.getElementById('contactform_id').value, '<?php echo $subdomain; ?>');
    });

    document.getElementById('contact_submit').addEventListener('click', function(){
        let shortcode = '[recras-contact id="' + document.getElementById('contactform_id').value + '"';

        const options = ['showtitle', 'showlabels', 'showplaceholders'];
        for (let opt of options) {
            if (!document.getElementById(opt).checked) {
                shortcode += ' ' + opt + '="no"';
            }
        }

        if (document.getElementById('arrangement_id').value > 0) {
            shortcode += ' arrangement="' + document.getElementById('arrangement_id').value + '"';
        }
        if (document.getElementById('container_element').value !== DEFAULT_ELEMENT) {
            shortcode += ' element="' + document.getElementById('container_element').value + '"';
        }
        if (document.getElementById('single_choice_element').value !== DEFAULT_SINGLE_CHOICE_ELEMENT) {
            shortcode += ' single_choice_element="' + document.getElementById('single_choice_element').value + '"';
        }
        if (document.getElementById('submit_text').value !== '<?php _e('Send', \Recras\Plugin::TEXT_DOMAIN); ?>') {
            shortcode += ' submittext="' + document.getElementById('submit_text').value + '"';
        }
        if (document.getElementById('redirect_page').value !== '') {
            shortcode += ' redirect="' + document.getElementById('redirect_page').value + '"';
        }

        shortcode += ']';

        tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
        tb_remove();
    });
</script>
