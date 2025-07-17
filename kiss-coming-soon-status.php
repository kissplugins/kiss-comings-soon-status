<?php
/**
 * Plugin Name:       Coming Soon Post Status
 * Plugin URI:        https://KISSPlugins.com
 * Description:       Adds a "Coming Soon" post status to show posts in archives but link to '#' instead of the full post.
 * Version:           1.0.1
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
 * | - public function add_coming_soon_post_status_to_dropdown()
 * | - public function add_coming_soon_to_quick_edit()
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
 * | if ( function_exists('CSPS_Coming_Soon_Post_Status') && CSPS_Coming_Soon_Post_Status::POST_STATUS === get_post_status( get_the_ID() ) ) {
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
 * | 1.0.1 - 2025-07-17
 * | - Fix: Add "Coming Soon" status to the Quick Edit dropdown on post/page listing screens.
 * | - Dev: Added documentation for developers on how to integrate with custom "Read More" links.
 * |
 * | 1.0.0 - 2025-07-17
 * | - Initial release.
 * | - Feature: "Coming Soon" custom post status.
 * | - Feature: Posts with "Coming Soon" status appear in frontend archives.
 * | - Feature: Permalink and "Read More" links for these posts point to "#" and display "Coming Soon".
 * | - Feature: Placeholder settings page under Settings > Coming Soon Status.
 * | - Feature: "Settings" link on the main plugins page.
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
 * Since everything within the plugin is registered via hooks,
 * kicking off the plugin from this point in the file does
 * not affect the page life cycle.
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
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains a single instance of the class.
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
	 * @var      CSPS_Coming_Soon_Post_Status    $instance    The single instance of the class.
	 */
	private static $instance;

	/**
	 * The current version of the plugin.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	const VERSION = '1.0.1';

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
	 * @return    CSPS_Coming_Soon_Post_Status    An instance of the class.
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
		add_action( 'init', array( $this, 'register_coming_soon_post_status' ) );
		add_action( 'admin_footer-post.php', array( $this, 'add_coming_soon_post_status_to_dropdown' ) );
		add_action( 'admin_footer-post-new.php', array( $this, 'add_coming_soon_post_status_to_dropdown' ) );
		add_action( 'admin_footer-edit.php', array( $this, 'add_coming_soon_to_quick_edit' ) );
		add_action( 'pre_get_posts', array( $this, 'include_coming_soon_in_queries' ) );

		add_filter( 'post_link', array( $this, 'modify_post_link' ), 10, 2 );
		add_filter( 'the_content_more_link', array( $this, 'modify_read_more_link' ) );
		add_filter( 'excerpt_more', array( $this, 'modify_excerpt_more' ) );

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
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: number of posts */
				'label_count'               => _n_noop( self::LABEL . ' <span class="count">(%s)</span>', self::LABEL . ' <span class="count">(%s)</span>', 'csps-coming-soon-post-status' ),
			)
		);
	}

	/**
	 * Adds the "Coming Soon" status to the post status dropdown in the editor.
	 * This uses JavaScript to modify the DOM as there is no native PHP hook.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function add_coming_soon_post_status_to_dropdown() {
		global $post;
		if ( ! is_object( $post ) ) {
			return;
		}

		$status_const = self::POST_STATUS;
		$label        = self::LABEL;
		$selected     = ( $post->post_status === $status_const ) ? ' selected="selected"' : '';

		echo "
        <script>
        jQuery(document).ready(function($){
            var statusDropdown = $('select#post_status');
            if (statusDropdown.length && statusDropdown.find('option[value=\"" . esc_js( $status_const ) . "\"]').length === 0) {
                statusDropdown.append('<option value=\"" . esc_js( $status_const ) . "\"" . esc_js( $selected ) . ">" . esc_js( $label ) . "</option>');
            }

            if ('" . esc_js( $post->post_status ) . "' === '" . esc_js( $status_const ) . "') {
                $('#post-status-display').text('" . esc_js( $label ) . "');
            }

            $('a.save-post-status').on('click', function(){
                if (statusDropdown.val() === '" . esc_js( $status_const ) . "') {
                    $('#post-status-display').text('" . esc_js( $label ) . "');
                }
            });
        });
        </script>
        ";
	}

	/**
	 * Adds the "Coming Soon" status to the Quick Edit status dropdown.
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public function add_coming_soon_to_quick_edit() {
		$status_const = self::POST_STATUS;
		$label        = self::LABEL;
		echo "
		<script>
		jQuery(document).ready(function($){
			// Use delegation for the Quick Edit link click, as rows are loaded via AJAX.
			$('#the-list').on('click', '.editinline', function() {
				// Set a small timeout to allow the Quick Edit row to be populated.
				setTimeout(function() {
					var quickEditRow = $('tr.inline-edit-row');
					var statusDropdown = quickEditRow.find('select[name=\"_status\"]');
					if (statusDropdown.length && statusDropdown.find('option[value=\"" . esc_js( $status_const ) . "\"]').length === 0) {
						statusDropdown.append('<option value=\"" . esc_js( $status_const ) . "\">" . esc_js( $label ) . "</option>');
					}
				}, 50);
			});
		});
		</script>
		";
	}


	/**
	 * Includes posts with the "Coming Soon" status in main frontend queries.
	 * This ensures they appear on category, tag, and archive pages.
	 *
	 * @since    1.0.0
	 * @param    WP_Query $query The WordPress query object.
	 * @return   void
	 */
	public function include_coming_soon_in_queries( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( $query->is_home() || $query->is_category() || $query->is_tag() || $query->is_archive() ) {
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
			__( 'Coming Soon Status Settings', 'csps-coming-soon-post-status' ), // Page Title
			__( 'Coming Soon Status', 'csps-coming-soon-post-status' ), // Menu Title
			'manage_options', // Capability
			'csps-settings', // Menu Slug
			array( $this, 'render_settings_page' ) // Callback function
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
