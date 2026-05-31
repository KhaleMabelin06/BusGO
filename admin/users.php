<?php
    $pageTitle = 'Users';
    require_once 'header.php';
    require_once '../databaseconnection.php';
    //add user
    if(isset($_POST['btnadd'])) {
        $full_name           = $_POST['full_name'];
        $role                = $_POST['role'];
        $username            = $_POST['username'];
        $password            = md5($_POST['password']);
        $email               = $_POST['email'];
        $contact_information = $_POST['contact_information'];

        //check if username or email already exists
        $checksql = "SELECT user_id FROM ccm_tbl_users WHERE username='$username' OR email='$email'";
        $checkresult = $conn->query($checksql);

        if($checkresult->num_rows > 0) {
            echo "<script>Swal.fire({ icon: 'warning', title: 'Already Exists', text: 'Username or email is already taken.' });</script>";
        } else {
            $insertsql = "INSERT INTO ccm_tbl_users (full_name, role, username, password, email, contact_information, status)
                          VALUES ('$full_name', '$role', '$username', '$password', '$email', '$contact_information', 'active')";
            $conn->query($insertsql);

            $logsql = "INSERT INTO ccm_tbl_logs (user_id, action, datetime) VALUES ('" . $_SESSION['id'] . "', 'Added user: $username', NOW())";
            $conn->query($logsql);
            echo "<script>Swal.fire({ icon: 'success', title: 'User Added!', showConfirmButton: false, timer: 1500 });</script>";
        }
    }
    //edit user
    if(isset($_POST['btnedit'])) {
        $user_id             = $_POST['user_id'];
        $full_name           = $_POST['full_name'];
        $role                = $_POST['role'];
        $username            = $_POST['username'];
        $email               = $_POST['email'];
        $contact_information = $_POST['contact_information'];
        $status              = $_POST['status'];
        $password            = $_POST['password'];

        //check if username or email belongs to another user already
        $checksql = "SELECT user_id FROM ccm_tbl_users WHERE (username='$username' OR email='$email') AND user_id != '$user_id'";
        $checkresult = $conn->query($checksql);

        if($checkresult->num_rows > 0) {
            echo "<script>Swal.fire({ icon: 'warning', title: 'Already Exists', text: 'Username or email is already used by another account.' });</script>";
        } else {
            if($password != '') {
                $password = md5($password);
                $updatesql = "UPDATE ccm_tbl_users SET full_name='$full_name', role='$role', username='$username', email='$email',
                              contact_information='$contact_information', status='$status', password='$password'
                              WHERE user_id='$user_id'";
            } else {
                $updatesql = "UPDATE ccm_tbl_users SET full_name='$full_name', role='$role', username='$username', email='$email',
                              contact_information='$contact_information', status='$status'
                              WHERE user_id='$user_id'";
            }

            $conn->query($updatesql);
            $logsql = "INSERT INTO ccm_tbl_logs (user_id, action, datetime) VALUES ('" . $_SESSION['id'] . "', 'Edited user ID: $user_id', NOW())";
            $conn->query($logsql);
            echo "<script>Swal.fire({ icon: 'success', title: 'User Updated!', showConfirmButton: false, timer: 1500 });</script>";
        }
    }
    //delete user
    if(isset($_GET['delete'])) {
        $user_id = $_GET['delete'];
        if($user_id != $_SESSION['id']) {
            $conn->query("DELETE FROM ccm_tbl_users WHERE user_id='$user_id'");
            $logsql = "INSERT INTO ccm_tbl_logs (user_id, action, datetime) VALUES ('" . $_SESSION['id'] . "', 'Deleted user ID: $user_id', NOW())";
            $conn->query($logsql);
            echo "<script>Swal.fire({ icon: 'success', title: 'User Deleted!', showConfirmButton: false, timer: 1500 });</script>";
        } else {
            echo "<script>Swal.fire({ icon: 'error', title: 'Action Denied', text: 'You cannot delete your own account.' });</script>";
        }
    }
    $displaysql = "SELECT * FROM ccm_tbl_users ORDER BY created_at DESC";
    $result = $conn->query($displaysql);
?>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="">Select role</option>
                            <option value="admin">Admin</option>
                            <option value="employee">Employee</option>
                            <option value="customer">Customer</option>
                        </select>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_information" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                        <div class="invalid-feedback">Min. 6 characters.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="btnadd" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Role</label>
                        <select name="role" id="edit_role" class="form-select" required>
                            <option value="admin">Admin</option>
                            <option value="employee">Employee</option>
                            <option value="customer">Customer</option>
                        </select>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_status" class="form-select" required>
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                        </select>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_information" id="edit_contact_information" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="btnedit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="bi bi-people me-2 text-primary"></i>User List</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-lg me-1"></i> Add User
    </button>
</div>

<div class="mb-3">
    <input type="text" id="searchInput" class="form-control" placeholder="Search users...">
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="userTable">
                <thead class="table-primary">
                    <tr>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if($result->num_rows > 0) {
                        foreach($result as $fieldnames) {
                            $roleBadge = match($fieldnames['role']) {
                                'admin'    => 'bg-danger',
                                'employee' => 'bg-warning text-dark',
                                default    => 'bg-primary'
                            };
                            $statusBadge = $fieldnames['status'] == 'active' ? 'bg-success' : 'bg-secondary';
                            $editBtn = "<button class='btn btn-warning btn-sm text-white' onclick='openEditUser(" . $fieldnames['user_id'] . ", \"" . addslashes($fieldnames['full_name']) . "\", \"" . $fieldnames['role'] . "\", \"" . addslashes($fieldnames['username']) . "\", \"" . addslashes($fieldnames['email']) . "\", \"" . addslashes($fieldnames['contact_information'] ?? '') . "\", \"" . $fieldnames['status'] . "\")'><i class='bi bi-pencil-square'></i></button>";
                            $deleteBtn = $fieldnames['user_id'] != $_SESSION['id']
                                ? $editBtn . " <a href='?delete=" . $fieldnames['user_id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Delete this user?\")'><i class='bi bi-trash'></i></a>"
                                : $editBtn . " <span class='text-muted small'>You</span>";

                            echo "<tr>
                                <td>" . htmlspecialchars($fieldnames['full_name']) . "</td>
                                <td>" . htmlspecialchars($fieldnames['username']) . "</td>
                                <td>" . htmlspecialchars($fieldnames['email']) . "</td>
                                <td>" . htmlspecialchars($fieldnames['contact_information'] ?? '-') . "</td>
                                <td><span class='badge $roleBadge'>" . ucfirst($fieldnames['role']) . "</span></td>
                                <td><span class='badge $statusBadge'>" . ucfirst($fieldnames['status']) . "</span></td>
                                <td>" . $fieldnames['created_at'] . "</td>
                                <td>$deleteBtn</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center text-muted py-3'>No users found.</td></tr>";
                    }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function openEditUser(user_id, full_name, role, username, email, contact_information, status) {
    document.getElementById('edit_user_id').value = user_id;
    document.getElementById('edit_full_name').value = full_name;
    document.getElementById('edit_role').value = role;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_contact_information').value = contact_information;
    document.getElementById('edit_status').value = status;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

document.getElementById('searchInput').addEventListener('keyup', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#userTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
    });
});
</script>

<?php require_once 'footer.php'; ?>