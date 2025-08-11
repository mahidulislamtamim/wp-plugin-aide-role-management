<?php
$roles = wp_roles()->roles;
$rules = get_option('aide_role_auto_assign', []);

if (isset($_POST['trigger_role']) && isset($_POST['assigned_role'])) {
    $rules[uniqid()] = [
           "trigger" => sanitize_key($_POST['trigger_role']),
           "assigned" => sanitize_key($_POST['assigned_role']),
           "date" => date("d-m-Y"),
    ];
    
    update_option('aide_role_auto_assign', $rules);
    echo '<div class="updated"><p>Rule saved successfully.</p></div>';
}
?>
<div class="wrap">
    <h1>Assign Role Automatically</h1>
    <form method="post">
        <table class="form-table">
            <tr>
                <th>When User Has Role</th>
                <td>
                    <select name="trigger_role" required>
                        <option value="">Select Role</option>
                        <?php foreach ($roles as $slug => $role): ?>
                            <option value="<?php echo esc_attr($slug); ?>"><?php echo esc_html($role['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Also Assign Role</th>
                <td>
                    <select name="assigned_role" required>
                        <option value="">Select Role</option>
                        <?php foreach ($roles as $slug => $role): ?>
                            <option value="<?php echo esc_attr($slug); ?>"><?php echo esc_html($role['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php submit_button('Save Rule'); ?>
    </form>
</div>
