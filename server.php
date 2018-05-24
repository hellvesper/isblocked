<?php
/**
 * Created by PhpStorm.
 * User: vesper
 * Date: 30/04/2018
 * Time: 18:11
 */

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/db_update.php';
require_once __DIR__ . '/ip_tools.php';

use Workerman\Worker;
use Workerman\Lib\Timer;

Worker::$eventLoopClass = '\Workerman\Events\Ev';
Worker::$stdoutFile = __DIR__ . '/stdout.log';
//Worker::$stdoutFile = '/dev/stdout';

/*
 * HTTP Worker
 */

$http_worker = new Worker("http://0.0.0.0:8081");
//$task = new Worker();
// 4 processes
$http_worker->count = 1;


$db_lock = false;

$time_interval = 12 * 60 * 60; // 12 hours

$db_fill = function () {
    global $db;
    print "Donwloading dump \n";
    $file = file_get_contents('https://raw.githubusercontent.com/zapret-info/z-i/master/dump.csv');
    print "Processing dump file \n";
    $time = time();
    $filename = "dump_$time.csv";
    if ($file)
        if (file_put_contents($filename, $file))
            db_update($db, $filename);
        else print "Error during save file \n";
    else print "Error on download file \n";
};

$http_worker->onWorkerStart = function (Worker $http_worker) use (&$db_lock, $time_interval, $db_fill) {
//    $time_interval = 60;
    print "\nWorker Started: $http_worker->id  \n";

    global $db;

    $db = new SQLite3(":memory:");
    $db_lock = true;
    $db_lock = false;
//    db_update($db);
    $db_fill();

    $timer = Timer::add($time_interval, $db_fill);
};


// Emitted when data received
$http_worker->onMessage = function(Workerman\Connection\ConnectionInterface $connection, $data) use (&$db_lock)
{
    global $db;

    \Workerman\Protocols\Http::header('Access-Control-Allow-Origin: *');

    if ($db_lock || !$db) {
        $connection->send(json_encode(['result' => 'error']));
    }

    print "URI: " . $data['server']['REQUEST_URI'] . "\n";

    list( ,$ip, $mask) = explode('/', $data['server']['REQUEST_URI']);

    if (isset($ip)) {
        $re = '/^\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3}/m';
        preg_match($re, $ip,$_ip);
        $_ip = explode(".", $_ip[0]);

        if (count($_ip) === 4)
            foreach ($_ip as $octet)
                if (!($octet >= 0 AND $octet < 256))
                    $connection->send(json_encode(['result' => 'error', 'message' => 'Wrong ip address format.' ]));

        if (isset($mask) AND ((int)$mask > 0 AND (int)$mask <= 32))
            $ip = implode('/', [$ip, $mask]);

    }


//    $ip = '91.108.8.0/22';
    print "ip: $ip \n";
    $r = ip_tools($ip);
    if ($db instanceof SQLite3) {
        $query = "SELECT * FROM ips 
              WHERE (ip_start <= $r->start_long AND ip_end >= $r->start_long) 
              OR (ip_start <= $r->end_long AND  ip_end >= $r->end_long);";
        print $query . "\n";
        $result = $db->query($query)->fetchArray();
        if ($result !== false) {

            $answer = [
                'result' => 'success',
                'is_blocked' => 'true',
                'subnet' => [
                    'start' => long2ip($result['ip_start']),
                    'end' => long2ip($result['ip_end']),
                    'mask' => long2ip($result['mask']),
//                    'wildcard' => ip2long(long2ip($wcmask)),
                ]
            ];

            $connection->send(json_encode($answer));
        } else {
            $connection->send(json_encode(['result' => 'success', 'is_blocked' => 'false']));
        }
    }
};

Worker::runAll();
