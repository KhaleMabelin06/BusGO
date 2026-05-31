<?php
    $pageTitle = 'Schedules';
    require_once 'header.php';
    require_once '../databaseconnection.php';
    //add sched
    if(isset($_POST['btnadd'])) {
        $bus_id         = $_POST['bus_id'];
        $route_id       = $_POST['route_id'];
        $departure_date = $_POST['departure_date'];
        $departure_time = $_POST['departure_time'];
        $available_seats = $_POST['available_seats'];

        $insertsql = "INSERT INTO ccm_tbl_schedule (bus_id, route_id, departure_date, departure_time, available_seats)
                      VALUES ('$bus_id', '$route_id', '$departure_date', '$departure_time', '$available_seats')";
        $conn->query($insertsql);

        $logsql = "INSERT INTO ccm_tbl_logs (user_id, action, datetime) VALUES ('" . $_SESSION['id'] . "', 'Added schedule', NOW())";
        $conn->query($logsql);
        echo "<script>Swal.fire({ icon: 'success', title: 'Schedule Added!', showConfirmButton: false, timer: 1500 });</script>";
    }
    //edit sched
    if(isset($_POST['btnedit'])) {
        $schedule_id    = $_POST['schedule_id'];
        $bus_id         = $_POST['bus_id'];
        $route_id       = $_POST['route_id'];
        $departure_date = $_POST['departure_date'];
        $departure_time = $_POST['departure_time'];
        $available_seats = $_POST['available_seats'];

        $updatesql = "UPDATE ccm_tbl_schedule SET bus_id='$bus_id', route_id='$route_id', departure_date='$departure_date',
                      departure_time='$departure_time', available_seats='$available_seats'
                      WHERE schedule_id='$schedule_id'";
        $conn->query($updatesql);

        $logsql = "INSERT INTO ccm_tbl_logs (user_id, action, datetime) VALUES ('" . $_SESSION['id'] . "', 'Edited schedule ID: $schedule_id', NOW())";
        $conn->query($logsql);
        echo "<script>Swal.fire({ icon: 'success', title: 'Schedule Updated!', showConfirmButton: false, timer: 1500 });</script>";
    }
    //delete sched
    if(isset($_GET['delete'])) {
        $schedule_id = $_GET['delete'];
        $conn->query("DELETE FROM ccm_tbl_schedule WHERE schedule_id='$schedule_id'");
        $logsql = "INSERT INTO ccm_tbl_logs (user_id, action, datetime) VALUES ('" . $_SESSION['id'] . "', 'Deleted schedule ID: $schedule_id', NOW())";
        $conn->query($logsql);
        echo "<script>Swal.fire({ icon: 'success', title: 'Schedule Deleted!', showConfirmButton: false, timer: 1500 });</script>";
    }
    $displaysql = "SELECT s.schedule_id, s.bus_id, s.route_id, s.departure_date, s.departure_time, s.available_seats,
                          b.bus_number, b.driver_name,
                          ro.route_name, ro.departure_location, ro.destination
                   FROM ccm_tbl_schedule s
                   JOIN ccm_tbl_bus b   ON s.bus_id = b.bus_id
                   JOIN ccm_tbl_route ro ON s.route_id = ro.route_id
                   ORDER BY s.departure_date DESC";
    $result = $conn->query($displaysql);

    $buses  = $conn->query("SELECT * FROM ccm_tbl_bus ORDER BY bus_number ASC");
    $routes = $conn->query("SELECT * FROM ccm_tbl_route ORDER BY route_name ASC");
    $editBuses  = $conn->query("SELECT * FROM ccm_tbl_bus ORDER BY bus_number ASC");
    $editRoutes = $conn->query("SELECT * FROM ccm_tbl_route ORDER BY route_name ASC");
