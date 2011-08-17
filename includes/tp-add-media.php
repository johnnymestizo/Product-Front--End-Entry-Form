<?php
/** Load WordPress Administration Bootstrap */
if(file_exists('../../../wp-load.php')) {
	require_once("../../../wp-load.php");
} else if(file_exists('../../wp-load.php')) {
	require_once("../../wp-load.php");
} else if(file_exists('../wp-load.php')) {
	require_once("../wp-load.php");
} else if(file_exists('wp-load.php')) {
	require_once("wp-load.php");
} else if(file_exists('../../../../wp-load.php')) {
	require_once("../../../../wp-load.php");
} else if(file_exists('../../../../wp-load.php')) {
	require_once("../../../../wp-load.php");
} else {

	if(file_exists('../../../wp-config.php')) {
		require_once("../../../wp-config.php");
	} else if(file_exists('../../wp-config.php')) {
		require_once("../../wp-config.php");
	} else if(file_exists('../wp-config.php')) {
		require_once("../wp-config.php");
	} else if(file_exists('wp-config.php')) {
		require_once("wp-config.php");
	} else if(file_exists('../../../../wp-config.php')) {
		require_once("../../../../wp-config.php");
	} else if(file_exists('../../../../wp-config.php')) {
		require_once("../../../../wp-config.php");
	} else {
		echo '<p>Failed to load bootstrap.</p>';
		exit;
	}

}
require_once(ABSPATH.'wp-admin/admin.php');
wp_enqueue_script( 'common' );
wp_enqueue_script( 'jquery-color' );

@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
	<title><?php bloginfo('name') ?> &rsaquo; <?php _e('Uploads'); ?> &#8212; <?php _e('WordPress'); ?></title>
	<?php
		wp_enqueue_style( 'global' );
		wp_enqueue_style( 'wp-admin' );
		wp_enqueue_style( 'colors' );
		wp_enqueue_style( 'media' );
		wp_enqueue_style('style-tradr', TRADR_PRODUCTS_PLUGIN_URL .'/css/style.css');
	?>
	<script type="text/javascript">
	//<![CDATA[
		function addLoadEvent(func) {if ( typeof wpOnload!='function'){wpOnload=func;}else{ var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}}
	//]]>
	</script>
	<?php
	do_action('admin_print_styles');
	do_action('admin_print_scripts');
	do_action('admin_head');
	if ( isset($content_func) && is_string($content_func) )
		do_action( "admin_head_{$content_func}" );
	?>
