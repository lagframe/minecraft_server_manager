<?php
require __DIR__ . '/../vendor/autoload.php';
use xPaw\MinecraftQueryException;

function get_player_count (&$Query, &$log, $write_log = true) {
	try {
		$Query->Connect( HOST, 25565 );
	}
	catch( MinecraftQueryException $e )
	{
		if ($write_log) {
			$log->error('Query connection failed');
			$log->error($e->getMessage());
		}
		return false;
	}
	
	try
	{	
		$server_info = $Query->GetInfo( );
	}
	catch( MinecraftQueryException $e )
	{
		if ($write_log) {
			$log->error('Query connection failed');
			$log->error($e->getMessage());
		}
		return false;
	}

	return $server_info['Players'];
}

function say_shutdown_warning (&$rcon, $minutes_left, $min_players) {
	if (($minutes_left == 15) || ($minutes_left == 10) || ($minutes_left <= 5)) {
		$rcon->sendCommand("say Nicht genung Spieler online! Wenn in " . $minutes_left . " Minute(n) nicht mindestens " . $min_players . " Spieler online " . (($min_players = 1) ? "ist" : "sind") . ", wird der Server herunter gefahren!");
	}
}

function say_gracefull_shutdown (&$rcon) {
	$seconds_left = 15;
	while ($seconds_left > 0) {
		$rcon->sendCommand("say Server wird in " . $seconds_left . " Sekunden herunter gefahren!");
		sleep(5);
		$seconds_left = $seconds_left - 5;		
	}

	$rcon->sendCommand("say Server wird jetzt herunter gefahren!");
}