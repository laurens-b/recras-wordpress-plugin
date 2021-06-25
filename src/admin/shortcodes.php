<h1><?php _e('Shortcodes', \Recras\Plugin::TEXT_DOMAIN); ?></h1>


<h2><?php _e('Packages', \Recras\Plugin::TEXT_DOMAIN); ?></h2>
<p>Packages can be added using the <kbd>[recras-package]</kbd> shortcode.</p>
<p>The following options are available:</p>
<ol class="recrasOptionsList">
    <li>Package - attribute <kbd>id</kbd>
    <li>Property to show - <kbd>show</kbd>. This can be any of the following:<ol>
        <li>Description - <kbd>description</kbd>
        <li>Duration - <kbd>duration</kbd>
        <li>Image tag - <kbd>image_tag</kbd>
        <li>Minimum number of persons - <kbd>persons</kbd>
        <li>Price p.p. excl. VAT - <kbd>price_pp_excl_vat</kbd>
        <li>Price p.p. incl. VAT - <kbd>price_pp_incl_vat</kbd>
        <li>Programme - <kbd>programme</kbd>
        <li>Starting location - <kbd>location</kbd>
        <li>Title - <kbd>title</kbd>
        <li>Total price excl. VAT - <kbd>price_total_excl_vat</kbd>
        <li>Total price incl. VAT - <kbd>price_total_incl_vat</kbd>
            <li>Relative image URL - <kbd>image_url</kbd>. When using quotation marks, be sure to use different marks in the shortcode and the surrounding code, or the image will not show. E.g. <kbd>&lt;img src="[recras-package id=42 image_url='https://somesite.com/image.png']"&gt;</kbd>
    </ol>
    <li>Start time - <kbd>starttime</kbd>, value is a 24-hour time string
    <li>Show header? - <kbd>showheader</kbd>, value is either  <kbd>1</kbd> (yes) or <kbd>0</kbd> (no)
</ol>
<p>Example: <kbd>[recras-package id="42" show="programme" starttime="12:00" showheader="1"]</kbd></p>


<hr>
<h2><?php _e('Contact forms', \Recras\Plugin::TEXT_DOMAIN); ?></h2>
<p>Contact forms can be added using the <kbd>[recras-contact]</kbd> shortcode.</p>
<p>The following options are available:</p>
<ol class="recrasOptionsList">
	<li>Contact form - <kbd>id</kbd>
	<li>Show title? - <kbd>showtitle</kbd>
	<li>Show labels? - <kbd>showlabels</kbd>
	<li>Show placeholders? - <kbd>showplaceholders</kbd>
	<li>Package - <kbd>arrangement</kbd>
	<li>HTML element - <kbd>element</kbd>, value is one of <kbd>dl</kbd> (recommended), <kbd>ol</kbd>, <kbd>table</kbd> (discouraged)
	<li>Element for single choices - <kbd>single_choice_element</kbd>, value is one of <kbd>select</kbd>, <kbd>radio</kbd>
    <li>Submit button text - <kbd>submitText</kbd>
    <li>Thank-you page - <kbd>redirect</kbd>
</ol>
<p>Example: <kbd>[recras-contact id="17" showtitle="0" showlabels="1" showplaceholders="1" submitText="Go!"]</kbd></p>


<hr>
<h2><?php _e('Online booking', \Recras\Plugin::TEXT_DOMAIN); ?></h2>
<p>Online booking can be added using the <kbd>recras-booking</kbd> shortcode.</p>
<p>The following options are available. Some options are only available depending on the chosen integration method.</p>
<ol class="recrasOptionsList">
    <li>Integration method - <kbd>use_new_library</kbd>. Value is either <kbd>1</kbd> (recommended, for seamless integration) or <kbd>0</kbd> (discouraged, for iframe integration)
    <li>Pre-filled package - <kbd>id</kbd> for a single package, or <kbd>package_list</kbd> for multiple packages. The latter case is only available for the seamless integration, and should be a comma-separated list of id's
    <li>Preview times in programme - <kbd>previewTimes</kbd>, value is either  <kbd>1</kbd> (yes) or <kbd>0</kbd> (no)
</ol>

<h3>Options for seamless integration</h3>
<ol class="recrasOptionsList">
    <li>Show discount fields - <kbd>showdiscount</kbd>, value is either <kbd>1</kbd> (yes) or <kbd>0</kbd> (no)
    <li>Pre-fill amounts - <kbd>product_amounts</kbd>, value is a valid JSON-object of line id's (as seen in the generated HTML) as keys and amounts as values. If you don't know what JSON is or how to find the line id's, contact your web developer.
    <li>Pre-fill date - <kbd>prefill_date</kbd>, value is an ISO 8601 string
    <li>Pre-fill time - <kbd>prefill_time</kbd>, value is a 24-hour time string
    <li>Thank-you page - <kbd>redirect</kbd>
</ol>

<h3>Options for iframe integration</h3>
<ol class="recrasOptionsList">
    <li>Auto resize iframe - <kbd>autoresize</kbd>, value is either  <kbd>1</kbd> (yes) or <kbd>0</kbd> (no)
</ol>

<p>Example: <kbd>[recras-booking use_new_library="1" package_list="9,83" redirect="https://www.recras.com/contact/"]</kbd></p>


<hr>
<h2><?php _e('Availability calendar', \Recras\Plugin::TEXT_DOMAIN); ?></h2>
<p>Availability calendars can be added using the <kbd>recras-availability</kbd> shortcode.</p>
<p>The following options are available:</p>
<ol class="recrasOptionsList">
	<li>Package - <kbd>id</kbd>
	<li>Auto resize iframe - <kbd>autoresize</kbd>, value is either  <kbd>1</kbd> (yes) or <kbd>0</kbd> (no)
</ol>
<p>Example: <kbd>[recras-availability id="18" autoresize="1"]</kbd></p>


<hr>
<h2><?php _e('Voucher sales', \Recras\Plugin::TEXT_DOMAIN); ?></h2>
<p>Voucher sales can be added using the <kbd>recras-vouchers</kbd> shortcode.</p>
<p>The following options are available:</p>
<ol class="recrasOptionsList">
	<li>Voucher template - <kbd>id</kbd>
	<li>Thank-you page - <kbd>redirect</kbd>
    <li>Show quantity input - <kbd>showquantity</kbd>, value is either  <kbd>1</kbd> (yes) or <kbd>0</kbd> (no)
</ol>
<p>Example: <kbd>[recras-vouchers id="12" redirect="https://www.recras.com/contact/"]</kbd></p>


<hr>
<h2><?php _e('Voucher info', \Recras\Plugin::TEXT_DOMAIN); ?></h2>
<p>Voucher info can be added using the <kbd>recras-voucher-info</kbd> shortcode.</p>
<p>The following options are available:</p>
<ol class="recrasOptionsList">
    <li>Voucher template - <kbd>id</kbd>
	<li>Property to show - <kbd>show</kbd>. Value is any of the following:<ol>
        <li>Name - <kbd>name</kbd>
        <li>Price - <kbd>price</kbd>
        <li>Number of days valid - <kbd>validity</kbd>
    </ol>
</ol>
<p>Example: <kbd>[recras-voucher-info id="81" show="price"]</kbd></p>
