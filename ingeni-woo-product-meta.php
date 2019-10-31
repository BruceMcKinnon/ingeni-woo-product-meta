<?php
/*
Plugin Name: Ingeni Woo Product Meta
Version: 2019.01
Plugin URI: http://ingeni.net
Author: Bruce McKinnon - ingeni.net
Author URI: http://ingeni.net
Description: Adds additional fields to Woo products and adds them to the standard Woo JSON-LD meta tags
*/

/*
Copyright (c) 2019 Ingeni Web Solutions
Released under the GPL license
http://www.gnu.org/licenses/gpl.txt

Disclaimer: 
	Use at your own risk. No warranty expressed or implied is provided.
	This program is free software; you can redistribute it and/or modify 
	it under the terms of the GNU General Public License as published by 
	the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
 	See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


Requires : Wordpress 3.x or newer ,PHP 5 +



Thanks for the helpful tips:
https://www.cloudways.com/blog/add-custom-product-fields-woocommerce/
https://shanerutter.co.uk/fix-for-woocommerce-schema-data-missing-brand-and-mpn/


v2019.01 - Initial version

*/


// Display Fields
add_action('woocommerce_product_options_general_product_data', 'ingeni_woo_product_custom_fields');

// Save Fields
add_action('woocommerce_process_product_meta', 'ingeni_woo_product_custom_fields_save');


function ingeni_woo_product_custom_fields()
{
    global $woocommerce, $post;
    echo '<div class="ingeni_wooproduct_custom_field">';
    // Custom fields
    //
    // Brand
    woocommerce_wp_text_input(
        array(
            'id' => '_ingeni_woo_product_brand_field',
            'placeholder' => 'Product Brand',
            'label' => __('Brand', 'woocommerce'),
            'desc_tip' => 'true'
        )
    );
    // MPN - Manf Part No.
    woocommerce_wp_text_input(
        array(
            'id' => '_ingeni_woo_product_mpn_field',
            'placeholder' => 'Defaults to SKU if none provided',
            'label' => __('Manf. Part Number', 'woocommerce'),
            'desc_tip' => 'true'
        )
	);	

/*
    //Custom Product Number Field
    woocommerce_wp_text_input(
        array(
            'id' => '_custom_product_number_field',
            'placeholder' => 'Custom Product Number Field',
            'label' => __('Custom Product Number Field', 'woocommerce'),
            'type' => 'number',
            'custom_attributes' => array(
                'step' => 'any',
                'min' => '0'
            )
        )
    );
    //Custom Product  Textarea
    woocommerce_wp_textarea_input(
        array(
            'id' => '_custom_product_textarea',
            'placeholder' => 'Custom Product Textarea',
            'label' => __('Custom Product Textarea', 'woocommerce')
        )
    );
*/
    echo '</div>';

}

function ingeni_woo_product_custom_fields_save($post_id)
{
    // Custom Product Text Fields
    $ingeni_woo_brand_field = $_POST['_ingeni_woo_product_brand_field'];
    update_post_meta($post_id, '_ingeni_woo_product_brand_field', esc_attr($ingeni_woo_brand_field));

    $ingeni_woo_mpn_field = $_POST['_ingeni_woo_product_mpn_field'];
    update_post_meta($post_id, '_ingeni_woo_product_mpn_field', esc_attr($ingeni_woo_mpn_field));

     
/*
// Custom Product Number Field
    $woocommerce_custom_product_number_field = $_POST['_custom_product_number_field'];
    if (!empty($woocommerce_custom_product_number_field))
        update_post_meta($post_id, '_custom_product_number_field', esc_attr($woocommerce_custom_product_number_field));
// Custom Product Textarea Field
    $woocommerce_custom_procut_textarea = $_POST['_custom_product_textarea'];
    if (!empty($woocommerce_custom_procut_textarea))
        update_post_meta($post_id, '_custom_product_textarea', esc_html($woocommerce_custom_procut_textarea));
*/
}


//
// Add the custom fields to the standard Woo JSON-LD meta tags on the each product page
//
add_filter( 'woocommerce_structured_data_product', 'ingeni_woo_get_custom_markup');

function ingeni_woo_get_custom_markup( $markup ) {
    global $product;

    // Add a brand
    $brand = get_post_meta($product->id, '_ingeni_woo_product_brand_field', true);
    if ( strlen(trim($brand)) > 0 ) {
        $markup['brand'] = $brand;
    }

    // Add the MPN (Manf Part Number in place of a Global Idnetifier. In this case we use the SKU if none provided)
    $mpn = get_post_meta($product->id, '_ingeni_woo_product_mpn_field', true);
    if ( strlen(trim($mpn)) == 0 ) {
        $mpn = $product->get_sku();
    }
    $markup['mpn'] = $mpn;
    

    // Return the revised product markup
    return $markup;
}



function ingeni_load_woo_product_meta() {
	// Init auto-update from GitHub repo
	require 'plugin-update-checker/plugin-update-checker.php';
	$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
		'https://github.com/BruceMcKinnon/ingeni-woo-product-meta',
		__FILE__,
		'ingeni-woo-product-meta'
	);
}
add_action( 'wp_enqueue_scripts', 'ingeni_load_woo_product_meta' );


// Plugin activation/deactivation hooks
function ingeni_woo_product_activation() {
	flush_rewrite_rules( false );
}
register_activation_hook(__FILE__, 'ingeni_woo_product_activation');

function ingeni_woo_product_deactivation() {
  flush_rewrite_rules( false );
}
register_deactivation_hook( __FILE__, 'ingeni_woo_product_deactivation' );

?>