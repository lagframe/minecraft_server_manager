<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/.configure.php';
require __DIR__ . '/src/functions.php';


use xPaw\MinecraftQuery;
use xPaw\MinecraftQueryException;
use Thedudeguy\Rcon;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$timeout = 3;                       // How long to timeout.

// create a log channel
$log = new Logger('manager');

switch (LOG_LEVEL) {
	case 'DEBUG':
		$log->pushHandler(new StreamHandler(LOG_FOLDER . '/server_manager.log', Logger::DEBUG));
		break;
	case 'INFO':
		$log->pushHandler(new StreamHandler(LOG_FOLDER . '/server_manager.log', Logger::INFO));
		break;
	case 'ERROR':
		$log->pushHandler(new StreamHandler(LOG_FOLDER . '/server_manager.log', Logger::ERROR));
		break;
}

$log->info('=== Starting Script ===');

$Query = new MinecraftQuery( );
$rcon = new Rcon(HOST, RCON_PORT, RCON_PASSWORD, $timeout);

$count_players = get_player_count($Query, $log);

if ($count_players !== false) {
	$log->info('player count: ' . $count_players);

	if ($count_players < MIN_PLAYERS) {
		if ($rcon->connect()) {
			$log->debug('Rcon connected to Server');

			$minutes_left = MINUTES_TO_NEXT_CHECK;
			$shutdown = true;
			$log->info('Shutdown timer started');

			say_shutdown_warning ($rcon, $minutes_left, MIN_PLAYERS);

			while (($minutes_left > 0) && $shutdown) {
				sleep(60);
				$minutes_left = $minutes_left - 1;
				$count_players = get_player_count($Query, $log);

				if ($count_players >= MIN_PLAYERS) {
					$shutdown = false;
					break;
				} elseif ($minutes_left > 0) {
					say_shutdown_warning ($rcon, $minutes_left, MIN_PLAYERS);
				}
			}

			if ($shutdown) {
				$log->info('Shutdown timer finised - server is going down');
				say_gracefull_shutdown ($rcon);
				
				if (DEVELOPMENT != true) {
					$rcon->sendCommand("stop");
					$log->info('System shudown Timer started');
					sleep(60); // 60 seconds to stop the Server.
					if (get_player_count($Query, $log, false) == false) {
						// Write the shutdown file.
						$log->info('Writing the shutdown file');
						$file = fopen('.shutdown-server',"w");
						fwrite($file, 'shutdown');
						fclose($file);
					} else {
						$log->critical("Minecraft did not stop in Time!");
					}
				}
			} else {
				$log->info('Shutdown timer stoped - min player limit reached');
				$rcon->sendCommand("say Puh! Es sind wieder genug Spieler online. Server wird nicht herunter gefahren!");
			}

			$rcon->disconnect();
		} else {
			$log->critical('Rcon connection failed');
		}
	}
}



