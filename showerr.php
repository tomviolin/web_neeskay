<?
	// pass back any error message from the data monitoring system

	if (file_exists("/opt/neeskay/bin/error.flag")) {
		header("Content-Type: text/plain");
		readfile("/opt/neeskay/bin/error.flag");
	} else {
		header("Content-Type: text/plain");
	}
	exit(0);
?>
