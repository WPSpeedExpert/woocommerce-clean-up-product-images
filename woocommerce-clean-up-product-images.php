<?php
/*
Plugin Name:          WooCommerce Clean Up Product Images
Description:          Automatically delete a product's attached images when the product is deleted in WooCommerce.
Version:              1.0.0
WC requires at least: 4.0.0
WC tested up to:      9.4.2
Author:               OctaHexa Media LLC
Author URI:           https://octahexa.com
Text Domain:          woocommerce-clean-up-product-images
Domain Path:          /languages
License:              GPLv2 or later
GitHub Plugin URI:    https://github.com/WPSpeedExpert/woocommerce-clean-up-product-images
GitHub Branch:        main
Primary Branch:       main
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if WooCommerce is active
 */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('before_delete_post', 'woocommerce_clean_up_product_images', 10, 1);

    /**
     * Delete attached images when a WooCommerce product is deleted
     *
     * @param int $post_id The ID of the post being deleted.
     */
    function woocommerce_clean_up_product_images($post_id)
    {
        // Ensure this is a WooCommerce product
        if (get_post_type($post_id) !== 'product') {
            return;
        }

        global $wpdb;

        // Get all attachments for the product
        $args = [
            'post_parent' => $post_id,
            'post_type'   => 'attachment',
            'numberposts' => -1,
            'post_status' => 'any',
        ];

        $attachments = get_children($args);

        // Delete attachments securely
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                wp_delete_attachment($attachment->ID, true); // Delete attachment file
                $wpdb->delete($wpdb->postmeta, ['post_id' => $attachment->ID], ['%d']); // Clean up metadata
                wp_delete_post($attachment->ID, true); // Remove post record
            }
        }
    }

} else {
    /**
     * Admin notice for missing WooCommerce dependency
     */
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error is-dismissible"><p>';
        esc_html_e(
            'WooCommerce plugin is not activated. Please install and activate WooCommerce to use the WooCommerce Clean Up Product Images plugin.',
            'woocommerce-clean-up-product-images'
        );
        echo '</p></div>';
    });
}
