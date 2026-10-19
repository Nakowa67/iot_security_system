
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>IoT-Based Intrusion Detection and Security Alert System</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        html, body {
            margin: 0;
            padding: 0;
            min-height: 100%;
        }

        * {
            box-sizing: border-box;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col bg-gray-100">

    <!-- Header -->
    <header class="bg-[#0b2b59] text-white px-6 md:px-10 py-4">
        <div>
            <h1 class="text-xl md:text-2xl font-bold">
                IoT Security System
            </h1>

            <p class="text-sm text-blue-100 mt-1">
                Faculty of Computing, BUK
            </p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex flex-col md:flex-row">

        <!-- Left Half: Image -->
        <div class="w-full md:w-1/2 min-h-[350px] md:min-h-0">
            <img
                src="image/logo.png"
                alt="IoT Security System showing motion detection and ESP32"
                class="w-full h-full object-cover"
            >
        </div>

        <!-- Right Half: Project Information -->
        <section class="w-full md:w-1/2 bg-white flex items-center">

            <div class="w-full p-8 md:p-10 lg:p-14">

                <h2 class="text-3xl lg:text-4xl font-bold
                           text-[#123568] leading-tight mb-6">
                    IoT-Based Intrusion Detection and Security Alert System
                </h2>

                <p class="text-gray-700 text-base lg:text-lg
                          leading-8 mb-5">
                    This system detects movement in a protected area
                    using a sensor. When movement is detected, a buzzer
                    and LED provide an alert.
                </p>

                <p class="text-gray-700 text-base lg:text-lg
                          leading-8 mb-8">
                    The ESP32 sends the event through Wi-Fi to a web
                    application, where an administrator can view
                    intrusion records.
                </p>

                <!-- Correct Administrator Login Link -->
                <a
                    href="administrator/login.php"
                    class="inline-block w-full text-center
                           bg-blue-700 hover:bg-blue-800
                           text-white font-semibold text-lg
                           py-4 px-6 rounded-lg
                           transition duration-300"
                >
                    Administrator Login &rarr;
                </a>

            </div>

        </section>

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
