<?php
shell_exec ('cd ../../../investorportal-db/; git lfs pull');

$_ENV   = array();
$handle = fopen("../../.env", "r");
if ($handle) {
	while (($line = fgets($handle)) !== false) {
		if (strpos($line, "=") !== false) {
			$var           = explode("=", $line);
			$_ENV[$var[0]] = trim($var[1]);
		}
	}
	fclose($handle);
} else {
	die('error opening .env');
}
if(isset($_GET['update'])){
	shell_exec(" yes | mysqladmin -u root -p". $_ENV['DB_PASSWORD'] ." drop ". $_ENV['DB_DATABASE']);
}

shell_exec("mysqladmin -u root -p" . $_ENV['DB_PASSWORD'] . "  create " . $_ENV['DB_DATABASE']);
shell_exec("mysql -u root -p" . $_ENV['DB_PASSWORD'] . ' ' . $_ENV['DB_DATABASE'] . "  < ../../../investorportal-db/investorportal.sql");
echo "Imported Successfully";
