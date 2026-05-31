<?php
    $pageTitle = 'Dashboard';
    require_once 'header.php';
    require_once '../databaseconnection.php';

    $totalBuses        = $conn->query("SELECT COUNT(*) AS cnt FROM ccm_tbl_bus")->fetch_assoc()['cnt'];
    $totalRoutes       = $conn->query("SELECT COUNT(*) AS cnt FROM ccm_tbl_route")->fetch_assoc()['cnt'];
    $totalSchedules    = $conn->query("SELECT COUNT(*) AS cnt FROM ccm_tbl_schedule")->fetch_assoc()['cnt'];
    $totalReservations = $conn->query("SELECT COUNT(*) AS cnt FROM ccm_tbl_reservation")->fetch_assoc()['cnt'];
    $pendingRes        = $conn->query("SELECT COUNT(*) AS cnt FROM ccm_tbl_reservation WHERE status = 'reserved'")->fetch_assoc()['cnt'];
?>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-primary text-white rounded p-3"><i class="bi bi-bus-front fs-4"></i></div>
                <div>
                    <div class="text-muted small">Total Buses</div>
                    <div class="fs-4 fw-bold"><?php echo $totalBuses; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-success text-white rounded p-3"><i class="bi bi-signpost-split fs-4"></i></div>
                <div>
                    <div class="text-muted small">Total Routes</div>
                    <div class="fs-4 fw-bold"><?php echo $totalRoutes; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-warning text-white rounded p-3"><i class="bi bi-calendar-week fs-4"></i></div>
                <div>
                    <div class="text-muted small">Total Schedules</div>
                    <div class="fs-4 fw-bold"><?php echo $totalSchedules; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-danger text-white rounded p-3"><i class="bi bi-ticket-detailed fs-4"></i></div>
                <div>
                    <div class="text-muted small">Pending Reservations</div>
                    <div class="fs-4 fw-bold"><?php echo $pendingRes; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-clock-history me-2 text-success"></i>Recent Reservations
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-success">
                    <tr>
                        <th>Passenger</th>
                        <th>Route</th>
                        <th>Bus</th>
                        <th>Departure Date</th>
                        <th>Seat</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $recentsql = "SELECT r.passenger_name, r.seat_number, r.status,
                                         s.departure_date,
                                         ro.departure_location, ro.destination,
                                         b.bus_number
                                  FROM ccm_tbl_reservation r
                                  JOIN ccm_tbl_schedule s  ON r.schedule_id = s.schedule_id
                                  JOIN ccm_tbl_route ro    ON s.route_id = ro.route_id
                                  JOIN ccm_tbl_bus b       ON s.bus_id = b.bus_id
                                  ORDER BY r.reservation_date DESC
                                  LIMIT 10";
                    $result = $conn->query($recentsql);

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
                                <td>" . htmlspecialchars($fieldnames['departure_location']) . " → " . htmlspecialchars($fieldnames['destination']) . "</td>
                                <td>" . htmlspecialchars($fieldnames['bus_number']) . "</td>
                                <td>" . $fieldnames['departure_date'] . "</td>
                                <td>" . $fieldnames['seat_number'] . "</td>
                                <td><span class='badge $badge'>" . ucfirst($fieldnames['status']) . "</span></td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center text-muted py-3'>No reservations yet.</td></tr>";
                    }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once 'footer.php'; ?>