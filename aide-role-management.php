<?php
/*
Plugin Name: Aide :: Role Management
Description: Manage roles and set auto role assignment rules.
Version: 1.0.0
Requires at least: 6.0
Requires PHP: 7.4
Author: Aide247
Author URI: https://aide247.com/
Text Domain: aiderolemanagement
Domain Path: /languages
Plugin URI: https://aide247.com/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Role management plugin bootstrap.
 *
 * Notes for WordPress.org:
 * - No external/CDN assets are loaded from admin pages.
 * - All role/rule mutations require capability + nonce checks.
 */
final class Aide_Role_Management {
	/**
	 * Option key storing auto-assign rules.
	 */
	private const OPTION_RULES = 'aide_role_auto_assign';

	/**
	 * Option key storing roles created via this plugin.
	 *
	 * We only allow deletion for roles we created, to avoid accidentally removing
	 * WordPress core roles or roles registered by other plugins/themes.
	 */
	private const OPTION_CREATED_ROLES = 'aide_role_created_roles';

	/**
	 * Nonce field name used by this plugin forms.
	 */
	private const NONCE_FIELD = 'aide_role_management_nonce';

	/**
	 * Absolute path to this plugin directory (with trailing slash).
	 *
	 * @var string
	 */
	private $plugin_dir;

