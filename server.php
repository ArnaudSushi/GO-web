<?php
// prevent the server from timing out
set_time_limit(0);

// include the web sockets server script (the server is started at the far bottom of this file)
require 'class.PHPWebSocket.php';

// when a client sends data to the server
function wsOnMessage($clientID, $message, $messageLength, $binary) {
	global $Server;
	$ip = long2ip( $Server->wsClients[$clientID][6] );

	// check if message length is 0
	if ($messageLength == 0) {
		$Server->wsClose($clientID);
		return;
	}

	//The speaker is the only person in the room. Don't let them feel lonely.
	if ( sizeof($Server->wsClients) == 1 ) {
		$obj->kind = "txt";
		$obj->content = "There isn't anyone else in the room, but I'll still listen to you? --Your Trusty Server";
		$Server->wsSend($clientID, $obj);
	}
	else {
		//Send the message to everyone but the person who said it
		echo $message;
		$obj = json_decode($message);
		var_dump($obj);
		if ($obj->kind === "txt") {
			$obj->content = "Visitor $clientID ($ip) said \"$obj->content\"";
		}
		foreach ( $Server->wsClients as $id => $client )
			if ( $id != $clientID )
				$Server->wsSend($id, json_encode($obj));
	}
}

// when a client connects
function wsOnOpen($clientID)
{
	global $Server;
	$ip = long2ip( $Server->wsClients[$clientID][6] );

	$Server->log( "$ip ($clientID) has connected." );

	$obj->kind = "gameColor";
	if ( sizeof($Server->wsClients) == 1 ) {
		$obj->content = "black";
	} else {
		$obj->content = "white";
	}
	$Server->wsSend($clientID, json_encode($obj));

	//Send a join notice to everyone but the person who joined
	foreach ( $Server->wsClients as $id => $client ) {
		if ( $id != $clientID ) {
			$obj->kind = "txt";
			$obj->content = "Visitor $clientID ($ip) has joined the room.";
			var_dump(json_encode($obj));
			$Server->wsSend($id, json_encode($obj));
		}
		$obj->kind = "gameBegin";
		if ( sizeof($Server->wsClients) == 1 ) {
			$obj->content = "false";
		} else {
			$obj->content = "true";
		}
		var_dump(json_encode($obj));
		$Server->wsSend($id, json_encode($obj));
	}
}

// when a client closes or lost connection
function wsOnClose($clientID, $status) {
	global $Server;
	$ip = long2ip( $Server->wsClients[$clientID][6] );

	$Server->log( "$ip ($clientID) has disconnected." );
	//Send a user left notice to everyone in the room
	foreach ( $Server->wsClients as $id => $client ) {
		$obj->kind = "conn";
		$obj->content = "abort";
		var_dump(json_encode($obj));
		$Server->wsSend($id, json_encode($obj));
	}
}

// start the server
$Server = new PHPWebSocket();
$Server->bind('message', 'wsOnMessage');
$Server->bind('open', 'wsOnOpen');
$Server->bind('close', 'wsOnClose');
// for other computers to connect, you will probably need to change this to your LAN IP or external IP,
// alternatively use: gethostbyaddr(gethostbyname($_SERVER['SERVER_NAME']))
$Server->wsStartServer('176.31.250.160', 9300);

?>
