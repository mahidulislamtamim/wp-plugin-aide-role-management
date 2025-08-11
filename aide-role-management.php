<?php
/*
Plugin Name: Aide::Role Management
Description: Manage roles and set auto role assignment rules.
Version: 1.0
Author: Aide247
Version: 1.0.0
Last Updated : "Aug 15, 2025",
Author URI: https://aide247.com/
Text Domain: aiderolemanagement
Domain Path: /languages
Plugin URI: https://aide247.com/
*/

if (!defined('ABSPATH')) exit; // No direct access

class AideRoleManagement {
    public function __construct() {
        add_action('admin_menu', [$this, 'register_admin_pages']);
        add_action('user_register', [$this, 'auto_assign_roles']);
    }

    // Create menu & submenus
    public function register_admin_pages() {
        add_menu_page(
            'Aide Role Management',
            'Aide - Role',
            'manage_options',
            'aide-role',
            [$this, 'main_page'],
            'dashicons-admin-users',
            50
        );

        add_submenu_page(
            'aide-role',
            'List',
            'List',
            'manage_options',
            'aide-role',
            [$this, 'main_page']
        );

        add_submenu_page(
            'aide-role',
            'Add Role',
            'Add Role',
            'manage_options',
            'aide-role-add',
            [$this, 'add_role_page']
        );

        add_submenu_page(
            'aide-role',
            'Assign Role',
            'Assign Role',
            'manage_options',
            'aide-role-assign',
            [$this, 'assign_role_page']
        );
    }

    // Main Page
    public function main_page() {
        include plugin_dir_path(__FILE__) . 'admin/main.php';
    }


    // Add Role Page
    public function add_role_page() {
        include plugin_dir_path(__FILE__) . 'admin/add-role-page.php';
    }

    // Assign Role Page
    public function assign_role_page() {
        include plugin_dir_path(__FILE__) . 'admin/assign-role-page.php';
    }

    // Auto Assign Logic
    public function auto_assign_roles($user_id) {
        $user = get_userdata($user_id);
        $rules = get_option('aide_role_auto_assign', []);

        foreach ($rules as $uniqid => $data) {
            
            $trigger_role = $data["trigger"];
            $assigned_role = $data["assigned"];
            if (in_array($trigger_role, $user->roles) && !in_array($assigned_role, $user->roles)) {
                $user->add_role($assigned_role);
            }
        }
    }
}

new AideRoleManagement();
