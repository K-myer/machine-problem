<?php
$result = [];
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = trim($_POST["numbers"] ?? "");

    if ($input === "") {
        $error = "Please enter at least one number.";
    } else {
        $parts = explode(",", $input);
        $numbers = [];

        foreach ($parts as $part) {
            $part = trim($part);

            if ($part === "" || !is_numeric($part)) {
                $error = "Enter valid numbers separated by commas.";
                break;
            }

            $numbers[] = (float) $part;
        }

        if ($error === "") {
            $sum = 0;
            $count = count($numbers);

            $result = array_fill(0, $count + 1, 0);

            for ($i = $count - 1; $i >= 0; $i--) {
                $sum += $numbers[$i];
                $result[$i] = $sum;
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
    <title>Adding Parts | MP Assessment</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/problem1.css">
</head>
<body>

<main class="container">
    <div class="page-heading">
        <p class="subtitle">MACHINE PROBLEM 1</p>
        <h1>Adding Parts</h1>
        <p>Calculate the sum of every possible suffix of an array.</p>
    </div>

    <section class="problem">
        <h2>Enter Your Array</h2>

        <form method="POST" action="problem1.php">
            <label for="numbers">Numbers (separated by commas)</label>

            <input
                type="text"
                id="numbers"
                name="numbers"
                placeholder="Example: 1, 2, 3, 4, 5, 6"
                value="<?= htmlspecialchars($_POST["numbers"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                required
            >

            <button type="submit">Calculate Sums</button>
        </form>

        <?php if ($error !== ""): ?>
            <div class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <?php if ($_SERVER["REQUEST_METHOD"] === "POST" && $error === ""): ?>
            <div class="result">
                <h3>Result</h3>

                <p>
                    <strong>Suffix sums:</strong>
                    <output>
                        <?= htmlspecialchars(
                            json_encode($result),
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>
                    </output>
                </p>
            </div>
        <?php endif; ?>
    </section>

    <p><a href="index.php" class="text-link">&larr; Back to Home</a></p>
</main>


</body>
</html>
