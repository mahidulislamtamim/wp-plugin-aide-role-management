<?php
/**
 * Admin UI: role list + auto-assign rule list.
 *
 * All mutations (add/delete) are handled in the main plugin class via `admin_init`.
 *
 * @package aiderolemanagement
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_roles;
if ( ! isset( $wp_roles ) ) {
	$wp_roles = wp_roles();
}

$status = isset( $_GET['status'] ) ? sanitize_key( (string) wp_unslash( $_GET['status'] ) ) : '';
$code   = isset( $_GET['code'] ) ? sanitize_key( (string) wp_unslash( $_GET['code'] ) ) : '';

if ( 'success' === $status && 'rule_deleted' === $code ) {
	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Rule deleted.', 'aiderolemanagement' ) . '</p></div>';
} elseif ( 'success' === $status && 'role_deleted' === $code ) {
	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Role deleted.', 'aiderolemanagement' ) . '</p></div>';
} elseif ( 'error' === $status && 'rule_not_found' === $code ) {
	echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Rule not found.', 'aiderolemanagement' ) . '</p></div>';
} elseif ( 'error' === $status ) {
	$message = esc_html__( 'Something went wrong. Please try again.', 'aiderolemanagement' );

	if ( 'missing_role' === $code ) {
		$message = esc_html__( 'Missing role.', 'aiderolemanagement' );
	} elseif ( 'role_not_deletable' === $code ) {
		$message = esc_html__( 'This role cannot be deleted by the plugin.', 'aiderolemanagement' );
	} elseif ( 'role_not_found' === $code ) {
		$message = esc_html__( 'Role not found.', 'aiderolemanagement' );
	} elseif ( 'role_in_use' === $code ) {
		$message = esc_html__( 'This role is assigned to users and cannot be deleted.', 'aiderolemanagement' );
	}

	echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
}

$rules = get_option( 'aide_role_auto_assign', [] );
if ( ! is_array( $rules ) ) {
	$rules = [];
}

require_once __DIR__ . '/class-aide-role-rules-table.php';
$rules_table = new Aide_Role_Rules_Table( $rules );
$rules_table->prepare_items();
?>

<div class="wrap aide-role-management">
	<h1><?php echo esc_html__( 'Aide Role Management', 'aiderolemanagement' ); ?></h1>

	<h2 class="title">
		<?php echo esc_html__( 'Role List', 'aiderolemanagement' ); ?>
		<small><?php echo esc_html__( '(List of all roles in the system)', 'aiderolemanagement' ); ?></small>
	</h2>

	<p>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=aide-role-add' ) ); ?>" class="button button-primary">
			<?php echo esc_html__( '+ Add New Role', 'aiderolemanagement' ); ?>
		</a>
	</p>

	<table class="wp-list-table widefat fixed striped table-view-list">
		<thead>
			<tr>
				<th scope="col"><?php echo esc_html__( 'Display Name', 'aiderolemanagement' ); ?></th>
				<th scope="col"><?php echo esc_html__( 'Role Key', 'aiderolemanagement' ); ?></th>
				<th scope="col"><?php echo esc_html__( 'Actions', 'aiderolemanagement' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$created_roles = get_option( 'aide_role_created_roles', [] );
			if ( ! is_array( $created_roles ) ) {
				$created_roles = [];
			}
			?>
			<?php foreach ( (array) $wp_roles->roles as $role_key => $role_details ) : ?>
				<?php
				$role_key       = sanitize_key( (string) $role_key );
				$is_deletable   = in_array( $role_key, $created_roles, true );
				$delete_role_url = $is_deletable ? wp_nonce_url(
					add_query_arg(
						[
							'page'   => 'aide-role',
							'action' => 'delete_role',
							'role'   => $role_key,
						],
						admin_url( 'admin.php' )
					),
					'aide_role_delete_role_' . $role_key,
					'aide_role_management_nonce'
				) : '';
				?>
				<tr>
					<td><?php echo esc_html( $role_details['name'] ?? '' ); ?></td>
					<td><code><?php echo esc_html( (string) $role_key ); ?></code></td>
					<td>
						<?php if ( $is_deletable ) : ?>
							<a
								href="<?php echo esc_url( $delete_role_url ); ?>"
								class="button button-secondary"
								onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this role? Users with this role will prevent deletion.', 'aiderolemanagement' ) ); ?>');"
							>
								<?php echo esc_html__( 'Delete', 'aiderolemanagement' ); ?>
							</a>
						<?php else : ?>
							<span class="dashicons dashicons-minus" aria-hidden="true"></span>
							<span class="screen-reader-text"><?php echo esc_html__( 'Not deletable', 'aiderolemanagement' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<hr />

	<h2 class="title">
		<?php echo esc_html__( 'Rule List', 'aiderolemanagement' ); ?>
		<small><?php echo esc_html__( '(List of all configured rules)', 'aiderolemanagement' ); ?></small>
	</h2>

	<p>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=aide-role-assign' ) ); ?>" class="button button-primary">
			<?php echo esc_html__( '+ Add New Rule', 'aiderolemanagement' ); ?>
		</a>
	</p>

	<form method="get">
		<input type="hidden" name="page" value="<?php echo esc_attr( sanitize_key( (string) ( $_GET['page'] ?? 'aide-role' ) ) ); ?>" />
		<?php $rules_table->search_box( __( 'Search Rules', 'aiderolemanagement' ), 'aide-role-rules' ); ?>
		<?php $rules_table->display(); ?>
	</form>
</div>

