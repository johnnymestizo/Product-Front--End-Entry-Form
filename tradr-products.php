<?php
/*
Plugin Name: Front End Product Entry Form
Plugin URI: http://imath.owni.fr/
Description: adds a front end form to marketpress plugin
Version: 1.0
Requires at least: 3.1
Tested up to: 3.1.3
License: GNU/GPL 2
Author: imath
Author URI: http://imath.owni.fr/
*/

define ( 'TRADR_PRODUCTS_PLUGIN_NAME', 'tradr-products' );
define ( 'TRADR_PRODUCTS_PLUGIN_URL', WP_PLUGIN_URL . '/' . TRADR_PRODUCTS_PLUGIN_NAME );
define ( 'TRADR_PRODUCTS_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . TRADR_PRODUCTS_PLUGIN_NAME );
define ( 'TRADR_PRODUCTS_VERSION', '1.0' );


add_action('template_redirect','tradr_products_catch_uri', 99);

function tradr_products_catch_uri(){
	global $wp_tradr_products_submit_errors;
	if(isset($_POST['tradr_submit_product'])){
		require_once(dirname(__FILE__).'/includes/tp-save-new.php');
	}
	if( get_option('tradr_product_form_page_id')!="" &&  is_page(get_option('tradr_product_form_page_id'))){
		wp_enqueue_script('utils');
		wp_enqueue_style('thickbox');
		wp_enqueue_script('jquery');
		wp_enqueue_script('thickbox');
		wp_enqueue_script('tradr-tag-editor', TRADR_PRODUCTS_PLUGIN_URL .'/js/jquery.tag.editor-min.js', 'jquery');
		wp_enqueue_style('style-tradr', TRADR_PRODUCTS_PLUGIN_URL .'/css/style.css');
	}
}

add_shortcode('tp_theform','tradr_products_handle_form_shortcode');

function tradr_products_handle_form_shortcode(){
	global $mp, $wp_tradr_products_submit_errors;
	
	if(!get_option('tradr_product_form_page_id') || "" == get_option('tradr_product_form_page_id')){
		?>
		<div id="tradr_product_errors"><p>Please set the ID of this page in the front end form options !</p></div>
		<?php
		return false;
	}
	
	if(!is_user_logged_in()){
		$tradr_product_login_message = get_option('_tradr_product_login_message');
		if(!$tradr_product_login_message || $tradr_product_login_message==""){
			$tradr_product_login_message = 'You need to be member of this site and logged in to submit a product';
		}
		?>
		<div id="tradr_product_notlogged"><p><?php echo stripslashes(nl2br($tradr_product_login_message));?></p></div>
		<?php
		return false;
	}
	
	if(isset($_GET['moderation'])){
		$tradr_product_moderation_message = get_option('_tradr_product_moderation_message');
		if(!$tradr_product_moderation_message || $tradr_product_moderation_message==""){
			$tradr_product_moderation_message = __('Your product is awaiting moderation. Thanks for submitting it.');
		}
		?>
			<div id="tradr_product_moderation"><p><?php echo stripslashes(nl2br($tradr_product_moderation_message));?></p></div>
		<?php
	}
	else{
		if(count($wp_tradr_products_submit_errors)>0){
			$product_content .= '<div id="tradr_product_errors"><p>Please make sure to check the following error(s) :</p><ul>';
			foreach($wp_tradr_products_submit_errors as $error){
				$product_content .= '<li>'.$error.'</li>';
			}
			$product_content .='</ul></div>';
		}
		require_once(dirname(__FILE__).'/includes/tp-tiny-mce.php');
		
		$price = "0.00";
		if(isset($_POST['_mp_price'])) $price = $_POST['_mp_price'];
		$inventory = "0";
		if(isset($_POST['_mp_inventory'])) $inventory = $_POST['_mp_inventory'];
		$product_link = "";
		if(isset($_POST['_mp_inventory'])) $product_link = wp_kses($_POST['_mp_product_link'], array());
		
		$product_content .= '<form action="" method="post" class="product_form">';
		$product_content .= '<div class="new-product-form"><label for="product_title">Name of your product</label><input type="text" id="product_title" name="product_title" value="'.wp_kses(stripslashes($_POST['product_title']), array()).'"/></div>';
		$product_content .= '<div class="new-product-form"><label for="product_content">Product Description</label><textarea tabindex="2" name="product_content" id="tradr_product_content" class="tradr_product_tinymce" cols="40" rows="12" >'.wp_kses(stripslashes($_POST['product_content']), tradr_product_allowed_html_tags()).'</textarea></div>';
		if(isset($_POST['tradr_feat_img'])){
			$product_content .= '<div id="tradr_featured_image">';
		}
		else $product_content .= '<div id="tradr_featured_image" style="display:none">';
		$product_content .= '<a href="javascript:openFeaturedImg()" title="Open in a new window">View Featured Image</a> <a href="javascript:removeFeaturedImg()" title="Remove">Remove Featured Image</a></div>';
		$product_content .= '<input type="hidden" name="tradr_feat_img" id="tradr_feat_img" value="'.$_POST['tradr_feat_img'].'"><input type="hidden" name="tradr_id_img_list" id="tradr_id_img_list" value="'.$_POST['tradr_id_img_list'].'">';
		$product_content .= '<div class="new_product_detail">';
		$product_content .= '<table><thead><tr><th title="'.__('Stock Keeping Unit - Your custom Product ID number', 'mp').'">SKU</th><th>Price</th><th>Inventory</th></tr></thead>';
		$product_content .= '<tbody><tr><td><input type="text" name="_mp_sku" value="'.$_POST['_mp_sku'].'"></td><td>'.$mp->format_currency().'&nbsp;<input type="text" name="_mp_price" value="'.$price.'"></td><td><input type="text" name="_mp_inventory" value="'.$inventory.'"></td></tr></tbody></table>';
		$product_content .= '</div>';
		$product_content .= '<div class="new-product-form"><label for="_mp_product_link">External Link:</label><br/>';
		$product_content .= '<small>When set this overrides the purchase button with a link to this URL.</small><br/>';
		$product_content .= '<input type="text" name="_mp_product_link" id="product_link" value="'.$product_link.'" onfocus="this.value=\'http://\'"></div>';
		$product_content .= '<div class="new-product-form"><label for="product_category">Choose at least one category</label>';
		$product_content .= '<p>';
		// getting taxo cat
		$table_taxo = get_terms('product_category', 'orderby=count&hide_empty=0');
		if(count($table_taxo)>=0){
			foreach($table_taxo as $taxo){
				$product_content .='<input type="checkbox" name="product_category[]" id="product_category-'.$taxo->term_id.'" value="'.$taxo->term_id.'">'.$taxo->name.'&nbsp;';
			}
		}
		$product_content .= '</p></div>';

		$product_content .= '<div class="new-product-form"><label for="product_tags">Add your tag(s)</label><br><small>Type your tag, then hit the return or space key to add it</small><br/><input type="text" id="product_tags" name="product_tags"/></div>';

		$product_content .= wp_nonce_field('tradr-check-referrer','tradr-check', true, false);

		$product_content .= '<div class="tradr-action-btn"><input type="submit" name="tradr_submit_product" id="tradr_submit_product" value="Submit your Product &rarr;" class="tradr_product_btn"/></div>';
		$product_content .= '</form>';

		$tradr_products_editor = new TP_Tiny_MCE();

		$tradr_products_editor->tiny_mce(true, array("editor_selector" => "tradr_product_tinymce"));

		echo $product_content;
	}
}

function tradr_products_add_footer_js(){
	if( get_option('tradr_product_form_page_id')!="" && is_page(get_option('tradr_product_form_page_id'))){
		?>
		<script type="text/javascript">
		if ( typeof tb_pathToImage != 'string' )
		{
		    var tb_pathToImage = "<?php echo includes_url('js/thickbox');?>/loadingAnimation.gif";
		}
		if ( typeof tb_closeImage != 'string' )
		{
		    var tb_closeImage = "<?php echo includes_url('js/thickbox');?>/tb-close.png";
		}
		jQuery(document).ready(function() {
            jQuery("#product_tags").tagEditor(
            {
				separator: ' ',
				completeOnSeparator: true,
                completeOnBlur: true,
				confirmRemoval: false
            });
            jQuery("#resetTagsButton").click(function() {
                jQuery("#product_tags").tagEditorResetTags();
            });
        });
		function imath_send_to_editor(h) {
			var ed;

			if ( typeof tinyMCE != 'undefined' && ( ed = tinyMCE.activeEditor ) && !ed.isHidden() ) {
				ed.focus();
				if ( tinymce.isIE )
					ed.selection.moveToBookmark(tinymce.EditorManager.activeEditor.windowManager.bookmark);

				if ( h.indexOf('[caption') === 0 ) {
					if ( ed.plugins.wpeditimage )
						h = ed.plugins.wpeditimage._do_shcode(h);
				} else if ( h.indexOf('[gallery') === 0 ) {
					if ( ed.plugins.wpgallery )
						h = ed.plugins.wpgallery._do_gallery(h);
				} else if ( h.indexOf('[embed') === 0 ) {
					if ( ed.plugins.wordpress )
						h = ed.plugins.wordpress._setEmbed(h);
				}

				ed.execCommand('mceInsertContent', false, h);

			} else if ( typeof edInsertContent == 'function' ) {
				edInsertContent(edCanvas, h);
			} else {
				jQuery( edCanvas ).val( jQuery( edCanvas ).val() + h );
			}

			tb_remove();
		}
		function openFeaturedImg(){
			var featurl = '<?php bloginfo('siteurl');?>/?attachment_id='+jQuery("#tradr_feat_img").val();
			window.open(featurl);
		}
		function removeFeaturedImg(){
			jQuery("#tradr_feat_img").val('');
			jQuery("#tradr_featured_image").hide();
		}
		</script>
		<?php
	}
}
add_action('wp_footer', 'tradr_products_add_footer_js');

add_action('admin_menu', 'tradr_product_form_options');

function tradr_product_form_options(){
	add_submenu_page('edit.php?post_type=product', 'Front end Form options', 'Front end Form options', 'manage_options', 'fe-products_form', 'tradr_product_form_options_page');
}

function tradr_product_form_options_page(){
	if($_POST['id_page_fe_form']){
		update_option('tradr_product_form_page_id', intval($_POST['id_page_fe_form']));
		update_option('_tradr_product_login_message', wp_kses($_POST['message_notlogged'], array()));
		update_option('_tradr_product_moderation_message', wp_kses($_POST['message_moderation'], array()));
		?>
		<div id="message" class="updated"><p>Options saved</p></div>
		<?php
	}
	$id_page_fe_form = get_option('tradr_product_form_page_id');
	$loggedin_message = get_option('_tradr_product_login_message');
	$moderation_message = get_option('_tradr_product_moderation_message');
	?>
	<div class="wrap">
		<h2>Product front end form options</h2>
		<form action="" method="POST">
			<p><label for="id_page_fe_form">Page ID where the form will be displayed</label> 
				<input type="text" value="<?php echo $id_page_fe_form;?>" name="id_page_fe_form"></p>
			<p><label for="message_notlogged">Message to display to not logged in users</label><br/>
				<textarea name="message_notlogged"><?php echo stripslashes($loggedin_message);?></textarea></p>
			<p>	<label for="message_moderation">Message to display once product has successfully been added</label><br/>
				<textarea name="message_moderation"><?php echo stripslashes($moderation_message);?></textarea></p>
			<input type="submit" value="Save these options" class="button-secondary">
		</form>
	</div>
	<?php
}

function tradr_product_allowed_html_tags(){
	$allowed_tp_tags = array(
		'a' => array(
			'class' => array (),
			'href' => array (),
			'id' => array (),
			'title' => array (),
			'rel' => array (),
			'rev' => array (),
			'name' => array (),
			'target' => array()),
		'b' => array(),
		'big' => array(),
		'blockquote' => array(
			'id' => array (),
			'cite' => array (),
			'class' => array(),
			'lang' => array(),
			'xml:lang' => array()),
		'br' => array (
			'class' => array ()),
		'del' => array(
			'datetime' => array ()),
		'em' => array(),
		'font' => array(
			'color' => array (),
			'face' => array (),
			'size' => array ()),
		'h1' => array(
			'align' => array (),
			'class' => array (),
			'id'    => array (),
			'style' => array ()),
		'h2' => array (
			'align' => array (),
			'class' => array (),
			'id'    => array (),
			'style' => array ()),
		'h3' => array (
			'align' => array (),
			'class' => array (),
			'id'    => array (),
			'style' => array ()),
		'h4' => array (
			'align' => array (),
			'class' => array (),
			'id'    => array (),
			'style' => array ()),
		'h5' => array (
			'align' => array (),
			'class' => array (),
			'id'    => array (),
			'style' => array ()),
		'h6' => array (
			'align' => array (),
			'class' => array (),
			'id'    => array (),
			'style' => array ()),
		'hr' => array (
			'align' => array (),
			'class' => array (),
			'noshade' => array (),
			'size' => array (),
			'width' => array ()),
		'i' => array(),
		'img' => array(
			'alt' => array (),
			'align' => array (),
			'border' => array (),
			'class' => array (),
			'height' => array (),
			'hspace' => array (),
			'longdesc' => array (),
			'vspace' => array (),
			'src' => array (),
			'style' => array (),
			'width' => array ()),
		'li' => array (
			'align' => array (),
			'class' => array ()),
		'p' => array(
			'class' => array (),
			'align' => array (),
			'dir' => array(),
			'lang' => array(),
			'style' => array (),
			'xml:lang' => array()),
		'span' => array (
			'class' => array (),
			'dir' => array (),
			'align' => array (),
			'lang' => array (),
			'style' => array (),
			'title' => array (),
			'xml:lang' => array()),
		'strike' => array(),
		'strong' => array(),
		'table' => array(
			'align' => array (),
			'bgcolor' => array (),
			'border' => array (),
			'cellpadding' => array (),
			'cellspacing' => array (),
			'class' => array (),
			'dir' => array(),
			'id' => array(),
			'rules' => array (),
			'style' => array (),
			'summary' => array (),
			'width' => array ()),
		'tbody' => array(
			'align' => array (),
			'char' => array (),
			'charoff' => array (),
			'valign' => array ()),
		'td' => array(
			'abbr' => array (),
			'align' => array (),
			'axis' => array (),
			'bgcolor' => array (),
			'char' => array (),
			'charoff' => array (),
			'class' => array (),
			'colspan' => array (),
			'dir' => array(),
			'headers' => array (),
			'height' => array (),
			'nowrap' => array (),
			'rowspan' => array (),
			'scope' => array (),
			'style' => array (),
			'valign' => array (),
			'width' => array ()),
		'tfoot' => array(
			'align' => array (),
			'char' => array (),
			'class' => array (),
			'charoff' => array (),
			'valign' => array ()),
		'th' => array(
			'abbr' => array (),
			'align' => array (),
			'axis' => array (),
			'bgcolor' => array (),
			'char' => array (),
			'charoff' => array (),
			'class' => array (),
			'colspan' => array (),
			'headers' => array (),
			'height' => array (),
			'nowrap' => array (),
			'rowspan' => array (),
			'scope' => array (),
			'valign' => array (),
			'width' => array ()),
		'thead' => array(
			'align' => array (),
			'char' => array (),
			'charoff' => array (),
			'class' => array (),
			'valign' => array ()),
		'tr' => array(
			'align' => array (),
			'bgcolor' => array (),
			'char' => array (),
			'charoff' => array (),
			'class' => array (),
			'style' => array (),
			'valign' => array ()),
		'u' => array(),
		'ul' => array (
			'class' => array (),
			'style' => array (),
			'type' => array ()),
		'ol' => array (
			'class' => array (),
			'start' => array (),
			'style' => array (),
			'type' => array ())
			);
	return $allowed_tp_tags;
}
?>