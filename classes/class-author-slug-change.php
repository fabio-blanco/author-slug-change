<?php

/**
 * Copyright © 2023 Global Byte - Fabio M. Blanco
 * 
 * This file is part of Author Slug Change.
 * 
 * Author Slug Change is free software: you can redistribute it and/or 
 * modify it under the terms of the GNU General Public License as 
 * published by the Free Software Foundation, either version 3 of the 
 * License, or (at your option) any later version.
 * 
 * Author Slug Change is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU 
 * General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along 
 * with Author Slug Change. If not, see <https://www.gnu.org/licenses/>.
 * 
 */

namespace Global_Byte\Author_Slug_Change;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use WP_User;

class Author_Slug_Change {

	private static $instance;

	private $plugin_init_file;

	private $plugin_url;

	private function __construct() {
		$this->setup_globals();
		$this->init_scripts();
		$this->init_filters();
		$this->init_actions();
	}

	public static function get_instance() {
		if ( !isset(self::$instance) ) {
			self::$instance = new Author_Slug_Change();
		}

		return self::$instance;
	}

	private function setup_globals() {
		$this->plugin_init_file = dirname( dirname( __FILE__ ) ) . '/author-slug-change.php';
		$this->plugin_url = plugin_dir_url( $this->plugin_init_file );
	}

	private function init_scripts() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	private function init_filters() {
		add_filter('author_rewrite_rules', array( $this, 'author_rewrite_rules' ) );
		add_filter('author_link', array( $this, 'author_link' ) , 1000, 2);
	}

	private function init_actions() {
		if ( is_admin() ) {
			add_action( 'edit_user_profile', array( $this, 'show_edit_author_slug_fields' ) );
			add_action( 'show_user_profile', array( $this, 'show_edit_author_slug_fields' ) );
			add_action( 'personal_options_update', array( $this, 'update_custom_author_slug' ) );
			add_action( 'edit_user_profile_update', array( $this, 'update_custom_author_slug' ) );
		}
	}

	public function enqueue_admin_scripts($hook) {
		if ( 'user-edit.php' != $hook && 'profile.php' != $hook ) {
			return;
		}

		wp_enqueue_style( 'my_custom_script', $this->plugin_url . '/assets/css/asc-admin-styles.css');
	}

	public function author_rewrite_rules( $author_rewrite ) {
		global $wpdb;

		if ( str_starts_with(array_key_first( $author_rewrite ), "index.php") ) {
			$author_rewrite = $this->build_index_based_author_rewrite_rules( $wpdb );
		} else {
			$author_rewrite = $this->build_path_based_author_rewrite_rules( $wpdb );
		}

		return $author_rewrite;
	}

	public function author_link($link, $author_id) {
		$slug_name = esc_attr( get_user_meta( $author_id, 'asc_custom_author_slug', true ));
		if ( !empty($slug_name) ) {
			$link = preg_replace("|author/.*/|", "author/{$slug_name}/", $link);
		}
		return $link;
	}

	private function build_index_based_author_rewrite_rules( $wpdb ) {
		return $this->build_author_rewrite_rules( 'index.php/', $wpdb );
	}

	private function build_path_based_author_rewrite_rules( $wpdb ) {
		return $this->build_author_rewrite_rules( '', $wpdb );
	}

	private function build_author_rewrite_rules( $prefix, $wpdb ) {
		$author_rewrite = array();

		$authors = $wpdb->get_results("SELECT ID, user_nicename AS nicename from $wpdb->users");    
		foreach($authors as $author) {
			$slug_name = $this->get_author_slug( $author );
			$author_rules = $this->build_single_author_rewrite_rules( $prefix, $slug_name, $author->nicename );
			$author_rewrite = array_merge($author_rewrite, $author_rules);
		}  

		return $author_rewrite;
	}

	private function get_author_slug( $author ) {
		$slug_name = esc_attr( get_user_meta( $author->ID, 'asc_custom_author_slug', true ));
		if ( empty(trim($slug_name)) ) {
			$slug_name = $author->nicename;
		}

		return $slug_name;
	}

	private function build_single_author_rewrite_rules( $prefix, $slug_name, $nicename ) {
		$author_rewrite = array();
		$author_rewrite["{$prefix}author/({$slug_name})/feed/(feed|rdf|rss|rss2|atom)/?$"] = "index.php?author_name={$nicename}&feed=\$matches[2]";
		$author_rewrite["{$prefix}author/({$slug_name})/(feed|rdf|rss|rss2|atom)/?$"] = "index.php?author_name={$nicename}&feed=\$matches[2]";
		$author_rewrite["{$prefix}author/({$slug_name})/embed/?$"] = "index.php?author_name={$nicename}&embed=true";
		$author_rewrite["{$prefix}author/({$slug_name})/page/?([0-9]{1,})/?$"] = "index.php?author_name={$nicename}&paged=\$matches[2]";
		$author_rewrite["{$prefix}author/({$slug_name})/?$"] = "index.php?author_name={$nicename}";
		return $author_rewrite;
	}

	public function show_edit_author_slug_fields( WP_User $user ) {

		// Return early if the user can't edit the author slug.
		if ( empty( $user->ID ) ) {
			return;
		}

		$custom_author_slug = esc_attr( get_user_meta( $user->ID, 'asc_custom_author_slug', true ));

		?>

		<h2><?php esc_html_e( 'Edit Author Slug', 'author-slug-change' ); ?></h2>

		<div class="form-wrap">
			<div class="asc-form-field">
				<label for="asc_custom_slug"><?php esc_html_e( 'Custom Slug', 'author-slug-change' ); ?></label>
				<input type="text" size="30" 
						id="asc_custom_slug" 
						name="asc_custom_slug" 
						value="<?= $custom_author_slug ?>"
						class="regular-text" 
						aria-required="true" 
						aria-describedby="asc_custom_slug_desc" />
				<p class="description" id="asc_custom_slug_desc"><?php esc_html_e( 'A custom slug for the author page.', 'author-slug-change' ); ?></p>
			</div>
		</div>

		<?php

	}

	public function update_custom_author_slug( $user_id ) {
		// check that the current user have the capability to edit the $user_id
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		$custom_author_slug = trim($_POST['asc_custom_slug']);

		if ( !empty($custom_author_slug) ) {
			// create/update user meta for the $user_id
			return update_user_meta(
				$user_id,
				'asc_custom_author_slug',
				$custom_author_slug
			);
		}

	}

}