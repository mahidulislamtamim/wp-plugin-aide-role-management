<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$status = isset( $_GET['status'] ) ? sanitize_key( (string) wp_unslash( $_GET['status'] ) ) : '';
$code   = isset( $_GET['code'] ) ? sanitize_key( (string) wp_unslash( $_GET['code'] ) ) : '';

if ( 'success' === $status && 'role_added' === $code ) {
	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Role added successfully.', 'aiderolemanagement' ) . '</p></div>';
} elseif ( 'error' === $status ) {
	$message = esc_html__( 'Something went wrong. Please try again.', 'aiderolemanagement' );

	if ( 'missing_fields' === $code ) {
		$message = esc_html__( 'Please fill in all required fields.', 'aiderolemanagement' );
	} elseif ( 'role_exists' === $code ) {
		$message = esc_html__( 'That role slug already exists.', 'aiderolemanagement' );
	} elseif ( 'add_failed' === $code ) {
		$message = esc_html__( 'Unable to add the role.', 'aiderolemanagement' );
	}

	echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
}
?>
<div class="wrap">
    <h1><?php echo esc_html__( 'Add Role', 'aiderolemanagement' ); ?></h1>
    <form method="post">
        <input type="hidden" name="aide_role_action" value="add_role" />
		<?php wp_nonce_field( 'aide_role_add_role', 'aide_role_management_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="aide_new_role"><?php echo esc_html__( 'Role Slug', 'aiderolemanagement' ); ?></label></th>
                <td><input id="aide_new_role" type="text" name="new_role" class="regular-text" required /></td>
            </tr>
            <tr>
                <th scope="row"><label for="aide_role_name"><?php echo esc_html__( 'Role Name', 'aiderolemanagement' ); ?></label></th>
                <td><input id="aide_role_name" type="text" name="role_name" class="regular-text" required /></td>
            </tr>
        </table>
        <?php submit_button( __( 'Add Role', 'aiderolemanagement' ) ); ?>
    </form>
</div>
