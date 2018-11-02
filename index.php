<?php
//var_dump($_GET);
//var_dump(is_null($_GET['p']));
//require('index.html');

if (is_null($_GET['p'])) {
	require('mainGoGui.php');
	require('mainGoGui.html');
} else {
	switch ($_GET['p']) {
		default:
			require('mainGoGui.php');
			require('mainGoGui.html');
			break;
		case 'game':
			require('PlayGo.php');
			require('PlayGo.html');
			break;
		case 'test':
			require('test.php');
			require('test.html');
			break;
		case 'chatLog':
			require('log.php');
			break;
	}
}
?>

