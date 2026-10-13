<?php
$totalEarnings = 0;
$averageEarnings = 0;
$totalExpenses = 0;
$finalMessage = "";
$dailyResults = [];
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $expensesInput = trim($_POST["expenses"] ?? "");
    $expeditionsInput = trim($_POST["expeditions"] ?? "");

    if (
        $expensesInput === "" ||
        !is_numeric($expensesInput) ||
        (float) $expensesInput < 0
    ) {
        $error = "Enter valid daily expenses of zero or more.";
    } elseif ($expeditionsInput === "") {
        $error = "Enter at least one day's expedition.";
    } else {
        $dailyExpenses = (float) $expensesInput;
        $parts = preg_split('/\s+/', $expeditionsInput);
        $days = [];

        for ($i = 0; $i < count($parts); $i += 3) {
            $expedition = array_slice($parts, $i, 3);

            if (
                count($expedition) !== 3 ||
                !ctype_digit($expedition[0]) ||
                !preg_match('/^[A-Za-z]+$/', $expedition[1]) ||
                !is_numeric($expedition[2]) ||
                (float) $expedition[2] < 0
            ) {
                $error = "Invalid expedition: " . implode(" ", $expedition) .
                    ". Use: hours path price.";
                break;
            }

            $hours = (int) $expedition[0];
            $path = strtoupper($expedition[1]);
            $price = (float) $expedition[2];

            $days[] = [
                "hours" => $hours,
                "path" => $path,
                "price" => $price
            ];
        }

        if ($error === "" && count($days) === 0) {
            $error = "Enter at least one valid expedition.";
        }

        if ($error === "") {
            foreach ($days as $index => $day) {
                $hours = $day["hours"];
                $path = $day["path"];
                $price = $day["price"];
                $pathLength = strlen($path);

                $fullLoops = intdiv($hours, $pathLength);
                $remainingHours = $hours % $pathLength;

                $bottlesPerLoop = substr_count($path, "B");
                $bottlesInRemainder = substr_count(
                    substr($path, 0, $remainingHours),
                    "B"
                );

                $bottles =
                    ($fullLoops * $bottlesPerLoop)
                    + $bottlesInRemainder;

                $earnings = $bottles * $price;

                $totalEarnings += $earnings;

                $dailyResults[] = [
                    "day" => $index + 1,
                    "bottles" => $bottles,
                    "price" => $price,
                    "earnings" => $earnings
                ];
            }

            $numberOfDays = count($days);
            $totalExpenses = $dailyExpenses * $numberOfDays;
            $averageEarnings = $totalEarnings / $numberOfDays;

            if ($averageEarnings > $dailyExpenses) {
                $extraMoney = $averageEarnings - $dailyExpenses;

                $finalMessage = sprintf(
                    "Good earnings. Extra money per day: %.2f",
                    $extraMoney
                );
            } else {
                $moneyNeeded = $totalExpenses - $totalEarnings;

                $finalMessage = sprintf(
                    "Hard times. Money needed: %.2f",
                    $moneyNeeded
                );
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bottle Collector | MP Assessment</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/problem2.css">
</head>
<body>

<main class="container">
    <div class="page-heading">
        <p class="subtitle">MACHINE PROBLEM 2</p>
        <h1>Bottle Collector</h1>
        <p>Calculate Rikku's earnings and daily expenses.</p>
    </div>

    <section class="problem">
        <h2>Enter Expedition Details</h2>

        <p>
            Each line must contain hours, path, and bottle price.
            Separate the three values with spaces.
        </p>

        <form method="POST" action="problem2.php">
            <label for="expenses">Daily Expenses (₱)</label>

            <input
                type="number"
                id="expenses"
                name="expenses"
                min="0"
                step="any"
                placeholder="Example: 100"
                value="<?= htmlspecialchars(
                    $_POST["expenses"] ?? "",
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>"
                required
            >

            <label for="expeditions">Daily Expeditions</label>

            <textarea
                id="expeditions"
                name="expeditions"
                rows="6"
                placeholder="8 ABMRB 24.50&#10;5 BBACA 15.00"
                required
            ><?= htmlspecialchars(
                $_POST["expeditions"] ?? "",
                ENT_QUOTES,
                "UTF-8"
            ) ?></textarea>

            <p class="hint">
                Example: 8 ABMRB 24.50 means 8 hours,
                path ABMRB, and ₱24.50 per bottle.
            </p>

            <button type="submit">Calculate Earnings</button>
        </form>

        <?php if ($error !== ""): ?>
            <div class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <?php if (
            $_SERVER["REQUEST_METHOD"] === "POST" &&
            $error === "" &&
            count($dailyResults) > 0
        ): ?>
            <div class="result">
                <h3>Calculation Results</h3>

                <p>
                    <strong>Total Earnings:</strong>
                    ₱<?= number_format($totalEarnings, 2) ?>
                </p>

                <p>
                    <strong>Average Earnings:</strong>
                    ₱<?= number_format($averageEarnings, 2) ?>
                </p>

                <p>
                    <strong>Total Expenses:</strong>
                    ₱<?= number_format($totalExpenses, 2) ?>
                </p>

                <h3>Daily Breakdown</h3>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Bottles</th>
                                <th>Price per Bottle</th>
                                <th>Earnings</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($dailyResults as $day): ?>
                                <tr>
                                    <td><?= $day["day"] ?></td>
                                    <td><?= $day["bottles"] ?></td>
                                    <td>
                                        ₱<?= number_format($day["price"], 2) ?>
                                    </td>
                                    <td>
                                        ₱<?= number_format($day["earnings"], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <p class="final-message">
                    <?= htmlspecialchars(
                        $finalMessage,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </p>
            </div>
        <?php endif; ?>
    </section>

    <p><a href="index.php" class="text-link">&larr; Back to Home</a></p>
</main>



</body>
</html>
