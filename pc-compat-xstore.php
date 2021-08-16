<?php
/**
 * Plugin Name: Compatibility between Xstore and Product Configurator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Xstore theme compatibility Class.
 */
class Iconic_PC_Compat_Xstore {
	/**
	 * Init.
	 */
	public static function run() {
		add_action( 'plugins_loaded', array( __CLASS__, 'hooks' ) );
	}

	/**
	 * Add hooks.
	 */
	public static function hooks() {
		if ( ! function_exists( 'initial_ETC' ) || ! class_exists( 'Iconic_WPC' ) ) {
			return;
		}

		remove_filter( 'woocommerce_dropdown_variation_attribute_options_html', array( 'Iconic_PC_Product', 'dropdown_variation_attribute_options_html' ), 10 );
		add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array( __CLASS__, 'insert_jckpc_id_to_non_swatches_attributes' ), 10, 2 );
		add_filter( 'et_single_overwrite_select_swatches', array( __CLASS__, 'insert_jckpc_id_to_swatches_attributes' ), 10, 3 );
	}


	/**
	 * Assign data-iconic_pc_layer_id attribute to the select tag
	 * For those attributes which doesn't have swatches enabled.
	 *
	 * @param string $html HTML.
	 * @param array  $args Arguments.
	 *
	 * @return string
	 */
	public static function insert_jckpc_id_to_non_swatches_attributes( $html, $args ) {
		global $st_woo_swatches;

		$attribute = self::get_tax_attribute( $args['attribute'] );

		$swatch_enabled = null;

		if ( empty( $attribute ) ) {
			$swatch_enabled = false;
		} else {
			$swatch_enabled = 'st-' === substr( $attribute->attribute_type, 0, 3 );
		}

		// if attribute has swatch enabled then return the HTML without any changes.
		// compat_xstore_insert_jckpc_id_to_swatches_attributes will perform the replacement
		// later for swatches.
		if ( $swatch_enabled ) {
			return $html;
		}

		$html = str_replace( 'select id', 'select data-iconic_pc_layer_id="' . Iconic_PC_Helpers::sanitise_str( $args['attribute'], '', false ) . '" id', $html );

		return $html;
	}

	/**
	 * Assign data-iconic_pc_layer_id attribute to the select tag
	 * For those attributes which have swatches enabled.
	 *
	 * @param string $form Form HTML.
	 * @param string $swatch    Swatch HTML.
	 * @param array  $taxonomy  Taxonomy.
	 *
	 * @return string
	 */
	public static function insert_jckpc_id_to_swatches_attributes( $form, $swatch, $taxonomy ) {
		$pattern     = '/<select id="(' . $taxonomy . ')"/U';
		$replacement = '<select id="${1}" data-iconic_pc_layer_id="${1}"';
		$form        = preg_replace( $pattern, $replacement, $form );

		return $form;
	}

	/**
	 * Fetch attribute data from the database.
	 *
	 * @param string $taxonomy Taxonomy.
	 *
	 * @return object|null
	 */
	public static function get_tax_attribute( $taxonomy ) {
		global $wpdb;

		$attr = substr( $taxonomy, 3 );
		$attr = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name = '$attr'" );

		return $attr;
	}
}

Iconic_PC_Compat_Xstore::run();
