<?php
/**
 * Badge Factor 2
 * Copyright (C) 2021 ctrlweb
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @package Badge_Factor_2_Certificates
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.VariableConstantNameFound
 */

namespace BadgeFactor2;

use BadgeFactor2\Admin\CMB2_Fields\PDF_Field;
use BadgeFactor2\Helpers\Constant;
use BadgeFactor2\Post_Types\Course_Certificate_Extension;

class BF2_Certificates {

	/**
	 * The single instance of the class.
	 *
	 * @var BF2_Certificates
	 * @since 2.0.0-alpha
	 */
	protected static $_instance = null;

	/**
	 * Main BadgeFactor 2 Certificates Add-On Instance.
	 *
	 * Ensures only one instance of BadgeFactor 2 Certificates Add-On is loaded or can be loaded.
	 *
	 * @return BF2_Certificates - Main instance.
	 * @since 1.0.0-alpha
	 * @static
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * BadgeFactor2 Constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Badge Factor 2 Init Hooks.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		//Certificates_Public::init_hooks();
		Course_Certificate_Extension::init_hooks();
		Certificates_Public::init_hooks();

		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			Certificates_Admin::init_hooks();
			PDF_Field::init_hooks();
		}
	}

	/**
	 * Define BadgeFactor2 Constants.
	 *
	 * @return void
	 */
	private function define_constants() {
		Constant::define( 'BF2_CERTIFICATES_DATA', get_plugin_data( BF2_CERTIFICATES_FILE ) );
	}

	/**
	 * Badge Factor 2 Includes.
	 *
	 * @return void
	 */
	public function includes() {
		require_once plugin_dir_path( __FILE__ ) . '../vendor/autoload.php';
		require_once plugin_dir_path( __FILE__ ) . 'controllers/class-certificate-controller.php';
		require_once plugin_dir_path( __FILE__ ) . 'public/class-certificates-public.php';
		require_once plugin_dir_path( __FILE__ ) . 'post-types/class-course-certificate-extension.php';
		require_once plugin_dir_path( __FILE__ ) . 'helpers/class-certificate-helper.php';

		// Admin / CLI classes.
		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'admin/class-certificates-admin.php';
			require_once plugin_dir_path( __FILE__ ) . 'admin/cmb2-fields/class-pdf-field.php';
		}
	}

}
