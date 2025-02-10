<?php

function find_factor_pairs($n) {
    $pairs = [];
    for ($a = 1; $a <= sqrt($n); $a++) {
        if ($n % $a == 0) {
            $b = $n / $a;
            $pairs[] = [$a, $b];
        }
    }
    return $pairs;
}

$n = 900900;
$factor_pairs = find_factor_pairs($n);

echo "<table border='1'>";
echo "<tr><th>Expression</th></tr>";
foreach ($factor_pairs as $pair) {
    echo "<tr><td>{$n} = {$pair[0]} * {$pair[1]}</td></tr>";
}
echo "</table>";