	public function __construct() {
		$this->plugin_dir = plugin_dir_path( __FILE__ );

		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
		add_action( 'admin_menu', [ $this, 'register_admin_pages' ] );
		add_action( 'admin_init', [ $this, 'handle_admin_requests' ] );
		add_action( 'user_register', [ $this, 'auto_assign_roles' ] );
	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'aiderolemanagement', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Register the admin menu pages.
	 *
	 * @return void
	 */
	public function register_admin_pages() {
		add_menu_page(
			__( 'Aide Role Management', 'aiderolemanagement' ),
			__( 'Aide - Role', 'aiderolemanagement' ),
			'manage_options',
			'aide-role',
			[ $this, 'main_page' ],
			'dashicons-admin-users',
			50
		);

		add_submenu_page(
			'aide-role',
			__( 'List', 'aiderolemanagement' ),
			__( 'List', 'aiderolemanagement' ),
			'manage_options',
			'aide-role',
			[ $this, 'main_page' ]
		);

		add_submenu_page(
			'aide-role',
			__( 'Add Role', 'aiderolemanagement' ),
			__( 'Add Role', 'aiderolemanagement' ),
			'manage_options',
			'aide-role-add',
			[ $this, 'add_role_page' ]
		);

		add_submenu_page(
			'aide-role',
			__( 'Assign Role', 'aiderolemanagement' ),
			__( 'Assign Role', 'aiderolemanagement' ),
			'manage_options',
			'aide-role-assign',
			[ $this, 'assign_role_page' ]
		);
	}

	/**
	 * Render main admin page.
	 *
	 * @return void
	 */
	public function main_page() {
		$this->include_admin_template( 'admin/main.php' );
	}

	/**
	 * Render add role page.
	 *
	 * @return void
	 */
	public function add_role_page() {
		$this->include_admin_template( 'admin/add-role-page.php' );
	}

	/**
	 * Render assign role page.
	 *
	 * @return void
	 */
	public function assign_role_page() {
		$this->include_admin_template( 'admin/assign-role-page.php' );
	}

	/**
	 * Include an admin template safely.
	 *
	 * @param string $relative_path Relative path from plugin directory.
	 * @return void
	 */
	private function include_admin_template( $relative_path ) {
		$full_path = $this->plugin_dir . ltrim( (string) $relative_path, '/\\' );
		if ( is_readable( $full_path ) ) {
			require $full_path;
		}
	}

	/**
	 * Handle admin GET/POST requests for this plugin.
	 *
	 * Templates should not mutate roles/options directly. This keeps all security
	 * checks centralized and consistent.
	 *
	 * @return void
	 */
	public function handle_admin_requests() {
		if ( ! is_admin() ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->handle_add_role_request();
		$this->handle_add_rule_request();
		$this->handle_delete_role_request();
		$this->handle_delete_rule_request();
	}

	/**
	 * Handle POST from "Add Role" page.
	 *
	 * @return void
	 */
	private function handle_add_role_request() {
		if ( empty( $_POST['aide_role_action'] ) || 'add_role' !== $_POST['aide_role_action'] ) {
			return;
		}

		check_admin_referer( 'aide_role_add_role', self::NONCE_FIELD );

		$role_slug = isset( $_POST['new_role'] ) ? sanitize_key( (string) wp_unslash( $_POST['new_role'] ) ) : '';
		$role_name = isset( $_POST['role_name'] ) ? sanitize_text_field( (string) wp_unslash( $_POST['role_name'] ) ) : '';

		if ( '' === $role_slug || '' === $role_name ) {
			$this->safe_redirect_with_flag( 'aide-role-add', 'error', 'missing_fields' );
		}

		if ( wp_roles()->is_role( $role_slug ) ) {
			$this->safe_redirect_with_flag( 'aide-role-add', 'error', 'role_exists' );
		}

		$result = add_role( $role_slug, $role_name );
		if ( null === $result ) {
			$this->safe_redirect_with_flag( 'aide-role-add', 'error', 'add_failed' );
		}

		$created_roles = get_option( self::OPTION_CREATED_ROLES, [] );
		if ( ! is_array( $created_roles ) ) {
			$created_roles = [];
		}

		if ( ! in_array( $role_slug, $created_roles, true ) ) {
			$created_roles[] = $role_slug;
			update_option( self::OPTION_CREATED_ROLES, array_values( array_unique( $created_roles ) ), false );
		}

		$this->safe_redirect_with_flag( 'aide-role-add', 'success', 'role_added' );
	}

	/**
	 * Handle GET delete action for plugin-created roles.
	 *
	 * @return void
	 */
	private function handle_delete_role_request() {
		$action = isset( $_GET['action'] ) ? sanitize_key( (string) wp_unslash( $_GET['action'] ) ) : '';
		if ( 'delete_role' !== $action ) {
			return;
		}

		$page = isset( $_GET['page'] ) ? sanitize_key( (string) wp_unslash( $_GET['page'] ) ) : '';
		if ( 'aide-role' !== $page ) {
			return;
		}

		$role_slug = isset( $_GET['role'] ) ? sanitize_key( (string) wp_unslash( $_GET['role'] ) ) : '';
		if ( '' === $role_slug ) {
			$this->safe_redirect_with_flag( 'aide-role', 'error', 'missing_role' );
		}

		check_admin_referer( 'aide_role_delete_role_' . $role_slug, self::NONCE_FIELD );

		$created_roles = get_option( self::OPTION_CREATED_ROLES, [] );
		if ( ! is_array( $created_roles ) ) {
			$created_roles = [];
		}

		if ( ! in_array( $role_slug, $created_roles, true ) ) {
			$this->safe_redirect_with_flag( 'aide-role', 'error', 'role_not_deletable' );
		}

		if ( ! wp_roles()->is_role( $role_slug ) ) {
			$this->safe_redirect_with_flag( 'aide-role', 'error', 'role_not_found' );
		}

		// Prevent deletion when role is assigned to any users.
		$user_query = new WP_User_Query(
			[
				'role'         => $role_slug,
				'fields'       => 'ID',
				'number'       => 1,
				'count_total'  => false,
				'no_found_rows'=> true,
			]
		);
		if ( ! empty( $user_query->get_results() ) ) {
			$this->safe_redirect_with_flag( 'aide-role', 'error', 'role_in_use' );
		}

		remove_role( $role_slug );

		$created_roles = array_values(
			array_filter(
				$created_roles,
				static function ( $slug ) use ( $role_slug ) {
					return sanitize_key( (string) $slug ) !== $role_slug;
				}
			)
		);
		update_option( self::OPTION_CREATED_ROLES, $created_roles, false );

		$this->safe_redirect_with_flag( 'aide-role', 'success', 'role_deleted' );
	}

	/**
	 * Handle POST from "Assign Role" page (auto-assign rule creation).
	 *
	 * @return void
	 */
	private function handle_add_rule_request() {
		if ( empty( $_POST['aide_role_action'] ) || 'add_rule' !== $_POST['aide_role_action'] ) {
			return;
		}

		check_admin_referer( 'aide_role_add_rule', self::NONCE_FIELD );

		$trigger_role  = isset( $_POST['trigger_role'] ) ? sanitize_key( (string) wp_unslash( $_POST['trigger_role'] ) ) : '';
		$assigned_role = isset( $_POST['assigned_role'] ) ? sanitize_key( (string) wp_unslash( $_POST['assigned_role'] ) ) : '';

		if ( '' === $trigger_role || '' === $assigned_role ) {
			$this->safe_redirect_with_flag( 'aide-role-assign', 'error', 'missing_fields' );
		}

		if ( $trigger_role === $assigned_role ) {
			$this->safe_redirect_with_flag( 'aide-role-assign', 'error', 'same_role' );
		}

		$editable_roles = array_keys( (array) get_editable_roles() );
		if ( ! in_array( $trigger_role, $editable_roles, true ) || ! in_array( $assigned_role, $editable_roles, true ) ) {
			$this->safe_redirect_with_flag( 'aide-role-assign', 'error', 'invalid_role' );
		}

		$rules = get_option( self::OPTION_RULES, [] );
		if ( ! is_array( $rules ) ) {
			$rules = [];
		}

		$rules[ wp_generate_uuid4() ] = [
			'trigger'   => $trigger_role,
			'assigned'  => $assigned_role,
			'createdAt' => current_time( 'mysql' ),
		];

		update_option( self::OPTION_RULES, $rules, false );

		$this->safe_redirect_with_flag( 'aide-role-assign', 'success', 'rule_added' );
	}

	/**
	 * Handle GET delete action from "Rule List".
	 *
	 * @return void
	 */
	private function handle_delete_rule_request() {
		$action = isset( $_GET['action'] ) ? sanitize_key( (string) wp_unslash( $_GET['action'] ) ) : '';
		if ( 'delete_rule' !== $action ) {
			return;
		}

		$page = isset( $_GET['page'] ) ? sanitize_key( (string) wp_unslash( $_GET['page'] ) ) : '';
		if ( 'aide-role' !== $page ) {
			return;
		}

		$rule_id = isset( $_GET['rule_id'] ) ? sanitize_text_field( (string) wp_unslash( $_GET['rule_id'] ) ) : '';
		if ( '' === $rule_id ) {
			$this->safe_redirect_with_flag( 'aide-role', 'error', 'missing_rule' );
		}

		check_admin_referer( 'aide_role_delete_rule_' . $rule_id, self::NONCE_FIELD );

		$rules = get_option( self::OPTION_RULES, [] );
		if ( is_array( $rules ) && isset( $rules[ $rule_id ] ) ) {
			unset( $rules[ $rule_id ] );
			update_option( self::OPTION_RULES, $rules, false );
			$this->safe_redirect_with_flag( 'aide-role', 'success', 'rule_deleted' );
		}

		$this->safe_redirect_with_flag( 'aide-role', 'error', 'rule_not_found' );
	}

	/**
	 * Redirect to a plugin admin page with a status flag.
	 *
	 * @param string $page   Admin page slug (value of `page=`).
	 * @param string $status One of: success|error.
	 * @param string $code   Short status code.
	 * @return void
	 */
	private function safe_redirect_with_flag( $page, $status, $code ) {
		$url = add_query_arg(
			[
				'page'   => sanitize_key( (string) $page ),
				'status' => sanitize_key( (string) $status ),
				'code'   => sanitize_key( (string) $code ),
			],
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Auto-assign roles based on saved rules when a user is created.
	 *
	 * @param int $user_id Newly created user ID.
	 * @return void
	 */
	public function auto_assign_roles( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			return;
		}

		$user = get_userdata( $user_id );
		if ( ! ( $user instanceof WP_User ) ) {
			return;
		}

		$rules = get_option( self::OPTION_RULES, [] );
		if ( ! is_array( $rules ) || empty( $rules ) ) {
			return;
		}

		$user_roles = is_array( $user->roles ) ? $user->roles : [];

		foreach ( $rules as $data ) {
			if ( ! is_array( $data ) ) {
				continue;
			}

			$trigger_role  = isset( $data['trigger'] ) ? sanitize_key( (string) $data['trigger'] ) : '';
			$assigned_role = isset( $data['assigned'] ) ? sanitize_key( (string) $data['assigned'] ) : '';

			if ( '' === $trigger_role || '' === $assigned_role ) {
				continue;
			}

			if ( in_array( $trigger_role, $user_roles, true ) && ! in_array( $assigned_role, $user_roles, true ) ) {
				$user->add_role( $assigned_role );
				$user_roles[] = $assigned_role;
			}
		}
	}
}

new Aide_Role_Management();
