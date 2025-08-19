<?php  
// Start session  
session_start();  
  
// API keys  
$openWeatherApiKey = "dfdeeea3e401405c92306186c8fc64ef";  
$openMeteoApiUrl = "https://api.open-meteo.com/v1/forecast";  
  
// Timezone  
date_default_timezone_set('Asia/Kolkata');  
  
// Database config  
require 'config.php';  
  
$weatherData = null;  
$forecastData = null;  
$hourlyData = null;
$error = "";  
$selectedDayWeather = null;  
  
// Ensure weather_log.json file exists (create if not)
$logFile = 'weather_log.json';
if (!file_exists($logFile)) {
    file_put_contents($logFile, json_encode([], JSON_PRETTY_PRINT));
}

// Handle form submit for weather check  
if (isset($_POST['check'])) {  
    $city = htmlspecialchars($_POST['city']);  
  
    // Fetch current weather  
    $url = "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$openWeatherApiKey}&units=metric";  
    $data = json_decode(file_get_contents($url));  
  
    if ($data && $data->cod == 200) {  
        $weatherData = $data;  
  
        // Save weather data to log file
        $logData = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
    
        $entry = [
            'city' => $data->name,
            'temp' => $data->main->temp,
            'desc' => $data->weather[0]->description,
            'humidity' => $data->main->humidity,
            'wind' => $data->wind->speed,
            'time' => date('Y-m-d H:i:s')
        ];
    
        $logData[] = $entry;
    
        file_put_contents($logFile, json_encode($logData, JSON_PRETTY_PRINT));
  
        // Fetch 7-day forecast and hourly forecast  
        $lat = $data->coord->lat;  
        $lon = $data->coord->lon;  
        $forecastUrl = "{$openMeteoApiUrl}?latitude={$lat}&longitude={$lon}&daily=temperature_2m_max,temperature_2m_min,precipitation_sum&hourly=temperature_2m,precipitation,windspeed_10m&timezone=Asia%2FKolkata";  
        $forecastResponse = json_decode(file_get_contents($forecastUrl), true);  
  
        if ($forecastResponse) {  
            if (isset($forecastResponse['daily'])) {
                $forecastData = $forecastResponse['daily'];
            }
            if (isset($forecastResponse['hourly'])) {
                $hourlyData = $forecastResponse['hourly'];
            }
        }  
    } else {  
        $error = "❌ City not found. Please try again.";  
    }  
}  
  
