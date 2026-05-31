<?php
    session_start();
    if(!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
        echo "<script>window.location.href = '../index.php';</script>";
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BusGo Admin - <?php echo $pageTitle ?? 'Dashboard'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        #sidebar { min-height: 100vh; }
        .nav-link.active { background-color: rgba(255,255,255,0.15); border-radius: 6px; }
        .nav-link:hover  { background-color: rgba(255,255,255,0.1);  border-radius: 6px; }
    </style>
</head>
<body>
<div class="d-flex">
    <div id="sidebar" class="bg-primary text-white p-3" style="width: 240px; min-width: 240px;">
        <div class="text-center mb-4">
            <div class="fw-bold fs-4">BusGo</div>
            <small class="text-white-50">Admin Panel</small>
        </div>
        <hr class="border-white-50">
        <ul class="nav flex-column gap-1">
            <li class="nav-item">
                <a class="nav-link text-white <?php echo ($pageTitle=='Dashboard') ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo ($pageTitle=='Buses') ? 'active' : ''; ?>" href="buses.php">
                    <i class="bi bi-bus-front me-2"></i> Buses
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo ($pageTitle=='Routes') ? 'active' : ''; ?>" href="routes.php">
                    <i class="bi bi-signpost-split me-2"></i> Routes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo ($pageTitle=='Schedules') ? 'active' : ''; ?>" href="schedules.php">
                    <i class="bi bi-calendar-week me-2"></i> Schedules
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo ($pageTitle=='Reservations') ? 'active' : ''; ?>" href="reservations.php">
                    <i class="bi bi-ticket-detailed me-2"></i> Reservations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo ($pageTitle=='Users') ? 'active' : ''; ?>" href="users.php">
                    <i class="bi bi-people me-2"></i> Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo ($pageTitle=='Logs') ? 'active' : ''; ?>" href="logs.php">
                    <i class="bi bi-journal-text me-2"></i> Logs
                </a>
            </li>
        </ul>
        <hr class="border-white-50 mt-4">
        <a href="logout.php" class="nav-link text-white">
            <i class="bi bi-box-arrow-left me-2"></i> Logout
        </a>
    </div>
    <div class="flex-grow-1">
        <nav class="navbar navbar-light bg-white border-bottom px-4 py-2">
            <span class="navbar-brand fw-semibold mb-0"><?php echo $pageTitle ?? 'Dashboard'; ?></span>
            <span class="text-muted small">
                <i class="bi bi-person-circle me-1"></i>
                <?php echo htmlspecialchars($_SESSION['fullname']); ?>
                <span class="badge bg-primary ms-1"><?php echo ucfirst($_SESSION['role']); ?></span>
            </span>
        </nav>
        <div class="p-4">