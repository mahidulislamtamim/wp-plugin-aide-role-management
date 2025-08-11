# Aide::Role Management

**Aide::Role Management** is a lightweight WordPress plugin that allows administrators to easily manage WordPress roles and set up automatic role assignment rules.  
It is designed for situations where you need to auto-assign one or more roles to users based on their primary role or registration events.

---

## ✨ Features

- **Role List View** – View all registered WordPress roles in a sortable and searchable list.
- **Add New Role** – Create custom roles directly from the admin dashboard.
- **Auto Assign Role Rules** – Define rules to automatically assign additional roles when a user is registered or updated.
- **Role Deletion for Rules** – Easily remove existing auto-assign rules.
- **Clean Admin UI** – Simple, WordPress-style admin interface.

## 📂 Plugin Structure

wp-plugin-aide-role-management/

├── aide-role-management.php # Main plugin file

├── admin/

│ ├── main.php # UI for role listing

│ ├── add-role.php # Add role functionality

│ ├── assign-role.php # Auto-assign rule UI

│ └── functions.php # Helper functions

└── README.md

---

## 🚀 Installation

1. **Download** or **Clone** the repository.
   ```bash
   git clone https://github.com/mahidulislamtamim/wp-plugin-aide-role-management.git
Upload the folder to your WordPress plugins directory:

swift
Copy
Edit
/wp-content/plugins/wp-plugin-aide-role-management/
Activate the plugin from WordPress Admin → Plugins.

## 🛠️ Usage
Add a New Role
Navigate to Aide - Role → Add Role.

Fill in the role name, slug, and capabilities.

Click Save.

Assign Roles Automatically
Navigate to Aide - Role → Assign Role.

Create a rule: When a user has role X, automatically assign role Y.

Save the rule.

The rule will be applied when new users are created or updated.

View and Manage Rules
Navigate to Aide - Role → Role Rules.

View existing auto-assign rules in a sortable and searchable table.

Delete unwanted rules with one click.

## ⚙️ Hooks & Filters
The plugin provides several hooks for developers:

aide_role_added – Fires when a new role is created.

aide_role_rule_added – Fires when an auto-assign rule is added.

aide_role_rule_deleted – Fires when a rule is removed.

## 📌 Requirements
WordPress 5.5+

PHP 7.4+

## 📄 License
This plugin is licensed under the GPL-2.0+.

## 🤝 Contributing
Pull requests are welcome! If you have suggestions, bug fixes, or improvements:

Fork the repository.

Create your feature branch.

Submit a pull request.

## 👨‍💻 Author

Developed by Aide .

Website: https://aide247.com
