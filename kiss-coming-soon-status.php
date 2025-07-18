<?php
/**
 * Plugin Name:       Coming Soon Post Status
 * Plugin URI:        https://KISSPlugins.com
 * Description:       Adds a "Coming Soon" post status to show posts in archives but link to '#' instead of the full post.
 * Version:           1.1.0
 * Author:            KISS Plugins
 * Author URI:        https://KISSPlugins.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       csps-coming-soon-post-status
 * Domain Path:       /languages
 *
 * =================================================================================================
 * | Table of Contents
 * =================================================================================================
 * |
 * | csps_run_plugin()
 * |
 * | CSPS_Coming_Soon_Post_Status Class
 * | - private static $instance
 * | - const VERSION
 * | - const POST_STATUS
 * | - const LABEL
 * |
 * | - public static function get_instance()
 * | - private function __construct()
 * | - public function register_coming_soon_post_status()
 * | - public function add_coming_soon_checkbox_to_publish_box( $post )
 * | - public function add_coming_soon_checkbox_to_quick_edit( $column_name, $post_type )
 * | - public function save_coming_soon_status( $post_id )
 * | - public function quick_edit_script()
 * | - public function include_coming_soon_in_queries( $query )
 * | - public function modify_post_link( $permalink, $post )
 * | - public function modify_read_more_link( $more_link )
 * | - public function modify_excerpt_more( $more_text )
 * | - public function add_admin_menu_page()
 * | - public function render_settings_page()
 * | - public function add_settings_link( $links )
 * |
 * =================================================================================================
 * | Developer Notes
 * =================================================================================================
 * |
 * | To make your theme's custom "Read More" links compatible with this plugin, you should
 * | check the post status before rendering the link.
 * |
 * | Example:
 * |
 * | if ( class_exists('CSPS_Coming_Soon_Post_Status') && CSPS_Coming_Soon_Post_Status::POST_STATUS === get_post_status( get_the_ID() ) ) {
 * |     // Output the "Coming Soon" link
 * |     echo '<a class="my-custom-read-more" href="#">' . esc_html( CSPS_Coming_Soon_Post_Status::LABEL ) . '</a>';
 * | } else {
 * |     // Output the standard permalink
 * |     echo '<a class="my-custom-read-more" href="' . esc_url( get_permalink() ) . '">Read More</a>';
 * | }
 * |
 * | This plugin uses the standard 'the_content_more_link' and 'excerpt_more' filters. If your
 * | theme also uses these standard WordPress filters for its "Read More" links, no changes
 * | should be necessary.
 * |
 * =================================================================================================
 * | Changelog
 * =================================================================================================
 * |
 * | 1.1.0 - 2025-07-17
 * | - Refactor: Replaced status dropdown modification with a dedicated "Set as Coming Soon" checkbox.
 * | - Feature: Added checkbox to the "Publish" meta box in the full post editor.
 * | - Feature: Added checkbox to the "Quick Edit" interface on post listing screens.
 * | - Fix: The "Coming Soon" status is now reliably saved and reflected in Quick Edit mode.
 * | - Dev: Removed old JavaScript for dropdown manipulation and added new script for Quick Edit checkbox logic.
 * |
 * | 1.0.1 - 2025-07-17
 * | - Fix: Add "Coming Soon" status to the Quick Edit dropdown on post/page listing screens.
 * | - Dev: Added documentation for developers on how to integrate with custom "Read More" links.
 * |
 * | 1.0.0 - 2025-07-17
 * | - Initial release.
 * |
 * =================================================================================================
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function csps_run_plugin() {
	CSPS_Coming_Soon_Post_Status::get_instance();
}
csps_run_plugin();

/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    CSPS_Coming_Soon_Post_Status
 * @author     KISS Plugins
 */
final class CSPS_Coming_Soon_Post_Status {

	/**
	 * The single instance of the class.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      CSPS_Coming_Soon_Post_Status    $instance
	 */
	private static $instance;

	/**
	 * The current version of the plugin.
	 *
	 * @since 1.1.0
	 * @access public
	 * @var string
	 */
	const VERSION = '1.1.0';