</head>
<body>
	<?php
	//preg_match('!^image/!', get_post_mime_type( $attachment ) 
	if($_POST && $_FILES["tp_upload_image"]["name"] && preg_match('!^image/!', $_FILES["tp_upload_image"]["type"])){
		  // check to make sure its a successful upload
		  if ($_FILES['tp_upload_image']['error'] !== UPLOAD_ERR_OK) __return_false();
		  

		  require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		  require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		  require_once(ABSPATH . "wp-admin" . '/includes/media.php');

		  $attach_id = media_handle_upload( 'tp_upload_image', $post_id );
		
		
		$upload_dir = wp_upload_dir();
		$img_data = wp_get_attachment_metadata( $attach_id );
		$img_dir_array = explode('/', $img_data["file"]);
		$img_dir = $upload_dir['baseurl'] .'/'. $img_dir_array[0] .'/'. $img_dir_array[1];

		//if too small
		if(!$img_data["sizes"]["thumbnail"]["file"]) $img_preview = $upload_dir['baseurl'] .'/'. $img_data["file"];
		else{
			$img_preview =   $img_dir .'/'. $img_data["sizes"]["thumbnail"]["file"] ;
		}
		?>
		<div style="margin:25px">
			<div id="message" class="updated"><p>Your image was successfully uploaded</p></div>
			<div class="wrap">
			<div class='media-item'>
				<table class="tradr-media-container">
					<thead class='media-item-info'>
						<tr valign='top'>
							<td class='A1B1'><p style="text-align:center"><img src="<?php echo $img_preview;?>"></p></td>
						</tr>
					</thead>
					<tbody>
						<tr><td><p><label for="img_alt">Alternative text</label> <input type="text" name="alt_text" id="alt_text" style="width:60%"><input type="hidden" name="img_title" id="img_title" value="<?php echo $img_dir_array[2];?>"></p></td></tr>
						<tr><td>
							<table class="tradr_image_editor">
								<tr><th colspan="4">Image alignment</th></tr>
								<tr>
									<td>
										<input type="radio" name="img_align" value="alignnone" checked>None
									</td>
									<td>
										<input type="radio" name="img_align" value="alignleft">Left
									</td>
									<td>
										<input type="radio" name="img_align" value="alignright">Right
									</td>
									<td>
										<input type="radio" name="img_align" value="aligncenter">Center
									</td>
								</tr>
							</table>
						</td></tr>
						<tr><td>
							<table class="tradr_image_editor">
								<tr><th colspan="3">Image Size</th></tr>
								<tr>
									<?php if($img_data["sizes"]["thumbnail"]["file"]):?>
										<td>
											<input type="radio" name="img_size" value="<?php echo $img_dir .'/'. $img_data["sizes"]["thumbnail"]["file"];?>" class="size-thumbnail" checked>Thumbnail<br/>(<?php echo $img_data["sizes"]["thumbnail"]["width"];?>x<?php echo $img_data["sizes"]["thumbnail"]["height"];?>)
										</td>
									<?php endif;?>
									<?php if($img_data["sizes"]["medium"]["file"]):?>
										<td>
											<input type="radio" name="img_size" value="<?php echo $img_dir .'/'. $img_data["sizes"]["medium"]["file"];?>" class="size-medium">Medium<br/>(<?php echo $img_data["sizes"]["medium"]["width"];?>x<?php echo $img_data["sizes"]["medium"]["height"];?>)
										</td>
									<?php endif;?>
										<td>
											<input type="radio" name="img_size" value="<?php echo $upload_dir['baseurl'] .'/'. $img_data["file"];?>" class="size-medium" <?php if(!$img_data["sizes"]["thumbnail"]["file"]) echo "checked";?>>Full size<br/>(<?php echo $img_data["width"];?>x<?php echo $img_data["height"];?>)</td>
								</tr>
							</table>
						</td></tr>
						<tr>
							<td><input type="button" value="Insert Image" class="tradr_insert_btn button-secondary" rel="<?php echo $attach_id;?>">
							&nbsp;<span id="set_as_featured"><a href="javascript:void(0)" class="add_featured_btn button-secondary" rel="<?php echo $attach_id;?>">Use as featured image</a></span><span id="remove_as_featured" style="display:none"><a href="javascript:void(0)" class="remove_featured_btn button-secondary">Remove featured image</a>&nbsp;<a href="javascript:self.parent.tb_remove()" class="button-secondary">Close this window</a></span>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			</div>
		<?php
	}
	else{
		$upload_size_unit = $max_upload_size =  wp_max_upload_size();
		$sizes = array( 'KB', 'MB', 'GB' );
		for ( $u = -1; $upload_size_unit > 1024 && $u < count( $sizes ) - 1; $u++ )
			$upload_size_unit /= 1024;
		if ( $u < 0 ) {
			$upload_size_unit = 0;
			$u = 0;
		} else {
			$upload_size_unit = (int) $upload_size_unit;
		}
		?>
		<div style="margin:25px">
			<?php if($_POST):?>
				<div id="message" class="error"><p>Something went wrong with your upload, only images can be uploaded..</p></div>
			<?php endif;?>
		<form enctype="multipart/form-data" method="post" action="">
			<div id="tp_upload_form">
			<p>
			<input type="file" name="tp_upload_image" id="tp_upload_image">
			&nbsp;<input type="submit" value="Upload your image" class="button-secondary">
			&nbsp;<input type="button" value="Add external url" class="tp_add_url button-secondary">
			</p>
			<p class="media-upload-size"><?php printf( __( 'Maximum upload file size: %d%s' ), $upload_size_unit, $sizes[$u] ); ?></p>
			</div>
			<div id="tp_add_image" style="display:none">
				<div class="wrap">
					<div class='media-item'>
						<table class="tradr-media-container">
							<tbody>
								<tr>
									<td>
										<table class="tradr_image_editor">
											<tr><td><label for="tp_upload_image">Image Url</label></td><td><input type="text" name="url_image" id="url_image" style="width:80%"></td></tr>
											<tr><td><label for="tp_alt_text">Alternative text</label></td><td><input type="text" name="tp_alt_text" id="tp_alt_text" style="width:60%"></td></tr>
											<tr><td><label for="tp_width_image">Image width</label></td><td><input type="text" name="tp_width_image" id="tp_width_image" style="width:60%"></td></tr>
										</table>
									</td>
								</tr>
								<tr>
									<td>
										<table class="tradr_image_editor">
											<tr><th colspan="4">Image alignment</th></tr>
											<tr>
												<td style="text-align:center">
													<input type="radio" name="url_img_align" value="alignnone" checked>None
												</td>
												<td style="text-align:center">
													<input type="radio" name="url_img_align" value="alignleft">Left
												</td>
												<td style="text-align:center">
													<input type="radio" name="url_img_align" value="alignright">Right
												</td>
												<td style="text-align:center">
													<input type="radio" name="url_img_align" value="aligncenter">Center
												</td>
											</tr>
										</table>
										</td>
									</tr>
									<tr>
										<td>
											<p><a href="javascript:void(0)" class="tradr_url_insert button-secondary">Insert Image</a>&nbsp;
												<a href="javascript:void(0)" class="tradr_display_form button-secondary">Back to upload form</a>
											</p>
										</td>
									</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</form>
		</div>
		<?php
	}
	?>
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery(".tp_add_url").click(function(){
			jQuery("#tp_add_image").show();
			jQuery("#tp_upload_form").hide();
		});
		jQuery(".tradr_display_form").click(function(){
			jQuery("#tp_add_image").hide();
			jQuery("#tp_upload_form").show();
		});
	});
	jQuery('.tradr_insert_btn').click(function(){
		if(jQuery("#tradr_id_img_list", top.document).val().indexOf(jQuery(this).attr("rel")+',')==-1){
			jQuery("#tradr_id_img_list", top.document).val(jQuery("#tradr_id_img_list", top.document).val()+jQuery(this).attr("rel")+',');
		}
		var win = window.dialogArguments || opener || parent || top;
		if("aligncenter" == jQuery("input[name=img_align]:checked").val()){
			img_post = '<p style="text-align: center;"><a href="'+jQuery("input[name=img_size]:checked").val()+'" title="'+jQuery("#img_title").val()+'"><img src="'+jQuery("input[name=img_size]:checked").val()+'" class="'+jQuery("input[name=img_size]:checked").attr('class')+' '+jQuery("input[name=img_align]:checked").val()+'" alt="'+jQuery("#alt_text").val()+'"></a></p>';
		}
		else img_post = '<a href="'+jQuery("input[name=img_size]:checked").val()+'" title="'+jQuery("#img_title").val()+'"><img src="'+jQuery("input[name=img_size]:checked").val()+'" class="'+jQuery("input[name=img_size]:checked").attr('class')+' '+jQuery("input[name=img_align]:checked").val()+'" alt="'+jQuery("#alt_text").val()+'"></a>';
		if(img_post){
			win.imath_send_to_editor(img_post);
		}
		
	});
	jQuery('.tradr_url_insert').click(function(){
		var win = window.dialogArguments || opener || parent || top;
		img_post = jQuery('#url_image').val();
		width_img = parseInt(jQuery('#tp_width_image').val());
		if(img_post){
			if("aligncenter" == jQuery("input[name=url_img_align]:checked").val()){
				win.imath_send_to_editor('<p style="text-align:center;"><img src="'+img_post+'" width="'+width_img+'px" alt="'+jQuery("#tp_alt_text").val()+'" class="'+jQuery("input[name=url_img_align]:checked").val()+'"></p>');
			}
			else win.imath_send_to_editor('<img src="'+img_post+'" width="'+width_img+'px" alt="'+jQuery("#tp_alt_text").val()+'"  class="'+jQuery("input[name=url_img_align]:checked").val()+'">');
		}
		else alert('add an url');
	});
	jQuery(".add_featured_btn").click(function(){
		jQuery("#tradr_feat_img", top.document).val(jQuery(this).attr("rel"));
		jQuery("#tradr_id_img_list", top.document).val(jQuery("#tradr_id_img_list", top.document).val()+jQuery(this).attr("rel")+',');
		jQuery("#tradr_featured_image", top.document).show();
		jQuery("#set_as_featured").hide();
		jQuery("#remove_as_featured").show();
	});
	jQuery(".remove_featured_btn").click(function(){
		jQuery("#tradr_feat_img", top.document).val('');
		jQuery("#tradr_featured_image", top.document).hide();
		jQuery("#set_as_featured").show();
		jQuery("#remove_as_featured").hide();
	});
</script>
</body>
</html>