<?php
/**
 * Uninstall cleanup.
 *
 * @package aiderolemanagement
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'aide_role_auto_assign' );
delete_option( 'aide_role_created_roles' );

