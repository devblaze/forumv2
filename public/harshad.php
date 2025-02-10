<?php
function isHarshadNumber($number)
{
    if ($number <= 0) {
        return false; // Harshad numbers are defined only for positive integers
    }

    // Calculate the sum of the digits of the number
    $sumOfDigits = array_sum(str_split($number));

    // Check if the number is divisible by the sum of its digits
    return $number % $sumOfDigits === 0;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harshad Number Checker</title>
</head>
<body>
<h1>Check if a Number is a Harshad Number</h1>
<form method="POST">
    <label for="number">Enter a positive integer:</label>
    <input type="number" id="number" name="number" required>
    <button type="submit">Check</button>
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['number'])) {
    $number = intval($_POST['number']);

    if ($number <= 0) {
        echo "<p style='color: red;'>Please enter a positive integer.</p>";
    } else {
        if (isHarshadNumber($number)) {
            echo "<p>The number <strong>$number</strong> is a Harshad number.</p>";
        } else {
            echo "<p>The number <strong>$number</strong> is not a Harshad number.</p>";
        }
    }
}
?>
</body>
</html>
