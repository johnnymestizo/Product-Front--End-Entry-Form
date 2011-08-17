<?php
global $wp_tradr_products_submit_errors, $current_user, $wpdb;
if(!wp_verify_nonce($_POST['tradr-check'],'tradr-check-referrer')){
	wp_die('You need to use the site form to submit a product');
}
if( !is_user_logged_in() ){
	wp_die('You need to be a member of this site to submit a product');
}

if(!$_POST["product_title"]){
	$wp_tradr_products_submit_errors[] = 'Name of the product is required';
}
if(!$_POST["product_content"]){
	$wp_tradr_products_submit_errors[] = 'Description of your product is required';
}
if(!$_POST["product_category"] || count($_POST["product_category"])==0){
	$wp_tradr_products_submit_errors[] = 'Category is required';
}

if(count($wp_tradr_products_submit_errors)>0){
	return false;
}

/* post_meta default */
$mp_var_name[0] = "";
$mp_sku[0] = "";
$mp_price[0] = "0";
$mp_is_sale = 0;
$mp_sale_price = "";
$mp_track_inventory	= 0;
$mp_inventory = "";
$mp_product_link = ""; 
$mp_sales_count = 0;
$mp_shipping['extra_cost']="0";
$mp_file ="";

/* post_meta posted value */
if($_POST['_mp_sku']) $mp_sku[0] = wp_kses($_POST['_mp_sku'], array());
if($_POST['_mp_price']) $mp_price[0] = intval($_POST['_mp_price']);
if($_POST['_mp_inventory'] && intval($_POST['_mp_inventory']) > 0){
	$mp_track_inventory	= 1;
	$mp_inventory[0] = intval($_POST['_mp_inventory']);
}
if($_POST['_mp_product_link'] && strlen($_POST['_mp_product_link']) > 7) $mp_product_link = wp_kses($_POST['_mp_product_link'], array());


$post_title = wp_kses($_POST["product_title"], array());
$post_content = wp_kses($_POST["product_content"], tradr_product_allowed_html_tags());
$post_author = $current_user->ID;
$post_category = $_POST["product_category"];

//saves product as draft
$post = array(
'post_author'	=> $post_author,
'post_title'	=> $post_title,
'post_content'	=> $post_content,
'post_status'	=> 'draft',
'post_type'		=> 'product'
);
$post_id = wp_insert_post($post);

//sets category
if($post_category) wp_set_post_terms( $post_id, $post_category, 'product_category', false);

//sets tags :
if($_POST['product_tags']) wp_set_post_terms( $post_id, str_replace(' ',',', $_POST['product_tags']), 'product_tag', false);

//adds postmeta
add_post_meta($post_id, 'mp_var_name', $mp_var_name, true);
add_post_meta($post_id, 'mp_sku', $mp_sku, true);
add_post_meta($post_id, 'mp_price', $mp_price, true); 
add_post_meta($post_id, 'mp_is_sale', $mp_is_sale, true); 
add_post_meta($post_id, 'mp_sale_price', $mp_sale_price, true); 	 
add_post_meta($post_id, 'mp_track_inventory', $mp_track_inventory, true); 
add_post_meta($post_id, 'mp_inventory', $mp_inventory, true); 
add_post_meta($post_id, 'mp_product_link', $mp_product_link, true); 	 
add_post_meta($post_id, 'mp_sales_count', $mp_sales_count, true); 
add_post_meta($post_id, 'mp_shipping', $mp_shipping, true); 
add_post_meta($post_id, 'mp_file', $mp_file, true);

//takes care of image attachment
$images = explode(',', substr($_POST['tradr_id_img_list'], 0, -1) );
foreach($images as $image){
	$wpdb->update($wpdb->posts, array('post_parent' => $post_id), array('ID' => $image) );
}

//takes care of thumbnail if needed
if (intval($_POST['tradr_id_img_list']) > 0) update_post_meta($post_id, '_thumbnail_id', intval($_POST['tradr_id_img_list']));

//redirect to moderation message
wp_redirect( get_permalink(get_option('tradr_product_form_page_id')) .'?moderation=1');
?>