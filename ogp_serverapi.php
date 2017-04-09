<?php
 
/*
 * Mostly copied from the OGP API
 * https://github.com/OpenGamePanel/OGP-Website/blob/master/ogp_api.php
 */
 
// Report all PHP errors
error_reporting(E_ERROR);

//Hardcoded API is a no no i know :/

define('APIKEY', '14a9f8c6f825091c7ca23da3bce1dfd8'); // <= CHANGE THIS !!

// Path definitions
define('IMAGES', 'images/');
define('INCLUDES', 'includes/');
define('MODULES', 'modules/');

define('CONFIG_FILE','includes/config.inc.php');

require_once 'includes/functions.php';
require_once 'includes/helpers.php';
require_once 'includes/lib_remote.php';

// Start the session valid for opengamepanel_web only
startSession();

require_once CONFIG_FILE;
// Connect to the database server and select database.
$db = createDatabaseConnection($db_type, $db_host, $db_user, $db_pass, $db_name, $table_prefix);
$settings = $db->getSettings();
@$GLOBALS['panel_language'] = $settings['panel_language'];

if ($_REQUEST['apikey'] != APIKEY && $_GET['apikey'] != APIKEY){
	echo 'Missing or Wrong APIKEY';
	exit();
}

$servers_raw = $db->getIpPorts();
$servers = array();

foreach( $servers_raw as $server_raw ){

	$server = array(
		'server_id' => $server_raw['home_id'],
		'server_name' => substr($server_raw['home_path'],strrpos($server_raw['home_path'],'/')+1),
		'name' => $server_raw['home_name'],
		'game' => $server_raw['mod_key'],
		'ip' => $server_raw['ip'],
		'port' => $server_raw['port'],
		'path' => $server_raw['home_path'],
		'remote_server_id' => $server_raw['remote_server_id'],
		'remote_server_name' => $server_raw['remote_server_name']
	);

	$remote = new OGPRemoteLibrary( $server_raw['agent_ip'], $server_raw['agent_port'],
									$server_raw['encryption_key'], $server_raw['timeout'] );
	
	if( $remote->is_screen_running(OGP_SCREEN_TYPE_HOME,$server_raw['home_id']) === 1 ) {
		$server['running'] = 'true';
	}else{
		$server['running'] = 'false';
	}
	
	$servers[] = $server;
}

header('Content-Type: application/json');
echo json_encode($servers);
exit();


?>