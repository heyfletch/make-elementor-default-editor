<?php
/*
	Plugin Name: 				Make Elementor Default Editor
	Plugin URI: 				https://github.com/heyfletch/make-elementor-default-editor
	Description:				Make Elementor the default editor for pages, posts, and Elementor templates
	Version: 						0.1.4
	Author: 						Fletcher Digital
	Author URI: 				https://fletcherdigital.com
  License:           	GPL v2 or later
  License URI:       	https://www.gnu.org/licenses/gpl-2.0.html
	Copyright: 					Fletcher Digital
	Text Domain: 				fletcher-digital
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

add_action('init', 'fd_init');
function fd_init() {

	/** Replace hyperlink in post titles on Page, Post, or Template lists with Elementor's editor link */
	add_filter('get_edit_post_link', 'fd_make_elementor_default_edit_link', 10, 3);

	function fd_make_elementor_default_edit_link($link, $post_id, $context)
	{

		// Only relevant in the admin, checks for function that is occasionally missing
		if (is_admin() && function_exists('get_current_screen')) {

			// Get current screen parameters
			$screen = get_current_screen();

			//check if $screen is object otherwise we may be on an admin page where get_current_screen isn't defined
			if (!is_object($screen)) {
				return;
			}

			// Post Types to Edit with Elementor
			$post_types_for_elementor = array(
				'page',
				'post',
				'elementor_library',
			);

			// When we are on a specified post type screen
			if (in_array($screen->post_type, $post_types_for_elementor) && $context == 'display') {

				// Build the Elementor editor link
				$elementor_editor_link = admin_url('post.php?post=' . $post_id . '&action=elementor');

				return $elementor_editor_link;

			} else {

				return $link;

			}
		}
	}

	/** Add back the default Edit link action in Page and Post list rows */
	add_filter('page_row_actions', 'fd_add_back_default_edit_link', 10, 2);
	add_filter('post_row_actions', 'fd_add_back_default_edit_link', 10, 2);

	function fd_add_back_default_edit_link($actions, $post)
	{

		// Build the Elementor edit URL
		$elementor_edit_url = admin_url('post.php?post=' . $post->ID . '&action=edit');

		// Rewrite the normal Edit link
		$actions['edit'] =
			sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url($elementor_edit_url),
				esc_html(__('Default WordPress Editor', 'elementor'))
			);

		return $actions;
	}

	/** Remove redundant "Edit with Elementor" link added by Elementor itself */
	add_filter('page_row_actions', 'fd_remove_default_edit_with_elementor', 99, 2);
	add_filter('post_row_actions', 'fd_remove_default_edit_with_elementor', 99, 2);

	function fd_remove_default_edit_with_elementor($actions, $post)
	{
		// Rewrite the normal Edit link
		unset($actions['edit_with_elementor']);

		return $actions;
	}

}

/* Minor Styling to make the "- Elementor" non-bold */
add_action('admin_head', 'fd_elementor_styling');
function fd_elementor_styling()
{
  echo '<style>
	td.title.column-title.has-row-actions.column-primary.page-title strong {
		font-weight: 100;
	}
  </style>';
}
