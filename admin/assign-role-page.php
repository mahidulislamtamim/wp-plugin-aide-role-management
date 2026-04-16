<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$roles = (array) get_editable_roles();

$status = isset( $_GET['status'] ) ? sanitize_key( (string) wp_unslash( $_GET['status'] ) ) : '';
$code   = isset( $_GET['code'] ) ? sanitize_key( (string) wp_unslash( $_GET['code'] ) ) : '';

if ( 'success' === $status && 'rule_added' === $code ) {
	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Rule saved successfully.', 'aiderolemanagement' ) . '</p></div>';
} elseif ( 'error' === $status ) {
	$message = esc_html__( 'Something went wrong. Please try again.', 'aiderolemanagement' );

	if ( 'missing_fields' === $code ) {
		$message = esc_html__( 'Please select both roles.', 'aiderolemanagement' );
	} elseif ( 'same_role' === $code ) {
		$message = esc_html__( 'Trigger role and assigned role must be different.', 'aiderolemanagement' );
	} elseif ( 'invalid_role' === $code ) {
		$message = esc_html__( 'One of the selected roles is invalid.', 'aiderolemanagement' );
	}

	echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
}
?>
<div class="wrap">
    <h1><?php echo esc_html__( 'Assign Role Automatically', 'aiderolemanagement' ); ?></h1>
    <form method="post">
        <input type="hidden" name="aide_role_action" value="add_rule" />
		<?php wp_nonce_field( 'aide_role_add_rule', 'aide_role_management_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="aide_trigger_role"><?php echo esc_html__( 'When User Has Role', 'aiderolemanagement' ); ?></label></th>
                <td>
                    <select id="aide_trigger_role" name="trigger_role" required>
                        <option value=""><?php echo esc_html__( 'Select Role', 'aiderolemanagement' ); ?></option>
                        <?php foreach ( $roles as $slug => $role ) : ?>
                            <option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $role['name'] ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="aide_assigned_role"><?php echo esc_html__( 'Also Assign Role', 'aiderolemanagement' ); ?></label></th>
                <td>
                    <select id="aide_assigned_role" name="assigned_role" required>
                        <option value=""><?php echo esc_html__( 'Select Role', 'aiderolemanagement' ); ?></option>
                        <?php foreach ( $roles as $slug => $role ) : ?>
                            <option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $role['name'] ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php submit_button( __( 'Save Rule', 'aiderolemanagement' ) ); ?>
    </form>
</div>
