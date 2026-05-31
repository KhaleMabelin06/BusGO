<?php
    $pageTitle = 'Logs';
    require_once 'header.php';
    require_once '../databaseconnection.php';

    $displaysql = "SELECT l.log_id, l.action, l.datetime,
                          u.full_name, u.role
                   FROM ccm_tbl_logs l
                   LEFT JOIN ccm_tbl_users u ON l.user_id = u.user_id
                   ORDER BY l.datetime DESC";
    $result = $conn->query($displaysql);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="bi bi-journal-text me-2 text-primary"></i>System Logs</h5>
</div>
<div class="mb-3">
    <input type="text" id="searchInput" class="form-control" placeholder="Search logs...">
</div>
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="logTable">
                <thead class="table-primary">
                    <tr>
                        <th>Full Name</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if($result->num_rows > 0) {
                        foreach($result as $fieldnames) {
                            $roleBadge = match($fieldnames['role'] ?? '') {
                                'admin'    => 'bg-danger',
                                'employee' => 'bg-warning text-dark',
                                'customer' => 'bg-primary',
                                default    => 'bg-secondary'
                            };
                            echo "<tr>
                                <td>" . htmlspecialchars($fieldnames['full_name'] ?? 'Deleted User') . "</td>
                                <td><span class='badge $roleBadge'>" . ucfirst($fieldnames['role'] ?? 'N/A') . "</span></td>
                                <td>" . htmlspecialchars($fieldnames['action']) . "</td>
                                <td>" . $fieldnames['datetime'] . "</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center text-muted py-3'>No logs yet.</td></tr>";
                    }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#logTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
    });
});
</script>

<?php require_once 'footer.php'; ?>