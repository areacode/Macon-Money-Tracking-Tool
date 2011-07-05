<?php require_once("config.inc"); ?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Reports</title>
		<?php include("includes/css.inc")?>
	</head>
	<body>
		<?php include("includes/header.inc")?>
		<h1>Reports</h1>
		<ul>
			<li>Bonds</li>
			<ul>
				<li><a href="reports_bonds.php">Redemption / Distribution</a></li>
				<li><a href="reports_tos.php">TOS Search</a></li>
				<li><a href="reports_rates.php">Bond Redemption Rates</a></li>
			</ul>
			<li>Bills</li>
			<ul>
				<li><a href="reports_bills.php">Distribution / Status</a></li>
			</ul>
			<li>Businesses</li>
			<ul>
				<li><a href="reports_businesses.php">Redemption / Checks</a></li>
			</ul>
		</ul>
	</body>
</html>