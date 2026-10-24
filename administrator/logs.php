<?php
session_start();

// Prevent browser caching of the protected page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Check administrator login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Database connection
require_once('../config_setup/database.php');

$adminName = $_SESSION['username'] ?? 'Administrator';

// Retrieve intrusion logs, excluding ESP32 heartbeat records
$sql = "SELECT event_id, event_type, event_date, event_time, event_status
        FROM intrusion_events
        WHERE event_type != 'ESP32 Heartbeat'
        ORDER BY event_id DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Unable to retrieve intrusion logs.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Intrusion Logs | IoT Security System</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-100 text-slate-800 flex flex-col">

    <!-- Header -->
    <header class="bg-[#0b2b59] text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-5 flex flex-col sm:flex-row
                    sm:items-center sm:justify-between gap-4">

            <div class="flex items-center gap-4">

                <div class="flex shrink-0 items-center justify-center">
                    <img src="../image/buk logo.png"
                         alt="Bayero University Kano Logo"
                         class="h-14 w-14 object-contain sm:h-16 sm:w-16 md:h-20 md:w-20">
                </div>

                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl font-bold">
                        IoT-Based Intrusion Detection and Security Alert System
                    </h1>

                    <p class="text-sm text-slate-200 mt-1">
                        Administrator Panel
                    </p>
                </div>

            </div>

            <div class="flex items-center gap-3">
                <span class="text-sm">
                    Welcome, <?php echo htmlspecialchars($adminName); ?>
                </span>

                <a href="logout.php"
                   class="bg-red-600 hover:bg-red-700 text-white
                          px-4 py-2 rounded-lg text-sm font-semibold">
                    Logout
                </a>
            </div>

        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 py-8">

        <!-- Page Heading -->
        <div class="flex flex-col sm:flex-row sm:items-center
                    sm:justify-between gap-4 mb-6">

            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    Intrusion Logs
                </h2>

                <p class="text-slate-600 mt-1">
                    View the recorded movement detection events.
                </p>
            </div>

            <a href="dashboard.php"
               class="inline-block text-center bg-[#0b2b59]
                      hover:bg-blue-900 text-white px-5 py-2.5
                      rounded-lg font-semibold">
                Back to Dashboard
            </a>

        </div>

        <!-- Logs Table -->
        <section class="bg-white rounded-xl shadow-sm
                        border border-slate-200 overflow-hidden">

            <div class="px-6 py-5 border-b border-slate-200">
                <h3 class="text-lg font-bold text-slate-800">
                    Recorded Events
                </h3>

                <p class="text-sm text-slate-500 mt-1">
                    The newest recorded events appear first.
                </p>
            </div>

            <div class="overflow-x-auto">

                <table class="w-full text-sm text-left">

                    <thead class="bg-slate-50 text-slate-600 uppercase
                                  text-xs tracking-wider">

                        <tr>
                            <th class="px-6 py-4">Event ID</th>
                            <th class="px-6 py-4">Event</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Time</th>
                            <th class="px-6 py-4">Status</th>
                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-200">

                        <?php if (mysqli_num_rows($result) > 0): ?>

                            <?php while ($event = mysqli_fetch_assoc($result)): ?>

                                <tr class="hover:bg-slate-50">

                                    <td class="px-6 py-4 font-semibold">
                                        <?php echo (int) $event['event_id']; ?>
                                    </td>

                                    <td class="px-6 py-4">
                                        <?php
                                        echo htmlspecialchars($event['event_type']);
                                        ?>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        echo htmlspecialchars($event['event_date']);
                                        ?>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        echo htmlspecialchars($event['event_time']);
                                        ?>
                                    </td>

                                    <td class="px-6 py-4">
                                        <?php
                                        $status = $event['event_status'];

                                        if ($status === 'New') {
                                            $statusClass =
                                                'bg-red-100 text-red-700';
                                        } elseif ($status === 'Reviewed') {
                                            $statusClass =
                                                'bg-green-100 text-green-700';
                                        } else {
                                            $statusClass =
                                                'bg-slate-100 text-slate-700';
                                        }
                                        ?>

                                        <span class="inline-block px-3 py-1
                                                     rounded-full text-xs
                                                     font-semibold
                                                     <?php echo $statusClass; ?>">
                                            <?php
                                            echo htmlspecialchars($status);
                                            ?>
                                        </span>
                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="5"
                                    class="px-6 py-10 text-center text-slate-500">
                                    No intrusion logs have been recorded yet.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

    <!-- Footer -->
    <footer class="bg-[#0b2b59] text-white mt-8">
        <div class="max-w-7xl mx-auto px-4 py-4 text-center text-sm">
            &copy; <?php echo date('Y'); ?>
            IoT-Based Intrusion Detection and Security Alert System.
            All rights reserved.
        </div>
    </footer>

</body>
</html>