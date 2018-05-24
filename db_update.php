<?php
/**
 * Created by PhpStorm.
 * User: vesper
 * Date: 27/04/2018
 * Time: 08:16
 */
require_once __DIR__ . '/ip_tools.php';
function db_update (SQLite3 $db, $file) {

    $lines = file($file);
    $count = count($lines);
    if ($count < 10000) return $db = false;
    print "Processing " . $count . " records \n";
    /*
    Array
    (
        [0] => 88.198.0.39
        [1] => example.com
        [2] => http://example.com/sayt-lorem-ipsum-dolorem-something-city
        [3] => МВД
        [4] => 5051
        [5] => 2018-03-01
    )
     */
    /*
    CREATE TABLE "urls" (
       "id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
       "domain" TEXT,
       "url" TEXT,
       "timestamp" integer,
       CONSTRAINT "url" UNIQUE (url) ON CONFLICT IGNORE
    );
    */
    /*
    -- ----------------------------
    --  Table structure for ips
    -- ----------------------------
    CREATE TABLE "ips" (
         "id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
         "ip" TEXT,
         "timestamp" integer,
        CONSTRAINT "ip" UNIQUE (ip)
    );
     */

//    unlink('res/dump.db');
    //$db = new SQLite3('res/dump_test.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
//    $db = new SQLite3(':memory:', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
    $date = date(DATE_ISO8601);

    if ($db)
        $sql = "CREATE TABLE IF NOT EXISTS \"urls\" (
         \"id\" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
         \"domain\" TEXT,
         \"url\" TEXT,
         \"timestamp\" integer,
        CONSTRAINT \"url\" UNIQUE (\"url\")
    );";
    $db->exec($sql);

    $sql = "CREATE TABLE IF NOT EXISTS \"ips\" (
         \"id\" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
         \"ip_start\" INTEGER,
         \"ip_end\" INTEGER,
         \"mask\" INTEGER,
         \"timestamp\" integer,
        CONSTRAINT \"ip\" UNIQUE (ip_start, ip_end)
    );";
    $db->exec($sql);

    foreach ($lines as $num => $line) {
        $record = str_getcsv(iconv('WINDOWS-1251', 'UTF-8', $line), ';');
        if (count($record) < 2)
            continue;
        list($ip, $domain, $url, $org, $reason, $date) = $record;
        $ip = trim($ip);
        $timestamp = strtotime($date);
        $url = SQLite3::escapeString($url);
        if (!empty($domain) || !empty($url))
            if (!$db->exec("INSERT OR IGNORE INTO urls(domain, url, timestamp) VALUES ('$domain', '$url', $timestamp)"))
                error_log("{$date} - Error: DB error code {$db->lastErrorCode()} : {$db->lastErrorMsg()} \n", 3, 'err.log');
        if (strpos($ip, '|')) {
            $ips = explode('|', $ip);
            foreach ($ips as $ip) {
//                if (strpos($ip, '/')) {
//                    list($ip, $mask) = explode('/', $ip);
//                }
                $ip = trim($ip);
//                list($ip_start, $ip_end) = ip_tools($ip);
                if (empty($ip)) continue;
                $r = ip_tools($ip);

                if ($r->start_long === 0 OR $r->end_long === 0) {
                    var_dump($ip);
                    var_dump($r);
                    print("Record: $count \n");
                }
//                $ip = SQLite3::escapeString($ip);
                if (!$db->exec("INSERT OR IGNORE INTO ips(ip_start, ip_end, mask, timestamp) VALUES ($r->start_long, $r->end_long, $r->mask, $timestamp)"))
                    error_log("{$date} - Error: DB error code {$db->lastErrorCode()} : {$db->lastErrorMsg()} \n", 3, 'err.log');
            }
        } else {
//            $ip = SQLite3::escapeString($ip);
//            list($ip_start, $ip_end) = ip_tools($ip);
            $ip = trim($ip);
            if (empty($ip)) continue;
            $r = ip_tools($ip);
            if ($r->start_long === 0 OR $r->end_long === 0) {
                var_dump($ip);
                var_dump($r);
                print("Record: $count \n");
            }
            if (!$db->exec("INSERT OR IGNORE INTO ips(ip_start, ip_end, mask, timestamp) VALUES ($r->start_long, $r->end_long, $r->mask, $timestamp)"))
                error_log("{$date} - Error: DB error code {$db->lastErrorCode()} : {$db->lastErrorMsg()} \n", 3, 'err.log');
        }
        $count--;
//        print $count-- . "\n";
    }
//    $db->close();
    print "Done \n";
    return $db;
}

//unlink('res/dump.db');
//$db = new SQLite3('res/dump.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);

/*$db = new SQLite3(':memory:', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
db_update($db);
$ip = '91.108.8.0/22';
$r = ip_tools($ip);
var_dump($r);
$query = "SELECT * FROM ips
              WHERE (ip_start <= $r->start_long AND ip_end >= $r->start_long)
              OR (ip_start <= $r->start_long AND  ip_end <= $r->end_long);";
$query_result = $db->query($query)->fetchArray();
print_r($query_result);

$query = <<<SQL
SELECT * FROM ips
WHERE ip_start = 0
OR ip_end = 0;
SQL;
$query_result = $db->query($query)->fetchArray();
var_dump($query_result);

$db->close();*/