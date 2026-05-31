<?php
    $pageTitle = 'Routes';
    require_once 'header.php';
    require_once '../databaseconnection.php';
    //add new route
    if(isset($_POST['btnadd'])) {
        $route_name         = $_POST['route_name'];
        $departure_location = $_POST['departure_location'];
        $destination        = $_POST['destination'];
        $distance           = $_POST['distance'];
        $duration           = $_POST['duration'];

        $insertsql = "INSERT INTO ccm_tbl_route (route_name, departure_location, destination, distance, duration)
                      VALUES ('$route_name', '$departure_location', '$destination', '$distance', '$duration')";
        $conn->query($insertsql);

        $logsql = "INSERT INTO ccm_tbl_logs (user_id, action, datetime) VALUES ('" . $_SESSION['id'] . "', 'Added route: $route_name', NOW())";
        $conn->query($logsql);
        echo "<script>Swal.fire({ icon: 'success', title: 'Route Added!', showConfirmButton: false, timer: 1500 });</script>";
    }
    //edit routes
    if(isset($_POST['btnedit'])) {
        $route_id           = $_POST['route_id'];
        $route_name         = $_POST['route_name'];
        $departure_location = $_POST['departure_location'];
        $destination        = $_POST['destination'];
        $distance           = $_POST['distance'];
        $duration           = $_POST['duration'];

        $updatesql = "UPDATE ccm_tbl_route SET route_name='$route_name', departure_location='$departure_location',
                      destination='$destination', distance='$distance', duration='$duration'
                      WHERE route_id='$route_id'";
        $conn->query($updatesql);

        $logsql = "INSERT INTO ccm_tbl_logs (user_id, action, datetime) VALUES ('" . $_SESSION['id'] . "', 'Edited route ID: $route_id', NOW())";
        $conn->query($logsql);
        echo "<script>Swal.fire({ icon: 'success', title: 'Route Updated!', showConfirmButton: false, timer: 1500 });</script>";
    }
    //delete a route
    if(isset($_GET['delete'])) {
        $route_id = $_GET['delete'];
        $conn->query("DELETE FROM ccm_tbl_route WHERE route_id='$route_id'");
        $logsql = "INSERT INTO ccm_tbl_logs (user_id, action, datetime) VALUES ('" . $_SESSION['id'] . "', 'Deleted route ID: $route_id', NOW())";
        $conn->query($logsql);
        echo "<script>Swal.fire({ icon: 'success', title: 'Route Deleted!', showConfirmButton: false, timer: 1500 });</script>";
    }

    //string query - descending order
    $displaysql = "SELECT * FROM ccm_tbl_route ORDER BY route_id DESC";
    $result = $conn->query($displaysql);
?>
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Route</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Route Name</label>
                        <input type="text" name="route_name" class="form-control" required>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Departure Location</label>
                        <input type="text" name="departure_location" class="form-control" required>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Destination</label>
                        <input type="text" name="destination" class="form-control" required>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Distance (e.g. 120 km)</label>
                        <input type="text" name="distance" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Duration (e.g. 2 hrs)</label>
                        <input type="text" name="duration" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="btnadd" class="btn btn-success">Add Route</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Route</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="route_id" id="edit_route_id">
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Route Name</label>
                        <input type="text" name="route_name" id="edit_route_name" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Departure Location</label>
                        <input type="text" name="departure_location" id="edit_departure_location" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Destination</label>
                        <input type="text" name="destination" id="edit_destination" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Distance</label>
                        <input type="text" name="distance" id="edit_distance" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Duration</label>
                        <input type="text" name="duration" id="edit_duration" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="btnedit" class="btn btn-warning">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="bi bi-signpost-split me-2 text-success"></i>Route List</h5>
    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-lg me-1"></i> Add Route
    </button>
</div>

<div class="mb-3">
    <input type="text" id="searchInput" class="form-control" placeholder="Search routes...">
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="routeTable">
                <thead class="table-success">
                    <tr>
                        <th>Route Name</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Distance</th>
                        <th>Duration</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if($result->num_rows > 0) {
                        foreach($result as $fieldnames) {
                            echo "<tr>
                                <td>" . htmlspecialchars($fieldnames['route_name']) . "</td>
                                <td>" . htmlspecialchars($fieldnames['departure_location']) . "</td>
                                <td>" . htmlspecialchars($fieldnames['destination']) . "</td>
                                <td>" . htmlspecialchars($fieldnames['distance'] ?? '-') . "</td>
                                <td>" . htmlspecialchars($fieldnames['duration'] ?? '-') . "</td>
                                <td>
                                    <button class='btn btn-warning btn-sm' onclick='openEdit(" . $fieldnames['route_id'] . ", \"" . addslashes($fieldnames['route_name']) . "\", \"" . addslashes($fieldnames['departure_location']) . "\", \"" . addslashes($fieldnames['destination']) . "\", \"" . addslashes($fieldnames['distance'] ?? '') . "\", \"" . addslashes($fieldnames['duration'] ?? '') . "\")'>
                                        <i class='bi bi-pencil'></i>
                                    </button>
                                    <a href='?delete=" . $fieldnames['route_id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Delete this route?\")'>
                                        <i class='bi bi-trash'></i>
                                    </a>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center text-muted py-3'>No routes found. Add one!</td></tr>";
                    }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function openEdit(id, name, depLoc, dest, dist, dur) {
    document.getElementById('edit_route_id').value            = id;
    document.getElementById('edit_route_name').value          = name;
    document.getElementById('edit_departure_location').value  = depLoc;
    document.getElementById('edit_destination').value         = dest;
    document.getElementById('edit_distance').value            = dist;
    document.getElementById('edit_duration').value            = dur;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

document.getElementById('searchInput').addEventListener('keyup', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#routeTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
    });
});
</script>

<?php require_once 'footer.php'; ?>