<?php
global $wp_roles;
if (!isset($wp_roles)) {
    $wp_roles = new WP_Roles();
}
?>
<div class="wrap aide-role-management">
    <div class="aide-header">
        <h1>Role List <small>(List of all roles in the system)</small></h1>
        <a href="<?php echo admin_url('admin.php?page=aide-role-add'); ?>" class="button button-primary">+ Add New Role</a>
    </div>
    

    <div class="aide-table-container">
        <table id="aide-role-table" class="wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr>
                    <th>Display Name</th>
                    <th>Role Key</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($wp_roles->roles as $role_key => $role_details) : ?>
                    <tr>
                        <td><?php echo esc_html($role_details['name']); ?></td>
                        <td><code><?php echo esc_html($role_key); ?></code></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<style>
    small{
        font-size: 12px;
    }
    .aide-role-management{
        margin-bottom: 30px;
    }
    .aide-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    .aide-header h1 {
        margin: 0;
        font-size: 1.5em;
    }
    .aide-table-container {
        background: #fff;
        padding: 15px;
        border: 1px solid #ccd0d4;
        border-radius: 6px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }
    table.dataTable thead th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    #aide-role-table{
        margin: 20px 0px;
        float: left;
        width: 100%;
    }
    #aide-role-table_length select{
        padding-right: 15px;
    }
    #aide-role-table_filter input {
        border-radius: 4px;
        padding: 4px 8px;
        border: 1px solid #ccd0d4;
    }
    #aide-role-table_paginate a {
        border-radius: 4px !important;
    }
</style>

<script>
jQuery(document).ready(function($) {
    $("#aide-role-table").DataTable({
        pageLength: 10,
        order: [[0, "asc"]],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search roles..."
        }
    });
});
</script>





<?php
$rules = get_option( 'aide_role_auto_assign', [] );

// Handle delete request
if (isset($_GET['action'], $_GET['rule_id'], $_GET['_wpnonce']) && $_GET['action'] === 'delete') {
    if (wp_verify_nonce($_GET['_wpnonce'], 'delete_rule_' . $_GET['rule_id'])) {
        $rule_id = $_GET['rule_id'];
        
        if (isset($rules[$rule_id])) {
            unset($rules[$rule_id]);
            update_option('aide_role_auto_assign', array_values($rules)); // reindex array
            wp_redirect(admin_url('admin.php?page=aide-role&deleted=1'));
            exit;
        }
    }
}
?>
<div class="wrap aide-role-management">

    <div class="aide-role-header">
        <h1>Rule List <small>(List of all rules are configured)</small></h1>
        <a href="<?php echo admin_url('admin.php?page=aide-role-assign'); ?>" class="button button-primary">+ Add New Rule</a>
    </div>

    
    <div class="aide-table-container">
        <table id="aide-rule-table" class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Trigger Role</th>
                    <th>Assigned Role</th>
                    <th>Date Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ( !empty( $rules ) ) {
                    foreach ($rules as $uniqid => $data) {
                        $trigger_role = $data["trigger"];
                        $assigned_role = $data["assigned"];
                        $date = $data["date"];

                        $delete_url =  wp_nonce_url(admin_url('admin.php?page=aide-role&action=delete&rule_id=' . $uniqid), 'delete_rule_' . $uniqid);
                        echo '<tr>
                            <td>' . esc_html( $trigger_role ) . '</td>
                            <td>' . esc_html( $assigned_role ) . '</td>
                            <td>' . esc_html( $date ) . '</td>
                            <td>
                                <a href="'. $delete_url .'" class="button delete-rule" onclick="return confirm(`Are you sure you want to delete this rule?`);"> Delete </a>
                            </td>
                        </tr>';
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .aide-role-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    .aide-role-header h1 {
        margin: 0;
        font-size: 1.5em;
    }
    #aide-rule-table{
        margin: 20px 0px;
        float: left;
        width: 100%;
    }
    #aide-rule-table_length select{
        padding-right: 15px;
    }
    #aide-rule-table_filter input {
        border-radius: 4px;
        padding: 4px 8px;
        border: 1px solid #ccd0d4;
    }
    #aide-rule-table_paginate a {
        border-radius: 4px !important;
    }
</style>

<!-- DataTables JS -->
<script>
jQuery(document).ready(function($) {
    $('#aide-rule-table').DataTable({
        pageLength: 10,
        order: [[0, "asc"]],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search roles..."
        }
    });
});
</script>