?>
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Bus</label>
                        <select name="bus_id" class="form-select" required>
                            <option value="">Select a bus</option>
                            <?php foreach($buses as $bus): ?>
                            <option value="<?php echo $bus['bus_id']; ?>">
                                <?php echo $bus['bus_number']; ?> (Driver: <?php echo $bus['driver_name']; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Route</label>
                        <select name="route_id" class="form-select" required>
                            <option value="">Select a route</option>
                            <?php foreach($routes as $route): ?>
                            <option value="<?php echo $route['route_id']; ?>">
                                <?php echo $route['route_name']; ?> (<?php echo $route['departure_location']; ?> → <?php echo $route['destination']; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Departure Date</label>
                        <input type="date" name="departure_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Departure Time</label>
                        <input type="time" name="departure_time" class="form-control" required>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Available Seats</label>
                        <input type="number" name="available_seats" class="form-control" required min="1">
                        <div class="invalid-feedback">Required.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="btnadd" class="btn btn-success">Add Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="schedule_id" id="edit_schedule_id">
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Bus</label>
                        <select name="bus_id" id="edit_bus_id" class="form-select" required>
                            <?php foreach($editBuses as $bus): ?>
                            <option value="<?php echo $bus['bus_id']; ?>">
                                <?php echo $bus['bus_number']; ?> (Driver: <?php echo $bus['driver_name']; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Route</label>
                        <select name="route_id" id="edit_route_id" class="form-select" required>
                            <?php foreach($editRoutes as $route): ?>
                            <option value="<?php echo $route['route_id']; ?>">
                                <?php echo $route['route_name']; ?> (<?php echo $route['departure_location']; ?> → <?php echo $route['destination']; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Departure Date</label>
                        <input type="date" name="departure_date" id="edit_departure_date" class="form-control" required>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Departure Time</label>
                        <input type="time" name="departure_time" id="edit_departure_time" class="form-control" required>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Available Seats</label>
                        <input type="number" name="available_seats" id="edit_available_seats" class="form-control" required min="0">
                        <div class="invalid-feedback">Required.</div>
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
    <h5 class="mb-0"><i class="bi bi-calendar-week me-2 text-success"></i>Schedule List</h5>
    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-lg me-1"></i> Add Schedule
    </button>
</div>

<div class="mb-3">
    <input type="text" id="searchInput" class="form-control" placeholder="Search schedules...">
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="schedTable">
                <thead class="table-success">
                    <tr>
                        <th>Bus</th>
                        <th>Driver</th>
                        <th>Route</th>
                        <th>From → To</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Available Seats</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if($result->num_rows > 0) {
                        foreach($result as $fieldnames) {
                            $seatBadge = $fieldnames['available_seats'] > 0 ? 'bg-success' : 'bg-danger';
                            echo "<tr>
                                <td>" . htmlspecialchars($fieldnames['bus_number']) . "</td>
                                <td>" . htmlspecialchars($fieldnames['driver_name']) . "</td>
                                <td>" . htmlspecialchars($fieldnames['route_name']) . "</td>
                                <td>" . htmlspecialchars($fieldnames['departure_location']) . " → " . htmlspecialchars($fieldnames['destination']) . "</td>
                                <td>" . $fieldnames['departure_date'] . "</td>
                                <td>" . $fieldnames['departure_time'] . "</td>
                                <td><span class='badge $seatBadge'>" . $fieldnames['available_seats'] . "</span></td>
                                <td>
                                    <button class='btn btn-warning btn-sm text-white' onclick='openEditSchedule(" . $fieldnames['schedule_id'] . ", " . $fieldnames['bus_id'] . ", " . $fieldnames['route_id'] . ", \"" . $fieldnames['departure_date'] . "\", \"" . $fieldnames['departure_time'] . "\", " . $fieldnames['available_seats'] . ")'>
                                        <i class='bi bi-pencil-square'></i>
                                    </button>
                                    <a href='?delete=" . $fieldnames['schedule_id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Delete this schedule?\")'>
                                        <i class='bi bi-trash'></i>
                                    </a>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center text-muted py-3'>No schedules found. Add one!</td></tr>";
                    }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function openEditSchedule(schedule_id, bus_id, route_id, departure_date, departure_time, available_seats) {
    document.getElementById('edit_schedule_id').value = schedule_id;
    document.getElementById('edit_bus_id').value = bus_id;
    document.getElementById('edit_route_id').value = route_id;
    document.getElementById('edit_departure_date').value = departure_date;
    document.getElementById('edit_departure_time').value = departure_time;
    document.getElementById('edit_available_seats').value = available_seats;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

document.getElementById('searchInput').addEventListener('keyup', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#schedTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
    });
});
</script>

<?php require_once 'footer.php'; ?>