// Handle click on calendar day  
if (isset($_POST['selected_date']) && isset($_POST['forecast_json'])) {  
    $selectedDate = $_POST['selected_date'];  
    $forecastData = json_decode($_POST['forecast_json'], true);  
  
    // Find selected day's forecast  
    foreach ($forecastData['time'] as $i => $date) {  
        if ($date === $selectedDate) {  
            $selectedDayWeather = [  
                'date' => $date,  
                'max' => $forecastData['temperature_2m_max'][$i],  
                'min' => $forecastData['temperature_2m_min'][$i],  
                'rain' => $forecastData['precipitation_sum'][$i]  
            ];  
            break;  
        }  
    }  
}  
?>  
<!DOCTYPE html>  
<html>  
<head>  
    <title>Weather Project</title>  
    <style>  
        body {  
            font-family: 'Segoe UI', sans-serif;  
            background: #eef5ff;  
            margin: 0;  
            padding: 0;  
        }  
        header {  
            background: #4a90e2;  
            color: white;  
            padding: 15px;  
            text-align: center;  
        }  
        .container {  
            max-width: 950px;  
            margin: 30px auto;  
            background: white;  
            padding: 20px;  
            border-radius: 10px;  
            box-shadow: 0 0 20px rgba(0,0,0,0.1);  
        }  
        form {  
            text-align: center;  
            margin-bottom: 20px;  
        }  
        input[type=text] {  
            padding: 10px;  
            width: 60%;  
            border: 2px solid #4a90e2;  
            border-radius: 5px;  
            outline: none;  
        }  
        button {  
            padding: 10px 20px;  
            background: #4a90e2;  
            color: white;  
            border: none;  
            border-radius: 5px;  
            margin-left: 5px;  
            cursor: pointer;  
        }  
        .error {  
            text-align: center;  
            color: red;  
            margin-bottom: 10px;  
        }  
        .weather-card {  
            text-align: center;  
            padding: 15px;  
            border-radius: 10px;  
            background: #f2f8ff;  
            margin-bottom: 20px;  
        }  
        .forecast-grid {  
            display: grid;  
            grid-template-columns: repeat(auto-fit,minmax(120px,1fr));  
            gap: 10px;  
        }  
        .forecast-card {  
            background: #f9fcff;  
            border-radius: 8px;  
            padding: 10px;  
            text-align: center;  
            border: 1px solid #d6e6f5;  
        }  
        .calendar {  
            margin-top: 20px;  
            text-align: center;  
        }  
        table {  
            margin: auto;  
            border-collapse: collapse;  
        }  
        td, th {  
            border: 1px solid #ccc;  
            padding: 8px 12px;  
        }  
        th {  
            background: #4a90e2;  
            color: white;  
        }  
        .day-button {  
            background: none;  
            border: none;  
            padding: 5px;  
            cursor: pointer;  
            color: #4a90e2;  
            font-weight: bold;  
        }  
        .day-button:hover {  
            text-decoration: underline;  
        }  

        /* Navbar styling */
        header nav {
            margin-top: 10px;
        }

        header nav a {
            color: #cce4ff;
            text-decoration: none;
            padding: 10px 18px;
            margin: 0 8px;
            border-radius: 5px;
            font-weight: 600;
            transition: background-color 0.3s ease, color 0.3s ease;
            display: inline-block;
        }

        header nav a:hover,  
        header nav a:focus {
            background-color: #fff9c4;
            color: #4a90e2;
            outline: none;
        }

        header nav a:active {
            background-color: #fff59d;
            color: #fff;
        }

        header nav a:first-child {
            margin-left: 0;
        }

        header nav a:last-child {
            margin-right: 0;
        }

        /* Hourly forecast table styling */
        .hourly-forecast {
            margin-bottom: 25px;
        }
        .hourly-forecast table {
            width: 100%;
            border-collapse: collapse;
            background: #f9fcff;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .hourly-forecast th, .hourly-forecast td {
            border: 1px solid #d6e6f5;
            padding: 8px 10px;
            text-align: center;
            color: #333;
        }
        .hourly-forecast th {
            background: #4a90e2;
            color: white;
        }

        /* Print styling for PDF */
        @media print {
            header, nav, form, .print-hide { display: none !important; }
            .container { box-shadow: none; margin: 0; width: 100%; }
            body { background: #fff; }
            table { page-break-inside: avoid; }
        }
    </style>  

    <!-- Chart.js CDN -->  
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>  
</head>  
<body>  
<header>  
    <h1>🌤 Weather Project</h1>  
    <nav>  
        <a href="index.php">Home</a> |   
        <a href="history.php">View History</a> |   
        <a href="settings.php">Settings</a> | 
        <a href="admin.php">Admin</a>    </nav>  
</header>

   
  <div class="container">  
    <form method="post">  
        <input type="text" name="city" placeholder="Enter city name" required>  
        <button type="submit" name="check">Check Weather</button>  
        <?php if ($weatherData || $hourlyData): ?>
            <button type="button" class="print-hide" onclick="window.print()">Download PDF</button>
        <?php endif; ?>
    </form>  

    <?php if ($error): ?>  
        <div class="error"><?= $error ?></div>  
    <?php endif; ?>  

    <?php if ($weatherData): ?>  
        <div class="weather-card">  
            <h2><?= $weatherData->name ?>, <?= $weatherData->sys->country ?></h2>  
            <p>🌡 Temp: <?= $weatherData->main->temp ?>°C</p>  
            <p>💧 Humidity: <?= $weatherData->main->humidity ?>%</p>  
            <p>🌬 Wind: <?= $weatherData->wind->speed ?> m/s</p>  
            <p>☁ Condition: <?= ucfirst($weatherData->weather[0]->description) ?></p>  
        </div>  
    <?php endif; ?>  

    <!-- Hourly forecast displayed BEFORE calendar -->
    <?php if ($hourlyData): ?>
    <div class="hourly-forecast">
        <h3>⏰ Hourly Forecast (Next 24 hours)</h3>
        <table>
            <thead>
                <tr>
                    <th>Time (IST)</th>
                    <th>Temp (°C)</th>
                    <th>Precipitation (mm)</th>
                    <th>Wind Speed (m/s)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Show next 24 hours of data as 12-hour clock with AM/PM
                for ($i = 0; $i < 24; $i++) {
                    $t = $hourlyData['time'][$i] ?? null;
                    $temp = $hourlyData['temperature_2m'][$i] ?? null;
                    $prec = $hourlyData['precipitation'][$i] ?? null;
                    $wind = $hourlyData['windspeed_10m'][$i] ?? null;

                    if ($t === null) break;

                    // Open-Meteo se time "YYYY-MM-DDTHH:MM" aata hai. Humne API me timezone=Asia/Kolkata diya hai,
                    // isliye yeh time already IST hota hai. Yahan 12-hour AM/PM format me dikhaya ja raha hai.
                    $timeLabel = date('h:i A', strtotime($t));

                    echo "<tr>";
                    echo "<td>{$timeLabel}</td>";
                    echo "<td>{$temp}</td>";
                    echo "<td>{$prec}</td>";
                    echo "<td>{$wind}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if ($forecastData): ?>  
        <div class="calendar">  
            <h3>📆 Calendar</h3>  
            <?php  
            $month = date('m');  
            $year = date('Y');  
            $firstDay = mktime(0,0,0,$month,1,$year);  
            $daysInMonth = date('t',$firstDay);  
            $dayOfWeek = date('w',$firstDay);  
            $monthName = date('F',$firstDay);  

            echo "<table>";  
            echo "<tr><th colspan='7'>$monthName $year</th></tr>";  
            echo "<tr>  
                    <th>Sun</th><th>Mon</th><th>Tue</th>  
                    <th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>  
                  </tr><tr>";  

            if ($dayOfWeek > 0) {  
                for ($i=0; $i < $dayOfWeek; $i++) {  
                    echo "<td></td>";  
                }  
            }  

            $currentDay = 1;  
            while ($currentDay <= $daysInMonth) {  
                if ($dayOfWeek == 7) {  
                    $dayOfWeek = 0;  
                    echo "</tr><tr>";  
                }  
                $fullDate = date("Y-m") . "-" . str_pad($currentDay, 2, "0", STR_PAD_LEFT);  
                echo "<td>  
                        <form method='post' style='margin:0;'>  
                            <input type='hidden' name='selected_date' value='$fullDate'>  
                            <input type='hidden' name='forecast_json' value='" . htmlspecialchars(json_encode($forecastData), ENT_QUOTES, 'UTF-8') . "'>  
                            <button type='submit' class='day-button'>$currentDay</button>  
                        </form>  
                      </td>";  
                $currentDay++;  
                $dayOfWeek++;  
            }  
            if ($dayOfWeek != 7) {  
                $remainingDays = 7 - $dayOfWeek;  
                for ($i=0; $i<$remainingDays; $i++) {  
                    echo "<td></td>";  
                }  
            }  
            echo "</tr></table>";  
            ?>  
        </div>  
    <?php endif; ?>  

    <?php if ($selectedDayWeather): ?>  
        <div class="weather-card">  
            <h3>Weather on <?= $selectedDayWeather['date'] ?></h3>  
            <p>🌡 Max: <?= $selectedDayWeather['max'] ?>°C</p>  
            <p>🌡 Min: <?= $selectedDayWeather['min'] ?>°C</p>  
            <p>☔ Rain: <?= $selectedDayWeather['rain'] ?> mm</p>  

            <!-- Graph ke liye canvas -->  
            <canvas id="weatherChart" width="400" height="200"></canvas>  

            <script>  
                const ctx = document.getElementById('weatherChart').getContext('2d');  
                const weatherChart = new Chart(ctx, {  
                    type: 'bar',  
                    data: {  
                        labels: ['Max Temp (°C)', 'Min Temp (°C)', 'Rain (mm)'],  
                        datasets: [{  
                            label: 'Weather Data',  
                            data: [  
                                <?= json_encode($selectedDayWeather['max']) ?>,  
                                <?= json_encode($selectedDayWeather['min']) ?>,  
                                <?= json_encode($selectedDayWeather['rain']) ?>  
                            ],  
                            backgroundColor: [  
                                'rgba(255, 99, 132, 0.6)',  
                                'rgba(54, 162, 235, 0.6)',  
                                'rgba(75, 192, 192, 0.6)'  
                            ],  
                            borderColor: [  
                                'rgba(255, 99, 132, 1)',  
                                'rgba(54, 162, 235, 1)',  
                                'rgba(75, 192, 192, 1)'  
                            ],  
                            borderWidth: 1  
                        }]  
                    },  
                    options: {  
                        scales: {  
                            y: {  
                                beginAtZero: true  
                            }  
                        },  
                        plugins: {  
                            legend: { display: false }  
                        }  
                    }  
                });  
            </script>  
        </div>  
    <?php endif; ?>  

</div>  
</body>  
</html>