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

use BadgeFactor2\Helpers\BuddyPress;
use BadgeFactor2\Models\BadgeClass;
use BadgeFactor2\Models\Issuer;
use setasign\Fpdi\Tcpdf\Fpdi;

class Certificates_Public {


	/**
	 * Init Hooks.
	 *
	 * @return void
	 */
	public static function init_hooks() {

		add_action( 'init', array( self::class, 'add_rewrite_tags' ), 10, 0 );
		add_action( 'init', array( self::class, 'add_rewrite_rules' ), 10, 0 );
	}


	/**
	 * Rewrite tags.
	 *
	 * @return void
	 */
	public static function add_rewrite_tags() {
		add_rewrite_tag( '%certificate%', '([^&]+)' );
	}


	/**
	 * Rewrite rules.
	 *
	 * @return void
	 */
	public static function add_rewrite_rules() {
		$options = get_option( 'bf2_certificates_settings' );

		if ( BuddyPress::is_active() ) {
			// Members page managed by BuddyPress.
			$members_page = BuddyPress::get_members_page_name();

			// FIXME make certificate variable.
			$certificate_slug = ! empty( $options['bf2_certificate_slug'] ) ? $options['bf2_certificate_slug'] : 'certificate';

			add_rewrite_rule( "{$members_page}/([^/]+)/badges/([^/]+)/{$certificate_slug}/?$", 'index.php?member=$matches[1]&badge=$matches[2]&certificate=1', 'top' );
		} else {
			// TODO Manage Members page without BuddyPress.
		}
	}


	/**
	 * Generate certificate.
	 */
	public static function generate( $course, $assertion ) {

		$plugin_data = get_plugin_data( BF2_CERTIFICATES_FILE );
		$settings    = get_option( 'bf2_certificates_settings' );

		$template_file = get_attached_file( $settings['bf2_certificate_template_id'] );

		$badge     = BadgeClass::get( $assertion->badgeclass );
		$issuer    = Issuer::get( $badge->issuer );
		$recipient = get_user_by( 'email', $assertion->recipient->plaintextIdentity );

		$pdf = new Fpdi();
		$pdf->AddPage( 'L', 'Letter' );
		$pdf->setSourceFile( $template_file );

		$tpl_id = $pdf->importPage( 1 );
		$pdf->useTemplate( $tpl_id, 0, 0, null, null, true );

		foreach ( $settings as $id => $field_settings ) {

			if ( false === strpos( $id, 'bf2_certificate_template' ) && is_array( $field_settings ) ) {

				$field_settings['link'] = null;

				if ( strpos( $field_settings['text'], '$badge$' ) !== false ) {
					// $badge$
					$image = str_replace( '$badge$', $assertion->image, $field_settings['text'] );
					self::generate_pdf_image( $pdf, $field_settings, $image );
				} else {
					$text = $field_settings['text'];

					// $course$
					if ( strpos( $text, '$course$' ) !== false ) {
						$text = str_replace( '$course$', $course->post_title, $text );
					}

					// $date$
					if ( strpos( $text, '$date$' ) !== false ) {
						$text = str_replace( '$date$', date_i18n( 'Y-m-d' ), $text );
					}

					// $hours$
					if ( strpos( $text, '$hours$' ) !== false ) {
						$hours = get_post_meta( $course, 'course_duration', true );
						if ( false === $hours ) {
							$hours = 'N/A';
						}
						if ( '1' === $hours ) {
							$hours .= sprintf( ' %s', __( 'hour', $plugin_data['TextDomain'] ) );
						} else {
							$hours .= sprintf( ' %s', __( 'hours', $plugin_data['TextDomain'] ) );
						}
						$text = str_replace( '$hours$', $hours, $text );
					}

					// $issuer$
					if ( strpos( $text, '$issuer$' ) !== false ) {
						$text = str_replace( '$issuer$', $issuer->name, $text );
					}

					// $name$
					if ( strpos( $text, '$name$' ) !== false ) {
						$text = str_replace( '$name$', $recipient->user_nicename, $text );
					}

					// $portfolio$
					if ( strpos( $text, '$portfolio$' ) !== false ) {
						$text                   = str_replace( '$portfolio$', 'TODO', $text );
						$field_settings['link'] = 'https://ctrlweb.ca/fr/';
					}

					self::generate_pdf_text( $pdf, $field_settings, $text );
				}
			}
		}
		status_header( 200 );
		$pdf->Output();
		die;
	}


	/**
	 * Generate PDF Image helper function.
	 *
	 * @param Fpdi $pdf Fpdi Class.
	 * @param array $field_settings Field Settings.
	 * @param string $content Field Content.
	 * @return void
	 */
	private static function generate_pdf_image( Fpdi $pdf, array $field_settings, $content ) {
		switch ( $field_settings['align'] ) {
			case 'C':
				$pos_x = (int) $field_settings['pos_x'] - (int) round( $field_settings['width'] / 2 );
				break;
			case 'R':
				$pos_x = (int) $field_settings['pos_x'] - (int) $field_settings['width'];
				break;
			case 'L':
			default:
				$pos_x = (int) $field_settings['pos_x'];
				break;
		}
		$pdf->image( $content, $pos_x, $field_settings['pos_y'], $field_settings['width'] );
	}


	/**
	 * Generate PDF Text helper function.
	 *
	 * @param Fpdi $pdf Fpdi Class.
	 * @param array $field_settings Field Settings.
	 * @param string $content Field Content.
	 * @return void
	 */
	private static function generate_pdf_text( Fpdi $pdf, array $field_settings, $content ) {
		$pdf->SetXY( $field_settings['pos_x'], $field_settings['pos_y'] );
		$pdf->setFont( $field_settings['font'], $field_settings['style'] );
		$pdf->setFontSize( $field_settings['size'] );
		$pdf->Cell(
			$field_settings['width'], // Width.
			0, // Height.
			$content, // Text.
			0, // Border.
			0, // Position after.
			$field_settings['align'], // Align.
			false, // Fill.
			$field_settings['link'] // Link.
		);
	}
}