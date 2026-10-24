<?php
session_start();
require_once '../config_setup/database.php';

$error = '';

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter your username and password.';
    } else {
        $stmt = $conn->prepare(
            'SELECT admin_id, username, password_hash
             FROM administrators
             WHERE username = ?'
        );

        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($admin = $result->fetch_assoc()) {
            if (password_verify($password, $admin['password_hash'])) {
                session_regenerate_id(true);

                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_username'] = $admin['username'];

                header('Location: dashboard.php');
                exit;
            }
        }

        $error = 'Invalid username or password.';
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Administrator Login | IoT Security System</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex flex-col bg-gray-100">

    <!-- Header -->
    <header class="w-full bg-[#0b2b59] text-white px-4 sm:px-6 md:px-10 py-3 shadow-md">

        <div class="flex w-full items-center gap-4">

            <!-- BUK Logo at the Far Left -->
            <div class="flex shrink-0 items-center justify-center">
                <img
                    src="../image/buk logo.png"
                    alt="Bayero University Kano Logo"
                    class="h-14 w-14 object-contain sm:h-16 sm:w-16 md:h-20 md:w-20"
                >
            </div>

            <!-- Header Text -->
            <div class="min-w-0">
                <h1 class="text-lg sm:text-xl md:text-2xl font-bold leading-tight">
                    IoT Security System
                </h1>

                <p class="mt-1 text-xs sm:text-sm text-blue-100">
                    Faculty of Computing, Bayero University Kano
                </p>
            </div>

        </div>

    </header>

    <!-- Login Form -->
    <main class="flex-grow flex items-center justify-center p-6">

        <div class="bg-white w-full max-w-md rounded-xl shadow-lg p-8">

            <h2 class="text-3xl font-bold text-[#123568] mb-2">
                Administrator Login
            </h2>

            <p class="text-gray-600 mb-6">
                Log in to monitor intrusion alerts and records.
            </p>

            <?php if ($error !== ''): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded-md mb-5">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">

                <label for="username"
                       class="block text-gray-700 font-semibold mb-2">
                    Username
                </label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    required
                    autocomplete="username"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    class="w-full border border-gray-300 rounded-lg
                           px-4 py-3 mb-5 focus:outline-none
                           focus:ring-2 focus:ring-blue-700"
                >

                <label for="password"
                       class="block text-gray-700 font-semibold mb-2">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="w-full border border-gray-300 rounded-lg
                           px-4 py-3 mb-6 focus:outline-none
                           focus:ring-2 focus:ring-blue-700"
                >

                <button
                    type="submit"
                    class="w-full bg-blue-700 hover:bg-blue-800
                           text-white font-semibold py-3 rounded-lg
                           transition duration-300"
                >
                    Login
                </button>

            </form>

            <a href="../index.php"
               class="block text-center text-blue-800 hover:underline mt-6">
                Back to Home
            </a>

        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-[#0b2b59] text-white text-center px-5 py-5">

        <p class="font-semibold">
            Bayero University Kano | Faculty of Computing
        </p>

        <p class="text-sm text-blue-200 mt-2">
            IoT-Based Intrusion Detection and Security Alert System
        </p>

    </footer>

</body>
</html>
