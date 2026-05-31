<?php
    $pageTitle = 'Buses';
    require_once 'header.php';
    require_once '../databaseconnection.php';
    //add bus
    if(isset($_POST['btnadd'])) {
        $bus_number         = $_POST['bus_number'];
        $seating_capacity   = $_POST['seating_capacity'];
        $driver_name        = $_POST['driver_name'];
        $departure_location = $_POST['departure_location'];
        $destination        = $_POST['destination'];
        $departure_time     = $_POST['departure_time'];
        $arrival_time       = $_POST['arrival_time'];

        $insertsql = "INSERT INTO ccm_tbl_bus (bus_number, seating_capacity, driver_name, departure_location, destination, departure_time, arrival_time)
                      VALUES ('$bus_number', '$seating_capacity', '$driver_name', '$departure_location', '$destination', '$departure_time', '$arrival_time')";
        $result = $conn->query($insertsql);

        if($result == true) {
            $logsql = "INSERT INTO ccm_tbl_logs (user_id, action, datetime) VALUES ('" . $_SESSION['id'] . "', 'Added bus: $bus_number', NOW())";
            $conn->query($logsql);
            echo "<script>
                Swal.fire({ icon: 'success', title: 'Bus Added!', showConfirmButton: false, timer: 1500 });
            </script>";
        } else {
            echo "<script>Swal.fire({ icon: 'error', title: 'Error', text: '" . $conn->error . "' });</script>";
        }
    }
    //edit bus entrty
    if(isset($_POST['btnedit'])) {
        $bus_id             = $_POST['bus_id'];
        $bus_number         = $_POST['bus_number'];
        $seating_capacity   = $_POST['seating_capacity'];
        $driver_name        = $_POST['driver_name'];
        $departure_location = $_POST['departure_location'];
        $destination        = $_POST['destination'];
        $departure_time     = $_POST['departure_time'];
        $arrival_time       = $_POST['arrival_time'];

        $updatesql = "UPDATE ccm_tbl_bus SET bus_number='$bus_number', seating_capacity='$seating_capacity',
                      driver_name='$driver_name', departure_location='$departure_location',
                      destination='$destination', departure_time='$departure_time', arrival_time='$arrival_time'
                      WHERE bus_id='$bus_id'";
        $conn->query($updatesql);

        $logsql = "INSERT INTO ccm_tbl_logs (user_id, action, datetime) VALUES ('" . $_SESSION['id'] . "', 'Edited bus ID: $bus_id', NOW())";
        $conn->query($logsql);
        echo "<script>Swal.fire({ icon: 'success', title: 'Bus Updated!', showConfirmButton: false, timer: 1500 });</script>";
    }

    //delete bus entry
    if(isset($_GET['delete'])) {
        $bus_id = $_GET['delete'];
        $conn->query("DELETE FROM ccm_tbl_bus WHERE bus_id='$bus_id'");
        $logsql = "INSERT INTO ccm_tbl_logs (user_id, action, datetime) VALUES ('" . $_SESSION['id'] . "', 'Deleted bus ID: $bus_id', NOW())";
        $conn->query($logsql);
        echo "<script>Swal.fire({ icon: 'success', title: 'Bus Deleted!', showConfirmButton: false, timer: 1500 });</script>";
    }

    $displaysql = "SELECT * FROM ccm_tbl_bus ORDER BY bus_id DESC";
    $result = $conn->query($displaysql);
?>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Bus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <div class="modal-body row g-3">
                    <div class="col-6">
                        <label class="form-label">Bus Number</label>
                        <input type="text" name="bus_number" class="form-control" required>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Seating Capacity</label>
                        <input type="number" name="seating_capacity" class="form-control" required min="1">
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Driver Name</label>
                        <input type="text" name="driver_name" class="form-control" required>
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
                        <label class="form-label">Departure Time</label>
                        <input type="time" name="departure_time" class="form-control" required>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Arrival Time</label>
                        <input type="time" name="arrival_time" class="form-control" required>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="btnadd" class="btn btn-primary">Add Bus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Bus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="bus_id" id="edit_bus_id">
                <div class="modal-body row g-3">
                    <div class="col-6">
                        <label class="form-label">Bus Number</label>
                        <input type="text" name="bus_number" id="edit_bus_number" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Seating Capacity</label>
                        <input type="number" name="seating_capacity" id="edit_seating_capacity" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Driver Name</label>
                        <input type="text" name="driver_name" id="edit_driver_name" class="form-control" required>
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
                        <label class="form-label">Departure Time</label>
                        <input type="time" name="departure_time" id="edit_departure_time" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Arrival Time</label>
                        <input type="time" name="arrival_time" id="edit_arrival_time" class="form-control" required>
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
    <h5 class="mb-0"><i class="bi bi-bus-front me-2 text-primary"></i>Bus List</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-lg me-1"></i> Add Bus
    </button>
</div>

<div class="mb-3">
    <input type="text" id="searchInput" class="form-control" placeholder="Search buses...">
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="busTable">
                <thead class="table-primary">
                    <tr>
                        <th>Bus Number</th>
                        <th>Driver Name</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Departure</th>
                        <th>Arrival</th>
                        <th>Capacity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if($result->num_rows > 0) {
                        foreach($result as $fieldnames) {
                            echo "<tr>
                                <td>" . htmlspecialchars($fieldnames['bus_number']) . "</td>
                                <td>" . htmlspecialchars($fieldnames['driver_name']) . "</td>
                                <td>" . htmlspecialchars($fieldnames['departure_location']) . "</td>
                                <td>" . htmlspecialchars($fieldnames['destination']) . "</td>
                                <td>" . $fieldnames['departure_time'] . "</td>
                                <td>" . $fieldnames['arrival_time'] . "</td>
                                <td>" . $fieldnames['seating_capacity'] . "</td>
                                <td>
                                    <button class='btn btn-warning btn-sm' onclick='openEdit(" . $fieldnames['bus_id'] . ", \"" . addslashes($fieldnames['bus_number']) . "\", " . $fieldnames['seating_capacity'] . ", \"" . addslashes($fieldnames['driver_name']) . "\", \"" . addslashes($fieldnames['departure_location']) . "\", \"" . addslashes($fieldnames['destination']) . "\", \"" . $fieldnames['departure_time'] . "\", \"" . $fieldnames['arrival_time'] . "\")'>
                                        <i class='bi bi-pencil'></i>
                                    </button>
                                    <a href='?delete=" . $fieldnames['bus_id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Delete this bus?\")'>
                                        <i class='bi bi-trash'></i>
                                    </a>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center text-muted py-3'>No buses found. Add one!</td></tr>";
                    }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function openEdit(id, num, cap, driver, depLoc, dest, depTime, arrTime) {
    document.getElementById('edit_bus_id').value             = id;
    document.getElementById('edit_bus_number').value         = num;
    document.getElementById('edit_seating_capacity').value   = cap;
    document.getElementById('edit_driver_name').value        = driver;
    document.getElementById('edit_departure_location').value = depLoc;
    document.getElementById('edit_destination').value        = dest;
    document.getElementById('edit_departure_time').value     = depTime;
    document.getElementById('edit_arrival_time').value       = arrTime;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

document.getElementById('searchInput').addEventListener('keyup', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#busTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
    });
});
</script>

<?php require_once 'footer.php'; ?>