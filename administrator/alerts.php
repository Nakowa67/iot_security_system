
<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$adminName = $_SESSION['username'] ?? 'Administrator';

require_once('../config_setup/database.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "success";

// Mark an alert as reviewed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit("Invalid request. Please refresh the page and try again.");
    }

    $eventId = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);

    if ($eventId) {
        $stmt = $conn->prepare(
            "UPDATE intrusion_events
             SET event_status = 'Reviewed'
             WHERE event_id = ?
             AND event_type != 'ESP32 Heartbeat'
             AND event_status = 'New'"
        );

        if ($stmt) {
            $stmt->bind_param("i", $eventId);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $message = "Alert marked as reviewed successfully.";
            } else {
                $message = "The alert could not be updated. It may already have been reviewed.";
                $messageType = "error";
            }

            $stmt->close();
        } else {
            $message = "Unable to update the alert.";
            $messageType = "error";
        }
    } else {
        $message = "Invalid alert selected.";
        $messageType = "error";
    }
}

// Retrieve intrusion events, excluding ESP32 heartbeat records
$alerts = $conn->query(
    "SELECT event_id, event_type, event_date, event_time, event_status
     FROM intrusion_events
     WHERE event_type != 'ESP32 Heartbeat'
     ORDER BY event_id DESC"
);

$loadError = $alerts ? "" : "Unable to load alerts. Please check the database connection.";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Intrusion Alerts | IoT Security System</title>

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

            <a href="logout.php"
               class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                Logout
            </a>
        </div>

    </div>
</header>

<main class="mx-auto max-w-7xl space-y-8 px-5 py-8">

    <!-- Page heading -->
    <section class="flex flex-wrap items-center justify-between gap-4">

        <div>
            <h2 class="text-2xl font-bold text-[#0b2b59]">
                Intrusion Alerts
            </h2>

            <p class="mt-1 text-sm text-slate-600">
                View and manage detected intrusion events.
            </p>
        </div>

        <a href="dashboard.php"
           class="rounded-lg bg-[#0b2b59] px-4 py-2 text-sm font-semibold text-white hover:bg-blue-900">
            &larr; Back to Dashboard
        </a>

    </section>

    <!-- Feedback message -->
    <?php if ($message !== ''): ?>
        <div class="rounded-lg p-4 text-sm font-medium
            <?= $messageType === 'success'
                ? 'bg-green-100 text-green-800'
                : 'bg-red-100 text-red-800' ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- Alerts table -->
    <section class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-5">

            <div>
                <h2 class="text-xl font-bold text-[#0b2b59]">
                    All Intrusion Alerts
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Review new alerts recorded by the security system.
                </p>
            </div>

            <span class="rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-800">
                <?= $alerts ? $alerts->num_rows : 0 ?> Records
            </span>

        </div>

        <?php if ($loadError !== ''): ?>

            <div class="p-6 text-sm text-red-700">
                <?= htmlspecialchars($loadError, ENT_QUOTES, 'UTF-8') ?>
            </div>

        <?php else: ?>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">

                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Event ID</th>
                            <th class="px-6 py-4 font-semibold">Event</th>
                            <th class="px-6 py-4 font-semibold">Date</th>
                            <th class="px-6 py-4 font-semibold">Time</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                    <?php if ($alerts->num_rows > 0): ?>

                        <?php while ($alert = $alerts->fetch_assoc()): ?>

                            <tr class="hover:bg-slate-50">

                                <td class="px-6 py-4">
                                    <?= (int) $alert['event_id'] ?>
                                </td>

                                <td class="px-6 py-4">
                                    <?= htmlspecialchars($alert['event_type'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td class="px-6 py-4">
                                    <?= htmlspecialchars($alert['event_date'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td class="px-6 py-4">
                                    <?= htmlspecialchars($alert['event_time'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td class="px-6 py-4">
                                    <?php if ($alert['event_status'] === 'New'): ?>

                                        <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-800">
                                            New
                                        </span>

                                    <?php else: ?>

                                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-800">
                                            <?= htmlspecialchars($alert['event_status'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>

                                    <?php endif; ?>
                                </td>

                                <td class="px-6 py-4">
                                    <?php if ($alert['event_status'] === 'New'): ?>

                                        <form method="POST">
                                            <input type="hidden"
                                                   name="csrf_token"
                                                   value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                                            <input type="hidden"
                                                   name="event_id"
                                                   value="<?= (int) $alert['event_id'] ?>">

                                            <button type="submit"
                                                    class="rounded-lg bg-[#0b2b59] px-3 py-2 text-xs font-semibold text-white hover:bg-blue-900">
                                                Mark Reviewed
                                            </button>
                                        </form>

                                    <?php else: ?>

                                        <span class="text-slate-500">
                                            Reviewed
                                        </span>

                                    <?php endif; ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                No intrusion alerts have been recorded yet.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>
                </table>
            </div>

        <?php endif; ?>

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
