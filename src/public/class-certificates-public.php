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

class Certificates_Public {

	/**
	 * BadgeFactorCertificates constructor.
	 */
	function __construct() {
		// Plugin constants
		$this->basename       = plugin_basename( __FILE__ );
		$this->directory_path = plugin_dir_path( __FILE__ );
		$this->directory_url  = plugin_dir_url( __FILE__ );

		// Load translations
		load_plugin_textdomain( 'badgefactor_cert', false, basename( dirname( __FILE__ ) ) . '/languages' );

		// Activation / deactivation hooks
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 300 );
		add_action( 'init', array( $this, 'create_cpt_certificate' ) );

		add_action( 'parse_request', array( $this, 'display_certificate' ) );
		add_action( 'acf/save_post', array( $this, 'save_certificate_template' ), 20 );

	}


	function display_notices() {
		?>
		<div class="error">
			<p><strong><?php esc_html_e( 'Badge Factor Certificates Installation Problem', 'badgefactor_cert' ); ?></strong></p>

			<p><?php esc_html_e( 'The minimum requirements for Badge Factor Certificates have not been met. Please fix the issue(s) below to continue:', 'badgefactor_cert' ); ?></p>
			<ul style="padding-bottom: 0.5em">
				<?php foreach ( $this->notices as $notice ) : ?>
					<li style="padding-left: 20px;list-style: inside"><?php echo $notice; ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}


	/**
	 * Check if Badge Factor version is compatible
	 *
	 * @return boolean Whether compatible or not
	 *
	 * @since 1.0.0
	 */
	public function is_compatible_bf_version() {

		/* Gravity Forms version not compatible */
		if ( ! class_exists( 'BadgeFactor' ) || ! version_compare( BadgeFactor::$version, $this->required_bf_version, '>=' ) ) {
			$this->notices[] = sprintf( esc_html__( '%1$sBadge Factor%2$s Version %3$s is required.', 'badgefactor_cert' ), '<a href="https://github.com/DigitalPygmalion/badge-factor">', '</a>', $this->required_bf_version );

			return false;
		}

		return true;
	}


	/**
	 * admin_menu hook.
	 */
	public function admin_menu() {
		add_submenu_page( 'badgeos_badgeos', __( 'Badge Factor Options', 'badgefactor' ), __( 'Certificates Settings', 'badgefactor_cert' ), 'manage_options', 'badgefactor_cert', array( $this, 'admin_options' ) );
	}


	/**
	 * add_options_page hook.
	 */
	public function badgefactor_options() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include( 'admin/settings-page.tpl.php' );
	}


	/**
	 * init hook to create the certificate post type attached to a badge
	 */
	public function create_cpt_certificate() {
		// Register the post type
		/*        register_post_type( 'certificate', array(
			'labels'             => array(
				'name'               => __( 'Certificates', 'badgefactor_cert'),
				'singular_name'      => __( 'Certificate', 'badgefactor_cert'),
				'add_new'            => __( 'Add New', 'badgefactor_cert' ),
				'add_new_item'       => __( 'Add New Certificate', 'badgefactor_cert' ),
				'edit_item'          => __( 'Edit Certificate', 'badgefactor_cert' ),
				'new_item'           => __( 'New Certificate', 'badgefactor_cert' ),
				'all_items'          => __( 'Certificates', 'badgefactor_cert'),
				'view_item'          => __( 'View Certificates', 'badgefactor_cert' ),
				'search_items'       => __( 'Search Certificates', 'badgefactor_cert' ),
				'not_found'          => __( 'No certificate found', 'badgefactor_cert' ),
				'not_found_in_trash' => __( 'No certificate found in Trash', 'badgefactor_cert' ),
				'parent_item_colon'  => '',
				'menu_name'          => 'Certificates',
			),
			'rewrite' => array(
				'slug' => 'certificate',
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => current_user_can( badgeos_get_manager_capability() ),
			'show_in_menu'       => 'badgeos_badgeos',
			'query_var'          => true,
			'exclude_from_search'=> true,
			'capability_type'    => 'post',
			'hierarchical'       => true,
			'menu_position'      => null,
			'supports'           => array( 'title' )
		) );*/

		if ( function_exists( 'register_field_group' ) ) :

			register_field_group(
				array(
					'id'         => 'acf_certificate',
					'title'      => 'Certificate',
					'fields'     => array(
						array(
							'key'         => 'field_59159115271cd',
							'label'       => 'Certificate Template File (PDF)',
							'name'        => 'template',
							'type'        => 'file',
							'required'    => 1,
							'save_format' => 'object',
							'library'     => 'all',
						),
						array(
							'key'             => 'field_59159159271ce',
							'label'           => 'Associated Badge',
							'name'            => 'badge',
							'type'            => 'relationship',
							'return_format'   => 'object',
							'post_type'       => array(
								0 => 'badges',
							),
							'taxonomy'        => array(
								0 => 'all',
							),
							'filters'         => array(
								0 => 'search',
							),
							'result_elements' => array(
								0 => 'post_type',
								1 => 'post_title',
							),
							'max'             => '',
						),
						array(
							'key'           => 'field_59159bb617306',
							'label'         => 'Recipient Name Position (x)',
							'name'          => 'recipient_name_position_x',
							'type'          => 'number',
							'required'      => 1,
							'default_value' => '',
							'placeholder'   => '',
							'prepend'       => '',
							'append'        => '',
							'min'           => '',
							'max'           => '',
							'step'          => '',
						),
						array(
							'key'           => 'field_59159bf417307',
							'label'         => 'Recipient Name Position (y)',
							'name'          => 'recipient_name_position_y',
							'type'          => 'number',
							'required'      => 1,
							'default_value' => '',
							'placeholder'   => '',
							'prepend'       => '',
							'append'        => '',
							'min'           => '',
							'max'           => '',
							'step'          => '',
						),
						array(
							'key'           => 'field_59159c0617308',
							'label'         => 'Issue Date Position (x)',
							'name'          => 'issue_date_position_x',
							'type'          => 'number',
							'required'      => 1,
							'default_value' => '',
							'placeholder'   => '',
							'prepend'       => '',
							'append'        => '',
							'min'           => '',
							'max'           => '',
							'step'          => '',
						),
						array(
							'key'           => 'field_59159c2717309',
							'label'         => 'Issue Date Position (y)',
							'name'          => 'issue_date_position_y',
							'type'          => 'number',
							'required'      => 1,
							'default_value' => '',
							'placeholder'   => '',
							'prepend'       => '',
							'append'        => '',
							'min'           => '',
							'max'           => '',
							'step'          => '',
						),
						array(
							'key'           => 'field_59159c641730a',
							'label'         => 'Font Family',
							'name'          => 'font_family',
							'type'          => 'select',
							'choices'       => array(
								'Courier'   => 'Courier',
								'Helvetica' => 'Helvetica',
								'Times'     => 'Times',
							),
							'default_value' => 'Helvetica',
							'allow_null'    => 0,
							'multiple'      => 0,
						),
						array(
							'key'           => 'field_59159cda1730b',
							'label'         => 'Font Style',
							'name'          => 'font_style',
							'type'          => 'select',
							'choices'       => array(
								'none' => 'Regular',
								'B'    => 'Bold',
								'I'    => 'Italic',
								'U'    => 'Underline',
							),
							'default_value' => '',
							'allow_null'    => 0,
							'multiple'      => 0,
						),
						array(
							'key'           => 'field_59159d171730c',
							'label'         => 'Font Size',
							'name'          => 'font_size',
							'type'          => 'number',
							'default_value' => 12,
							'placeholder'   => '',
							'prepend'       => '',
							'append'        => '',
							'min'           => '',
							'max'           => '',
							'step'          => '',
						),
					),
					'location'   => array(
						array(
							array(
								'param'    => 'post_type',
								'operator' => '==',
								'value'    => 'badges',
								'order_no' => 10,
								'group_no' => 0,
							),
						),
					),
					'options'    => array(
						'position'       => 'normal',
						'layout'         => 'default',
						'hide_on_screen' => array(),
					),
					'menu_order' => 999,
				)
			);

		endif;

		flush_rewrite_rules();

	}


	/**
	 * Handles options page form submission
	 * @param int $post_id Post ID
	 */
	public function save_certificate_template( $post_id ) {
		$post = get_post( $post_id );
		if ( $post->post_type === 'certificate' ) {

			if ( $post_id == 'options' ) {
				$archive  = get_field( 'documents_archive', $post_id );
				$filename = get_attached_file( $archive['ID'] );
				if ( $this->unzip_archive( $filename ) === true ) {
					unlink( $filename );
					delete_field( 'documents_archive', $post_id );
					wp_delete_attachment( $archive['ID'], true );
				}
			}
		}
	}


	/**
	 *
	 * Displays badge certificate
	 *
	 * @since 1.0.0
	 */
	public function display_certificate() {
		if ( preg_match( '/^\/members\/([^\/]+)\/badges\/([^\/]+)\/certificate\/?$/', $_SERVER['REQUEST_URI'], $output_array ) ) {
			$user_name  = $output_array[1];
			$badge_name = $output_array[2];

			$badge_id = $GLOBALS['badgefactor']->get_badge_id_by_slug( $badge_name );
			$user     = get_user_by( 'login', $user_name );

			$submission = $GLOBALS['badgefactor']->get_submission( $badge_id, $user );

			$achievement_id = get_post_meta( $badge_id, '_badgeos_submission_achievement_id' );

					// Redirect if there is a $badge_id, and if the achievement is private to others.
			if ( $badge_id && ( $user->ID != wp_get_current_user()->ID && $GLOBALS['badgefactor']->is_achievement_private( $submission->ID ) === true ) ) {

				//What to do if badge is private: redirect to user page
				$url       = $_SERVER['REQUEST_URI'];
				$segments  = explode( '/', parse_url( $url, PHP_URL_PATH ) );
				$user_path = '/' . $segments[1] . '/' . $segments[2];
				wp_safe_redirect( $user_path );
				exit;
			}

			// Get field values
			$recipient_name            = bp_core_get_user_displayname( $submission->post_author );
			$recipient_name_position_x = get_field( 'recipient_name_position_x', $badge_id );
			$recipient_name_position_y = get_field( 'recipient_name_position_y', $badge_id );

			setlocale( LC_TIME, get_locale(), 0 );
			$issue_date            = strftime( '%e %B %G', ( strtotime( $submission->post_modified ) ) );
			$issue_date_position_x = get_field( 'issue_date_position_x', $badge_id );
			$issue_date_position_y = get_field( 'issue_date_position_y', $badge_id );

			$badge_cert = get_field( 'template', $badge_id );
			$pdf_file   = ltrim( parse_url( $badge_cert['url'], PHP_URL_PATH ), '/' );

			if ( $badge_id && ( $user->ID === wp_get_current_user()->ID || ! $GLOBALS['badgefactor']->is_achievement_private( $submission->ID ) ) ) {
				$pdf = new FPDI();

				$pdf->setSourceFile( $pdf_file );
				$templateId = $pdf->importPage( 1 );
				$size       = $pdf->getTemplateSize( $templateId );
				$w          = $size['w'];
				$h          = $size['h'];
				$pdf->AddPage( 'L' );
				$pdf->useTemplate( $templateId, null, null, $w, $h, true );

				// TODO Get Font Family, Type and Size from certificate and add it as the function's parameters
				// TODO Get User name Font options
				$pdf->SetFont( 'Helvetica', '', 16 );

				// TODO Get Name placement variables and add them as the function's parameters
				$pdf->SetXY(
				//positionX
					( ( $recipient_name_position_x == '-1' ) ? ( $w / 2 - $pdf->GetStringWidth( $recipient_name ) / 2 ) : $recipient_name_position_x ),
					//positionY
					( ( $recipient_name_position_y == '-1' ) ? ( $h / 2 - $pdf->GetStringHeight( $recipient_name ) / 2 ) : $recipient_name_position_y )
				);

				// Get Badge recipient name and add it as the function's parameters
				$pdf->Cell( 0, 0, utf8_decode( $recipient_name ), 0, 'C' );

				// TODO Get Date Font options
				$pdf->SetFont( 'Helvetica', '', 12 );

				// Get issue date placement variables and add them as the function's parameters
				$pdf->SetXY(
				//positionX
					( ( $issue_date_position_x == '-1' ) ? ( $w / 2 - $pdf->GetStringWidth( $issue_date ) / 2 ) : $issue_date_position_x ),
					//positionY
					( ( $issue_date_position_y == '-1' ) ? ( $h / 2 - $pdf->GetStringHeight( $issue_date ) / 2 ) : $issue_date_position_y )
				);

				// Get Badge issue date and add it as the function's parameters
				$pdf->Cell( 0, 0, $issue_date, 0, 'C' );

				// Output badge name
				$pdf->Output( 'I', $badge_name . '.pdf' );
				exit;

			}
		}
	}
}

/*
function load_badgefactor_cert() {
	$GLOBALS['badgefactor']->cert = new BadgeFactorCertificates();
}
add_action( 'plugins_loaded', 'load_badgefactor_cert' );
*/

