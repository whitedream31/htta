<html>
<?php
require 'class.calendar.php';
?>
<head>
</head>
<body>
<?php
$cal = new calendar(3, 2017);
$cal->AddEvent('2017-03-04', 'test1');
$cal->AddEvent('2017-03-05', 'test3');
$cal->AddEvent('2017-03-14', 'test2');
$cal->AddEvent('2017-03-24', 'test4');
$cal->ShowCalendar(false);
?>
</body>
</html>