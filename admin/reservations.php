<?php
    $pageTitle = 'Reservations';
    require_once 'header.php';
    require_once '../databaseconnection.php';
    //add new reservation
    if(isset($_POST['btnadd'])) {
        $schedule_id         = $_POST['schedule_id'];
        $passenger_name      = $_POST['passenger_name'];
        $contact_information = $_POST['contact_information'];
        $seat_number         = $_POST['seat_number'];

        //check if schedule/s still has seats
        $schedsql = "SELECT available_seats FROM ccm_tbl_schedule WHERE schedule_id='$schedule_id'";
        $schedresult = $conn->query($schedsql);
        $sched = $schedresult->fetch_assoc();

        //check if a seat is already taken for this schedule
        $checksql = "SELECT reservation_id FROM ccm_tbl_reservation WHERE schedule_id='$schedule_id' AND seat_number='$seat_number' AND status != 'canceled'";
        $checkresult = $conn->query($checksql);

        if(!$sched || $sched['available_seats'] <= 0) {
            echo "<script>document.addEventListener('DOMContentLoaded', function(){ Swal.fire({ icon: 'error', title: 'No Seats Available', text: 'This schedule is already fully booked.' }); });</script>";
        } else if($checkresult->num_rows > 0) {
            echo "<script>document.addEventListener('DOMContentLoaded', function(){ Swal.fire({ icon: 'warning', title: 'Seat Taken', text: 'That seat is already reserved for this schedule.' }); });</script>";
        } else {
            $insertsql = "INSERT INTO ccm_tbl_reservation (schedule_id, passenger_name, contact_information, seat_number, status)
                          VALUES ('$schedule_id', '$passenger_name', '$contact_information', '$seat_number', 'reserved')";
            $result = $conn->query($insertsql);

            if($result == true) {
                //decreases available seats displayed
                $conn->query("UPDATE ccm_tbl_schedule SET available_seats = available_seats - 1 WHERE schedule_id='$schedule_id'");

                $logsql = "INSERT INTO ccm_tbl_logs (user_id, action, datetime) VALUES ('" . $_SESSION['id'] . "', 'Added reservation for: $passenger_name', NOW())";
                $conn->query($logsql);
                echo "<script>document.addEventListener('DOMContentLoaded', function(){ Swal.fire({ icon: 'success', title: 'Reservation Added!', showConfirmButton: false, timer: 1500 }); });</script>";
            }
        }
    }
    //update statuses
    if(isset($_POST['btnstatus'])) {
        $reservation_id = $_POST['reservation_id'];
        $status         = $_POST['status'];

        $oldsql = "SELECT schedule_id, status FROM ccm_tbl_reservation WHERE reservation_id='$reservation_id'";
        $oldresult = $conn->query($oldsql);
        $old = $oldresult->fetch_assoc();
        if($old) {
            $updatesql = "UPDATE ccm_tbl_reservation SET status='$status' WHERE reservation_id='$reservation_id'";
            $conn->query($updatesql);

            //reserved/boarded use one seat; canceled releases the seat back into the pool
            if($old['status'] != 'canceled' && $status == 'canceled') {
                $conn->query("UPDATE ccm_tbl_schedule SET available_seats = available_seats + 1 WHERE schedule_id='" . $old['schedule_id'] . "'");
            } else if($old['status'] == 'canceled' && $status != 'canceled') {
                $conn->query("UPDATE ccm_tbl_schedule SET available_seats = available_seats - 1 WHERE schedule_id='" . $old['schedule_id'] . "' AND available_seats > 0");
            }

            $logsql = "INSERT INTO ccm_tbl_logs (user_id, action, datetime) VALUES ('" . $_SESSION['id'] . "', 'Updated reservation ID $reservation_id to $status', NOW())";
            $conn->query($logsql);
            echo "<script>document.addEventListener('DOMContentLoaded', function(){ Swal.fire({ icon: 'success', title: 'Status Updated!', showConfirmButton: false, timer: 1500 }); });</script>";
        }
    }
    //delete function
    if(isset($_GET['delete'])) {
        $reservation_id = $_GET['delete'];
        //restore the seat only if the deleted reservation was still using a seat
        $oldsql = "SELECT schedule_id, status FROM ccm_tbl_reservation WHERE reservation_id='$reservation_id'";
        $oldresult = $conn->query($oldsql);
        $old = $oldresult->fetch_assoc();

        if($old && $old['status'] != 'canceled') {
            $conn->query("UPDATE ccm_tbl_schedule SET available_seats = available_seats + 1 WHERE schedule_id='" . $old['schedule_id'] . "'");
        }

        $conn->query("DELETE FROM ccm_tbl_reservation WHERE reservation_id='$reservation_id'");
        $logsql = "INSERT INTO ccm_tbl_logs (user_id, action, datetime) VALUES ('" . $_SESSION['id'] . "', 'Deleted reservation ID: $reservation_id', NOW())";
        $conn->query($logsql);
        echo "<script>document.addEventListener('DOMContentLoaded', function(){ Swal.fire({ icon: 'success', title: 'Reservation Deleted!', showConfirmButton: false, timer: 1500 }); });</script>";
    }

    //string query - descending order, join tables, no foreign keys displayed
    $displaysql = "SELECT r.reservation_id, r.passenger_name, r.contact_information, r.seat_number, r.status, r.reservation_date,
                          s.departure_date, s.departure_time,
                          ro.departure_location, ro.destination,
                          b.bus_number
                   FROM ccm_tbl_reservation r
                   JOIN ccm_tbl_schedule s   ON r.schedule_id = s.schedule_id
                   JOIN ccm_tbl_route ro     ON s.route_id = ro.route_id
                   JOIN ccm_tbl_bus b        ON s.bus_id = b.bus_id
                   ORDER BY r.reservation_date DESC";
    $result = $conn->query($displaysql);

    //for schedule dropdown in add modal
    $schedules = $conn->query("SELECT s.schedule_id, b.bus_number, ro.departure_location, ro.destination, s.departure_date, s.available_seats
                               FROM ccm_tbl_schedule s
                               JOIN ccm_tbl_bus b    ON s.bus_id = b.bus_id
                               JOIN ccm_tbl_route ro ON s.route_id = ro.route_id
                               WHERE s.available_seats > 0 AND s.departure_date >= CURDATE()
                               ORDER BY s.departure_date ASC");
?>
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Reservation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Schedule</label>
                        <select name="schedule_id" class="form-select" required>
                            <option value="">Select a schedule</option>
                            <?php foreach($schedules as $sched): ?>
                            <option value="<?php echo $sched['schedule_id']; ?>">
                                <?php echo $sched['bus_number']; ?> | <?php echo $sched['departure_location']; ?> → <?php echo $sched['destination']; ?> | <?php echo $sched['departure_date']; ?> (<?php echo $sched['available_seats']; ?> seats left)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Passenger Name</label>
                        <input type="text" name="passenger_name" class="form-control" required>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_information" class="form-control" required>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Seat Number</label>
                        <input type="text" name="seat_number" class="form-control" placeholder="e.g. A1" required>
                        <div class="invalid-feedback">Required.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="btnadd" class="btn btn-primary">Add Reservation</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="reservation_id" id="status_res_id">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="reserved">Reserved</option>
                        <option value="boarded">Boarded</option>
                        <option value="canceled">Canceled</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="btnstatus" class="btn btn-primary btn-sm">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="bi bi-ticket-detailed me-2 text-primary"></i>Reservation List</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-lg me-1"></i> Add Reservation
    </button>
</div>

<div class="row g-2 mb-3">
    <div class="col-md-8">
        <input type="text" id="searchInput" class="form-control" placeholder="Search reservations...">
    </div>
    <div class="col-md-4">
        <select id="statusFilter" class="form-select">
            <option value="">All Status</option>
            <option value="reserved">Reserved</option>
            <option value="boarded">Boarded</option>
            <option value="canceled">Canceled</option>
        </select>
    </div>
</div>
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="resTable">
                <thead class="table-primary">
                    <tr>
                        <th>Passenger</th>
                        <th>Contact</th>
                        <th>Bus</th>
                        <th>Route</th>
                        <th>Date</th>
                        <th>Seat</th>
                        <th>Reserved On</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if($result->num_rows > 0) {
                        foreach($result as $fieldnames) {
                            $badge = match($fieldnames['status']) {
                                'reserved' => 'bg-warning text-dark',
                                'boarded'  => 'bg-success',
                                'canceled' => 'bg-danger',
                                default    => 'bg-secondary'
                            };
                            echo "<tr>
                                <td>" . htmlspecialchars($fieldnames['passenger_name']) . "</td>
                                <td>" . htmlspecialchars($fieldnames['contact_information']) . "</td>
                                <td>" . htmlspecialchars($fieldnames['bus_number']) . "</td>
                                <td>" . htmlspecialchars($fieldnames['departure_location']) . " → " . htmlspecialchars($fieldnames['destination']) . "</td>
                                <td>" . $fieldnames['departure_date'] . "</td>
                                <td>" . $fieldnames['seat_number'] . "</td>
                                <td>" . $fieldnames['reservation_date'] . "</td>
                                <td><span class='badge $badge'>" . ucfirst($fieldnames['status']) . "</span></td>
                                <td>
                                    <button class='btn btn-info btn-sm text-white' onclick='openStatus(" . $fieldnames['reservation_id'] . ")'>
                                        <i class='bi bi-arrow-repeat'></i>
                                    </button>
                                    <a href='?delete=" . $fieldnames['reservation_id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Delete this reservation?\")'>
                                        <i class='bi bi-trash'></i>
                                    </a>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9' class='text-center text-muted py-3'>No reservations yet.</td></tr>";
                    }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function openStatus(id) {
    document.getElementById('status_res_id').value = id;
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}

document.getElementById('searchInput').addEventListener('keyup', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);

function filterTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const status = document.getElementById('statusFilter').value.toLowerCase();
    document.querySelectorAll('#resTable tbody tr').forEach(row => {
        const text     = row.innerText.toLowerCase();
        const rowStat  = row.querySelector('td:nth-child(8)')?.innerText.toLowerCase() || '';
        const matchSearch = text.includes(search);
        const matchStatus = !status || rowStat.includes(status);
        row.style.display = matchSearch && matchStatus ? '' : 'none';
    });
}
</script>

<?php require_once 'footer.php'; ?>