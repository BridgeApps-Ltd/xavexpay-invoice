<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">

<title>Log File Reader</title>
<link rel="icon" href="/static/BridgeApps-Logo-Only-300x300.png" sizes="192x192" />

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="static/log.js"></script>
<link rel="stylesheet" type="text/css" href="static/style.css">
<title>Log Tails</title>
</head>

<?php
include 'files.php';
?>

<body>
<div id='controls'>
	<input type='button' id='clear' value='Clear'></input>&nbsp;&nbsp;
	&nbsp;&nbsp;&nbsp;
	<label for='frequency'>Refresh (s): </label><input type='text' id='frequency' value='30' size='4'></input>&nbsp;&nbsp;&nbsp;
	
	<label for='filter' title='Show only lines that match this regular expression'>Filter: </label><div class='filtercontainer'><input type='text' id='filter' size='20'  title='Show only lines that match this regular expression'></input><div class='filterbutton noselect' id='filterbutton'>▾</div><div class='filterdropdown' id='filterdropdown'></div></div>&nbsp;
	
	<label for='inv' title='Show only lines that do not match this regular expression'>Inverse Filter: </label><div class='filtercontainer'><input type='text' id='inv' size='20' title='Show only lines that do not match this regular expression'></input><div class='filterbutton noselect' id='invbutton'>▾</div><div class='filterdropdown' id='invdropdown'></div></div>&nbsp;&nbsp;&nbsp;

	<label for='filenames' title='Show list of all files available'>Log File: </label>
	<div class='filenamecontainer'>
		<div>
			<input type='button' id='loadAlllogs' value='Get Logs'></input>
			<select class='filenamedropdown' id='filenamedropdown' title='Show list of all files available'>
				<?php
					$logFileForUIMap = $_SESSION['FILE_UI_MAP'];
					echo "test".$logFileForUIMap;
					foreach ($logFileForUIMap as $key => $value) {
				?>
					<option value="<?php echo $key ?>"><?php echo $value ?></option>
				<?php } ?>
			</select>
			<label for='load'>Load </label><input type='text' id='load' value='100' size='4'></input> <input type='button' id='reset' value='Go'></input>
		</div>
	</div>&nbsp;
	<label for='invert'>Dark: </label><input type='checkbox' id='invert' checked='checked'></input>&nbsp;&nbsp;&nbsp;
	<label for='format'>Format: </label><input type='checkbox' id='format' checked='checked'></input>
</div>
<div id='log_container'>
	<table id='log' cellspacing='0' cellpadding='0'></table>
</div>



</body>
</html>
