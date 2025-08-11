<?php
if (isset($_POST['new_role']) && isset($_POST['role_name'])) {
    add_role(sanitize_key($_POST['new_role']), sanitize_text_field($_POST['role_name']));
    echo '<div class="updated"><p>Role added successfully.</p></div>';
}
?>
<div class="wrap">
    <h1>Add Role</h1>
    <form method="post">
        <table class="form-table">
            <tr>
                <th>Role Slug</th>
                <td><input type="text" name="new_role" required></td>
            </tr>
            <tr>
                <th>Role Name</th>
                <td><input type="text" name="role_name" required></td>
            </tr>
        </table>
        <?php submit_button('Add Role'); ?>
    </form>
</div>
