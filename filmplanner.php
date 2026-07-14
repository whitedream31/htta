<?php
if ($_POST) {
  $filmname = $_POST['filmname'];
  $starttime = $_POST['starttime'];
  $length = $_POST['length'];
  $delay = $_POST['delay'];
  $tm = strtotime($starttime);
  $adddelay = strtotime("+{$delay} minutes", $tm);
  $addlength = strtotime("+{$length} minutes", $adddelay);
  $st = date('G:i', $addlength);
  $msg = "<p><strong>{$filmname}</strong> finishes at {$st}</p>";
} else {
  $filmname = '';
  $starttime = '12:00';
  $length = '90';
  $delay = '30';
  $msg = '';
}
?><!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <title>film planner</title>
</head>
<body lang="en">
  <form method="POST" action="">
    <div>
      <label for="filmname">Film Name</label>
      <input type="text" id="filmname" name="filmname" value="<?php echo $filmname; ?>">
    </div>
    <div>
      <label for="starttime">Start Time</label>
      <input type="time" id="starttime" name="starttime" value="<?php echo $starttime; ?>">
    </div>
    <div>
      <label for="delay">Delay</label>
      <input type="number" id="delay" name="delay" min="10" max="40" step="10" value="<?php echo $delay; ?>">
    </div>
    <div>
      <label for="length">Length</label>
      <input type="number" id="length" name="length" min="80" max="180" step="1" value="<?php echo $length; ?>">
    </div>

    <div>
      <input type="submit" id="submit" value="DO">
    </div>

    <div>
      <?php echo $msg; ?>
    </div>

  </form>
</body>
</html>