<?php
require_once('Classes/SalaryCalculator.php');

$salary_calculator = new SalaryCalculator(7.7, 40, [], "0", "week");
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>
    <pre>
        <?php
        print_r($salary_calculator->calculate());
        ?>
    </pre>
</body>
</html>