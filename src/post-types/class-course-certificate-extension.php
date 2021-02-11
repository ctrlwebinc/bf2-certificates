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
 * @package Badge_Factor_2
 *
 * @phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain
 * @phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralContext
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
 */

namespace BadgeFactor2\Post_Types;

/**
 * Course post type (Certificate extension).
 */
class Course_Certificate_Extension {

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init_hooks() {
		add_action( 'cmb2_admin_init', array( self::class, 'register_cpt_metaboxes' ), 20 );
	}

	/**
	 * Custom meta boxes.
	 *
	 * @return void
	 */
	public static function register_cpt_metaboxes() {

		$plugin_data = get_plugin_data( BF2_CERTIFICATES_FILE );
		$cmb         = cmb2_get_metabox( 'course_info' );

		if ( $cmb ) {
			$cmb->add_field(
				array(
					'id'           => 'certificate_template',
					'name'         => __( 'Certificate Template', $plugin_data['TextDomain'] ),
					'type'         => 'file',
					'options'      => array(
						'url' => false,
					),
					'text'         => array(
						'add_upload_file_text' => __( 'Add Template', $plugin_data['TextDomain'] ),
					),
					'query_args'   => array(
						'type' => 'application/pdf',
					),
					'preview_size' => 'small',
				)
			);
		}

		
	}
}
