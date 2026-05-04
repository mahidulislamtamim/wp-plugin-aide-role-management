# Aide :: Role Management

WordPress plugin to manage custom roles and configure automatic role assignment rules for newly registered users.

## Features

- Adds an admin menu: **Aide - Role**.
- Create new custom roles from wp-admin.
- Delete only roles created by this plugin (prevents deleting core/third-party roles).
- Define auto-assignment rules:
  - **Trigger role** -> **Assigned role**
  - Applied when a new user is registered.
- Manage and search configured rules from an admin list table.
- Security protections:
  - `manage_options` capability checks
  - Nonce verification for add/delete actions
  - Sanitized request handling and safe redirects

## Requirements

- WordPress 6.0+
- PHP 7.4+

## Installation

1. Copy the `aide-role-management` folder into `wp-content/plugins/`.
2. In WordPress admin, go to **Plugins**.
3. Activate **Aide :: Role Management**.

## Usage

### Add a custom role

1. Go to **Aide - Role -> Add Role**.
2. Enter:
   - role key (slug)
   - display name
3. Save the role.

### Create an auto-assign rule

1. Go to **Aide - Role -> Assign Role**.
2. Choose a trigger role and a role to assign.
3. Save the rule.

When a user is registered and has the trigger role, the assigned role is automatically added.

## Safety Notes

- Role/rule changes are admin-only.
- Role deletion is restricted to roles created by this plugin.
- A role cannot be deleted if it is currently assigned to any user.

## Data Storage

The plugin stores settings in WordPress options:

- `aide_role_auto_assign` (rule set)
- `aide_role_created_roles` (plugin-created role slugs)

## Plugin Details

- **Plugin Name:** Aide :: Role Management
- **Version:** 1.0.0
- **Author:** Aide247
- **Plugin URI:** https://aide247.com/
- **Text Domain:** `aiderolemanagement`
- **License:** GPLv2 or later

