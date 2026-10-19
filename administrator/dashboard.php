
<?php
session_start();

// Prevent the dashboard from being cached by the browser
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Check administrator login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once('../config_setup/database.php');

// Count all intrusion events
$totalEvents = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total FROM intrusion_events"
);

if ($result) {
    $totalEvents = (int) $result->fetch_assoc()['total'];
}

// Count new intrusion alerts
$newAlerts = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM intrusion_events
     WHERE event_status = 'New'"
);

if ($result) {
    $newAlerts = (int) $result->fetch_assoc()['total'];
}

// Monitor ESP32 status using heartbeat records
$esp32Status = "Inactive";

$result = $conn->query(
    "SELECT created_at
     FROM intrusion_events
     WHERE event_type = 'ESP32 Heartbeat'
     ORDER BY created_at DESC
     LIMIT 1"
);

if ($result && $result->num_rows > 0) {
    $heartbeat = $result->fetch_assoc()['created_at'];
    $heartbeatTime = strtotime($heartbeat);

    if (
        $heartbeatTime !== false &&
        $heartbeatTime <= time() &&
        $heartbeatTime >= time() - 60
    ) {
        $esp32Status = "Active";
    }
}

// Get the latest five intrusion events
// Do not display heartbeat records as intrusion events
$latestEvents = $conn->query(
    "SELECT event_id, event_type, event_date, event_time, event_status
     FROM intrusion_events
     WHERE event_type != 'ESP32 Heartbeat'
     ORDER BY event_id DESC
     LIMIT 5"
);

$adminName = $_SESSION['username'] ?? 'Administrator';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta http-equiv="refresh" content="15">

    <title>Administrator Dashboard | IoT Security System</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">

<!-- Header -->
<header class="bg-[#0b2b59] text-white shadow-md">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-5 py-5">

        <div>
            <h1 class="text-xl font-bold sm:text-2xl">
                IoT Security System
            </h1>

            <p class="mt-1 text-sm text-blue-100">
                Administrator Dashboard
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">

            <span class="text-sm">
                Welcome,
                <?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?>
            </span>

            <!-- Logout -->
            <a href="logout.php"
               class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                Logout
            </a>

        </div>
    </div>
</header>

<main class="mx-auto max-w-7xl space-y-8 px-5 py-8">

    <!-- Dashboard heading -->
    <section>
        <h2 class="text-2xl font-bold text-[#0b2b59]">
            Dashboard Overview
        </h2>

        <p class="mt-1 text-sm text-slate-600">
            Monitor system status and review intrusion events.
        </p>
    </section>

    <!-- Intrusion statistics -->
    <section class="grid gap-5 sm:grid-cols-2">

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm font-medium text-slate-500">
                Total Intrusion Events
            </p>

            <p class="mt-3 text-4xl font-bold text-[#0b2b59]">
                <?= $totalEvents ?>
            </p>

            <a href="logs.php"
               class="mt-4 inline-block text-sm font-semibold text-blue-700 hover:underline">
                View intrusion logs →
            </a>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm font-medium text-slate-500">
                New Intrusion Alerts
            </p>

            <p class="mt-3 text-4xl font-bold text-red-600">
                <?= $newAlerts ?>
            </p>

            <a href="alerts.php"
               class="mt-4 inline-block text-sm font-semibold text-blue-700 hover:underline">
                View alerts →
            </a>
        </div>

    </section>

    <!-- Monitor System Status -->
    <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">

        <h2 class="text-xl font-bold text-[#0b2b59]">
            Monitor System Status
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Check whether the ESP32 is communicating through Wi-Fi.
        </p>

        <div class="mt-5 flex flex-wrap items-center justify-between gap-4 rounded-lg border border-slate-200 p-5">

            <div>
                <h3 class="font-semibold text-slate-800">
                    ESP32 Wi-Fi Connection
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Connection status
                </p>
            </div>

            <?php if ($esp32Status === 'Active'): ?>

                <span class="inline-flex items-center gap-2 rounded-full bg-green-100 px-4 py-2 text-sm font-bold text-green-800">
                    <span class="h-2.5 w-2.5 rounded-full bg-green-600"></span>
                    Active
                </span>

            <?php else: ?>

                <span class="inline-flex items-center gap-2 rounded-full bg-red-100 px-4 py-2 text-sm font-bold text-red-800">
                    <span class="h-2.5 w-2.5 rounded-full bg-red-600"></span>
                    Inactive
                </span>

            <?php endif; ?>

        </div>

        <p class="mt-4 text-xs text-slate-500">
            The ESP32 must send a heartbeat within the last 60 seconds
            to be displayed as Active.
        </p>

    </section>

    <!-- Recent intrusion events -->
    <section class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-5">

            <div>
                <h2 class="text-xl font-bold text-[#0b2b59]">
                    Recent Intrusion Events
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    The five most recent recorded intrusion events.
                </p>
            </div>

            <a href="logs.php"
               class="rounded-lg bg-[#0b2b59] px-4 py-2 text-sm font-semibold text-white hover:bg-blue-900">
                View All Logs
            </a>

        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">

                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Event ID</th>
                        <th class="px-6 py-4 font-semibold">Event</th>
                        <th class="px-6 py-4 font-semibold">Date</th>
                        <th class="px-6 py-4 font-semibold">Time</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    <?php if ($latestEvents && $latestEvents->num_rows > 0): ?>

                        <?php while ($event = $latestEvents->fetch_assoc()): ?>

                            <tr class="hover:bg-slate-50">

                                <td class="px-6 py-4">
                                    <?= (int) $event['event_id'] ?>
                                </td>

                                <td class="px-6 py-4">
                                    <?= htmlspecialchars($event['event_type'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td class="px-6 py-4">
                                    <?= htmlspecialchars($event['event_date'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td class="px-6 py-4">
                                    <?= htmlspecialchars($event['event_time'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td class="px-6 py-4">
                                    <?= htmlspecialchars($event['event_status'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                No intrusion events have been recorded yet.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>
            </table>
        </div>

    </section>

</main>

<!-- Footer -->
<footer class="mt-10 bg-[#0b2b59] px-5 py-5 text-center text-sm text-blue-100">
    IoT-Based Intrusion Detection and Security Alert System
    <span class="mx-2">|</span>
    Faculty of Computing, Bayero University Kano
</footer>

</body>
</html>
