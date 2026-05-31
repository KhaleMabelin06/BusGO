<?php
session_start();
if(!isset($_SESSION['id']) || $_SESSION['role'] != 'customer') {
    echo "<script>window.location.href = '../index.php';</script>";
    exit();
}
require_once '../databaseconnection.php';
$uid = $_SESSION['id'];
$fullname = $_SESSION['fullname'];
//make a reservation
if(isset($_POST['btnbook'])) {
    $schedule_id = $_POST['schedule_id'];
    $passenger_name = $fullname;
    $contact_information = $_POST['contact_information'];
    $seat_number = $_POST['seat_number'];

    //checks if schedule still has seats
    $sched = $conn->query("SELECT available_seats FROM ccm_tbl_schedule WHERE schedule_id='$schedule_id'")->fetch_assoc();

    //checks if seat is already taken for this schedule
    $checksql = "SELECT reservation_id FROM ccm_tbl_reservation WHERE schedule_id='$schedule_id' AND seat_number='$seat_number' AND status != 'canceled'";
    $checkresult = $conn->query($checksql);

    if(!$sched || $sched['available_seats'] <= 0) {
        echo "<script>document.addEventListener('DOMContentLoaded', function(){ Swal.fire({ icon: 'error', title: 'No Seats Available', text: 'This schedule is fully booked.' }); });</script>";
    } else if($checkresult->num_rows > 0) {
        echo "<script>document.addEventListener('DOMContentLoaded', function(){ Swal.fire({ icon: 'warning', title: 'Seat Taken', text: 'That seat is already reserved for this schedule.' }); });</script>";
    } else {
        //customer reservations include user_id so My Reservations can show only this customer
        $insertsql = "INSERT INTO ccm_tbl_reservation (schedule_id, user_id, passenger_name, contact_information, seat_number, status)
                      VALUES ('$schedule_id', '$uid', '$passenger_name', '$contact_information', '$seat_number', 'reserved')";
        $result = $conn->query($insertsql);

        if($result == true) {
            $conn->query("UPDATE ccm_tbl_schedule SET available_seats = available_seats - 1 WHERE schedule_id='$schedule_id'");
            $conn->query("INSERT INTO ccm_tbl_logs (user_id, action, datetime) VALUES ('$uid', 'Booked reservation for schedule ID: $schedule_id', NOW())");
            echo "<script>document.addEventListener('DOMContentLoaded', function(){ Swal.fire({ icon: 'success', title: 'Reservation Successful!', text: 'Your seat has been reserved.' }); });</script>";
        } else {
            echo "<script>document.addEventListener('DOMContentLoaded', function(){ Swal.fire({ icon: 'error', title: 'Error', text: '" . addslashes($conn->error) . "' }); });</script>";
        }
    }
}
//button function - cancels own reservation
if(isset($_GET['cancel'])) {
    $reservation_id = $_GET['cancel'];

    $getres = $conn->query("SELECT schedule_id, status FROM ccm_tbl_reservation WHERE reservation_id='$reservation_id' AND user_id='$uid'");
    if($getres->num_rows == 1) {
        $res = $getres->fetch_assoc();
        if($res['status'] != 'canceled') {
            $conn->query("UPDATE ccm_tbl_reservation SET status='canceled' WHERE reservation_id='$reservation_id' AND user_id='$uid'");
            $conn->query("UPDATE ccm_tbl_schedule SET available_seats = available_seats + 1 WHERE schedule_id='" . $res['schedule_id'] . "'");
            $conn->query("INSERT INTO ccm_tbl_logs (user_id, action, datetime) VALUES ('$uid', 'Canceled reservation ID: $reservation_id', NOW())");
            echo "<script>document.addEventListener('DOMContentLoaded', function(){ Swal.fire({ icon: 'success', title: 'Reservation Canceled', showConfirmButton: false, timer: 1500 }); });</script>";
        }
    }
}
//available schedules
$schedules = $conn->query("SELECT s.schedule_id, s.departure_date, s.departure_time, s.available_seats,
                                  b.bus_number, b.driver_name,
                                  ro.route_name, ro.departure_location, ro.destination, ro.distance, ro.duration
                           FROM ccm_tbl_schedule s
                           JOIN ccm_tbl_bus b ON s.bus_id = b.bus_id
                           JOIN ccm_tbl_route ro ON s.route_id = ro.route_id
                           WHERE s.available_seats > 0 AND s.departure_date >= CURDATE()
                           ORDER BY s.departure_date ASC, s.departure_time ASC");
//customer's reservations
$myReservations = $conn->query("SELECT r.reservation_id, r.seat_number, r.status, r.reservation_date,
                                       s.departure_date, s.departure_time,
                                       ro.departure_location, ro.destination,
                                       b.bus_number
                                FROM ccm_tbl_reservation r
                                JOIN ccm_tbl_schedule s ON r.schedule_id = s.schedule_id
                                JOIN ccm_tbl_route ro ON s.route_id = ro.route_id
                                JOIN ccm_tbl_bus b ON s.bus_id = b.bus_id
                                WHERE r.user_id = '$uid'
                                ORDER BY r.reservation_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BusGo Customer - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php"><i class="bi bi-bus-front-fill me-2"></i>BusGo</a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="text-white small"><i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($fullname); ?></span>
            <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="p-4 bg-white rounded shadow-sm mb-4">
        <h4 class="fw-bold mb-1">Welcome, <?php echo htmlspecialchars($fullname); ?>!</h4>
        <p class="text-muted mb-0">Search available bus schedules and manage your reservations.</p>
    </div>

    <div class="modal fade" id="bookModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-ticket-detailed me-2"></i>Reserve a Seat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="schedule_id" id="book_schedule_id">
                    <div class="modal-body row g-3">
                        <div class="col-12">
                            <p class="mb-1"><strong>Passenger:</strong> <?php echo htmlspecialchars($fullname); ?></p>
                            <p id="book_route_info" class="text-muted small mb-0"></p>
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
                        <button type="submit" name="btnbook" class="btn btn-primary">Confirm Reservation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <h5 class="fw-bold mb-3"><i class="bi bi-calendar-week me-2 text-primary"></i>Available Schedules</h5>
        <div class="mb-3">
            <input type="text" id="schedSearch" class="form-control" placeholder="Search by route, bus, destination, or date...">
        </div>
        <div class="row g-3" id="schedCards">
            <?php if($schedules->num_rows > 0): foreach($schedules as $row): ?>
            <div class="col-md-6 col-lg-4 sched-card" data-info="<?php echo strtolower(htmlspecialchars($row['bus_number'].' '.$row['departure_location'].' '.$row['destination'].' '.$row['departure_date'])); ?>">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-primary"><?php echo htmlspecialchars($row['bus_number']); ?></span>
                            <span class="badge <?php echo $row['available_seats'] > 5 ? 'bg-success' : 'bg-warning text-dark'; ?>"><?php echo $row['available_seats']; ?> seats left</span>
                        </div>
                        <h6 class="fw-bold"><?php echo htmlspecialchars($row['departure_location']); ?> → <?php echo htmlspecialchars($row['destination']); ?></h6>
                        <p class="text-muted small mb-1"><i class="bi bi-calendar me-1"></i><?php echo $row['departure_date']; ?> at <?php echo $row['departure_time']; ?></p>
                        <p class="text-muted small mb-1"><i class="bi bi-person me-1"></i>Driver: <?php echo htmlspecialchars($row['driver_name']); ?></p>
                        <?php if(!empty($row['distance']) || !empty($row['duration'])): ?>
                        <p class="text-muted small mb-0"><i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($row['distance']); ?> <?php echo !empty($row['duration']) ? '&middot; ' . htmlspecialchars($row['duration']) : ''; ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <button class="btn btn-primary btn-sm w-100" onclick="openBook('<?php echo $row['schedule_id']; ?>', '<?php echo addslashes($row['departure_location']); ?>', '<?php echo addslashes($row['destination']); ?>', '<?php echo $row['departure_date']; ?>', '<?php echo $row['departure_time']; ?>')">
                            <i class="bi bi-ticket-detailed me-1"></i> Reserve a Seat
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; else: ?>
            <div class="col-12"><div class="alert alert-info">No available schedules at the moment.</div></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mb-5">
        <h5 class="fw-bold mb-3"><i class="bi bi-ticket-detailed me-2 text-primary"></i>My Reservations</h5>
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-primary">
                            <tr>
                                <th>Bus</th>
                                <th>Route</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Seat</th>
                                <th>Reserved On</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if($myReservations && $myReservations->num_rows > 0): foreach($myReservations as $row):
                            $badge = match($row['status']) {
                                'reserved' => 'bg-warning text-dark',
                                'boarded' => 'bg-success',
                                'canceled' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['bus_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['departure_location']); ?> → <?php echo htmlspecialchars($row['destination']); ?></td>
                                <td><?php echo $row['departure_date']; ?></td>
                                <td><?php echo $row['departure_time']; ?></td>
                                <td><?php echo htmlspecialchars($row['seat_number']); ?></td>
                                <td><?php echo $row['reservation_date']; ?></td>
                                <td><span class="badge <?php echo $badge; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                <td>
                                    <?php if($row['status'] == 'reserved'): ?>
                                    <a href="?cancel=<?php echo $row['reservation_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Cancel this reservation?')"><i class="bi bi-x-circle"></i></a>
                                    <?php else: ?>
                                    <span class="text-muted small">No action</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="8" class="text-center text-muted py-3">You have no reservations yet.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function openBook(id, from, to, date, time) {
    document.getElementById('book_schedule_id').value = id;
    document.getElementById('book_route_info').textContent = from + ' → ' + to + ' | ' + date + ' at ' + time;
    new bootstrap.Modal(document.getElementById('bookModal')).show();
}

document.getElementById('schedSearch').addEventListener('keyup', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('.sched-card').forEach(card => {
        card.style.display = card.dataset.info.includes(val) ? '' : 'none';
    });
});

(() => {
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>
</body>
</html>