	/**
	 * The unique identifier for the custom post status.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	const POST_STATUS = 'coming_soon';

	/**
	 * The public-facing label for the custom post status.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	const LABEL = 'Coming Soon';

	/**
	 * Ensures only one instance of the class is loaded.
	 *
	 * @since     1.0.0
	 * @static
	 * @return    CSPS_Coming_Soon_Post_Status An instance of the class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * A private constructor to prevent creating a new instance of the class.
	 *
	 * @since    1.0.0
	 */
	private function __construct() {
		// Register the custom status.
		add_action( 'init', array( $this, 'register_coming_soon_post_status' ) );

		// Add checkbox UI to editors.
		add_action( 'post_submitbox_misc_actions', array( $this, 'add_coming_soon_checkbox_to_publish_box' ) );
		add_action( 'quick_edit_custom_box', array( $this, 'add_coming_soon_checkbox_to_quick_edit' ), 10, 2 );

		// Handle saving the status from the checkbox.
		add_action( 'save_post', array( $this, 'save_coming_soon_status' ) );

		// Add JS for Quick Edit UI.
		add_action( 'admin_footer-edit.php', array( $this, 'quick_edit_script' ) );

		// Frontend modifications.
		add_action( 'pre_get_posts', array( $this, 'include_coming_soon_in_queries' ) );
		add_filter( 'post_link', array( $this, 'modify_post_link' ), 10, 2 );
		add_filter( 'the_content_more_link', array( $this, 'modify_read_more_link' ) );
		add_filter( 'excerpt_more', array( $this, 'modify_excerpt_more' ) );

		// Settings page.
		add_action( 'admin_menu', array( $this, 'add_admin_menu_page' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_settings_link' ) );
	}

	/**
	 * Registers the custom "Coming Soon" post status.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function register_coming_soon_post_status() {
		register_post_status(
			self::POST_STATUS,
			array(
				'label'                     => _x( self::LABEL, 'post', 'csps-coming-soon-post-status' ),
				'public'                    => false, // Set to false to prevent direct access.
				'internal'                  => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: number of posts */
				'label_count'               => _n_noop( self::LABEL . ' <span class="count">(%s)</span>', self::LABEL . ' <span class="count">(%s)</span>', 'csps-coming-soon-post-status' ),
			)
		);
	}

	/**
	 * Adds a "Coming Soon" checkbox to the Publish meta box in the full editor.
	 *
	 * @since 1.1.0
	 * @param WP_Post $post The current post object.
	 * @return void
	 */
	public function add_coming_soon_checkbox_to_publish_box( $post ) {
		if ( ! is_object( $post ) ) {
			return;
		}
		$is_coming_soon = ( $post->post_status === self::POST_STATUS );
		$checked        = $is_coming_soon ? 'checked="checked"' : '';
		wp_nonce_field( 'csps_save_coming_soon_status', 'csps_nonce' );

		echo '<div class="misc-pub-section misc-pub-coming-soon" style="padding: 5px 10px;">';
		echo '<label><input type="checkbox" name="csps_coming_soon" value="1" ' . $checked . '> ';
		echo esc_html__( 'Set as Coming Soon', 'csps-coming-soon-post-status' ) . '</label>';
		echo '</div>';
	}

	/**
	 * Adds a "Coming Soon" checkbox to the Quick Edit interface.
	 *
	 * @since 1.1.0
	 * @param string $column_name The name of the column being displayed.
	 * @param string $post_type The current post type.
	 * @return void
	 */
	public function add_coming_soon_checkbox_to_quick_edit( $column_name, $post_type ) {
		static $printed = false;
		if ( $printed ) {
			return;
		}
		$printed = true;
		// The nonce will be added via JS to ensure it's inside the form.
		echo '<fieldset class="inline-edit-col-right">';
		echo '<div class="inline-edit-col inline-edit-csps">';
		echo '<label class="alignleft"><input type="checkbox" name="csps_coming_soon"> ';
		echo '<span class="checkbox-title">' . esc_html__( 'Set as Coming Soon', 'csps-coming-soon-post-status' ) . '</span></label>';
		echo '</div></fieldset>';
	}

	/**
	 * Saves the "Coming Soon" status based on the checkbox state.
	 *
	 * @since 1.1.0
	 * @param int $post_id The ID of the post being saved.
	 * @return void
	 */
	public function save_coming_soon_status( $post_id ) {
		$nonce_action = 'csps_save_coming_soon_status';
		$nonce_name   = isset( $_POST['csps_nonce'] ) ? 'csps_nonce' : ( isset( $_POST['csps_quick_edit_nonce'] ) ? 'csps_quick_edit_nonce' : '' );

		if ( empty( $nonce_name ) || ! isset( $_POST[ $nonce_name ] ) || ! wp_verify_nonce( $_POST[ $nonce_name ], $nonce_action ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$current_status         = get_post_status( $post_id );
		$is_coming_soon_checked = isset( $_POST['csps_coming_soon'] );

		remove_action( 'save_post', array( $this, 'save_coming_soon_status' ) );

		if ( $is_coming_soon_checked ) {
			if ( $current_status !== self::POST_STATUS ) {
				wp_update_post( array( 'ID' => $post_id, 'post_status' => self::POST_STATUS ) );
			}
		} elseif ( $current_status === self::POST_STATUS ) {
			$new_status = isset( $_POST['post_status'] ) && $_POST['post_status'] !== self::POST_STATUS ? sanitize_text_field( $_POST['post_status'] ) : 'draft';
			wp_update_post( array( 'ID' => $post_id, 'post_status' => $new_status ) );
		}

		add_action( 'save_post', array( $this, 'save_coming_soon_status' ) );
	}

	/**
	 * Adds JavaScript to the footer of edit screens for Quick Edit functionality.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function quick_edit_script() {
		global $current_screen;
		if ( ! $current_screen || 'edit' !== $current_screen->base ) {
			return;
		}
		?>
		<script id="csps-quick-edit-script">
		jQuery(function($) {
			$('#the-list').on('click', '.editinline', function() {
				var post_id = $(this).closest('tr').attr('id').replace('post-', '');
				var post_status = $('#post-' + post_id + ' .post_status').text();
				var $checkbox = $('.inline-edit-row input[name="csps_coming_soon"]');
				$checkbox.prop('checked', post_status === '<?php echo esc_js( self::LABEL ); ?>');

				if ( ! $('.inline-edit-row input[name="csps_quick_edit_nonce"]').length ) {
					$checkbox.closest('.inline-edit-csps').append('<?php echo wp_nonce_field( 'csps_save_coming_soon_status', 'csps_quick_edit_nonce', true, false ); ?>');
				}
			});
		});
		</script>
		<?php
	}

	/**
	 * Includes posts with the "Coming Soon" status in main frontend queries.
	 *
	 * @since    1.0.0
	 * @param    WP_Query $query The WordPress query object.
	 * @return   void
	 */
	public function include_coming_soon_in_queries( $query ) {
		if ( ! $query->is_main_query() ) {
			return;
		}

		// Handle frontend queries
		if ( ! is_admin() && ( $query->is_home() || $query->is_category() || $query->is_tag() || $query->is_archive() ) ) {
			$current_statuses = $query->get( 'post_status' );
			if ( empty( $current_statuses ) ) {
				$current_statuses = array( 'publish' );
			}
			if ( is_string( $current_statuses ) ) {
				$current_statuses = array( $current_statuses );
			}
			if ( ! in_array( self::POST_STATUS, $current_statuses, true ) ) {
				$current_statuses[] = self::POST_STATUS;
				$query->set( 'post_status', $current_statuses );
			}
		}

		// Handle admin queries - include Coming Soon posts in "All Posts" view
		if ( is_admin() && ! isset( $_GET['post_status'] ) ) {
			$current_statuses = $query->get( 'post_status' );
			if ( empty( $current_statuses ) ) {
				$current_statuses = array( 'publish', 'draft', 'pending', 'private', 'future' );
			}
			if ( is_string( $current_statuses ) ) {
				$current_statuses = array( $current_statuses );
			}
			if ( ! in_array( self::POST_STATUS, $current_statuses, true ) ) {
				$current_statuses[] = self::POST_STATUS;
				$query->set( 'post_status', $current_statuses );
			}
		}
	}

	/**
	 * Modifies the post permalink for posts with "Coming Soon" status.
	 *
	 * @since    1.0.0
	 * @param    string  $permalink The original post permalink.
	 * @param    WP_Post $post      The post object.
	 * @return   string  The modified permalink ('#') or the original.
	 */
	public function modify_post_link( $permalink, $post ) {
		if ( is_object( $post ) && self::POST_STATUS === get_post_status( $post->ID ) ) {
			return '#';
		}
		return $permalink;
	}

	/**
	 * Changes the "Read More" link text and URL for posts using the <!--more--> tag.
	 *
	 * @since    1.0.0
	 * @param    string $more_link The original "Read More" link HTML.
	 * @return   string The modified "Read More" link HTML.
	 */
	public function modify_read_more_link( $more_link ) {
		if ( self::POST_STATUS === get_post_status( get_the_ID() ) ) {
			return '<a class="more-link" href="#">' . esc_html__( self::LABEL, 'csps-coming-soon-post-status' ) . '</a>';
		}
		return $more_link;
	}

	/**
	 * Changes the "Read More" link for automatic excerpts.
	 *
	 * @since    1.0.0
	 * @param    string $more_text The original excerpt more text (e.g., '[...]').
	 * @return   string The modified excerpt more HTML.
	 */
	public function modify_excerpt_more( $more_text ) {
		if ( self::POST_STATUS === get_post_status( get_the_ID() ) ) {
			return ' <a class="more-link" href="#">' . esc_html__( self::LABEL, 'csps-coming-soon-post-status' ) . '</a>';
		}
		return $more_text;
	}

	/**
	 * Adds the plugin settings page to the main "Settings" menu in the admin dashboard.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function add_admin_menu_page() {
		add_options_page(
			__( 'Coming Soon Status Settings', 'csps-coming-soon-post-status' ),
			__( 'Coming Soon Status', 'csps-coming-soon-post-status' ),
			'manage_options',
			'csps-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Renders the content for the settings page.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Settings/Configuration - Coming Soon', 'csps-coming-soon-post-status' ); ?></h1>
			<p><?php esc_html_e( 'Future settings for the plugin will be available here.', 'csps-coming-soon-post-status' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Adds a "Settings" link to the plugin's entry on the plugins listing page.
	 *
	 * @since    1.0.0
	 * @param    array $links An array of existing action links.
	 * @return   array An array of modified action links.
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="options-general.php?page=csps-settings">' . __( 'Settings', 'csps-coming-soon-post-status' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}
}
