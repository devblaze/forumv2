<?php
function classifyNumber($number)
{
    if ($number <= 0) {
        return "Please enter a positive integer.";
    }

    $sumOfDivisors = 0;

    for ($i = 1; $i <= $number / 2; $i++) {
        if ($number % $i === 0) {
            $sumOfDivisors += $i;
        }
    }

    if ($sumOfDivisors == $number) {
        return "The number $number is a perfect number.";
    } elseif ($sumOfDivisors < $number) {
        return "The number $number is a deficient number.";
    } else { // $sumOfDivisors > $number
        return "The number $number is an abundant number.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Number Classification</title>
</head>
<body>
<h1>Classify a Number as Deficient, Perfect, or Abundant</h1>
<form method="POST">
    <label for="number">Enter a positive integer:</label>
    <input type="number" id="number" name="number" required>
    <button type="submit">Classify</button>
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['number'])) {
    $number = intval($_POST['number']);

    if ($number <= 0) {
        echo "<p style='color: red;'>Please enter a positive integer.</p>";
    } else {
        echo "<p>" . classifyNumber($number) . "</p>";
    }
}
?>
</body>
</html>
