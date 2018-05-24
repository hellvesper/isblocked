<?php
/**
 * Created by PhpStorm.
 * User: vesper
 * Date: 01/05/2018
 * Time: 10:32
 */

/**
 * @param $ip
 * @return object
 */
function ip_tools($ip) {
//    $ip = '10.100.100.14/24';
    //$range = '10.100.100.0/20';
    if (!strpos($ip, '/'))
        $ip .= '/32';

    list($ip, $mask) = explode('/', $ip);
//    var_dump($ip, $mask);
    //list($a,$b,$c,$d) = explode('.', $ip);
    $mask = long2ip(-1 << (32 - (int)$mask)); //CIDR prefix 2 mask
//    print $mask; print "\n";
    /*
     * 10.100.100.14/20
     * 10.100.96.0 start
     * 10.100.111.255 end
     * 255.255.240.0
     * 0.0.15.255
     * 96(100) & 240 => 96 start
     * 96(100) | 15 => 111 end
     */
    $start_long = ip2long($ip) & ip2long($mask);
    $start_ip = long2ip($start_long); // start range
    $end_long = ip2long($ip) | ~ip2long($mask);
    $end_ip = long2ip($end_long); // end range
    $end_long = ip2long($end_ip); // normalize negative int
    $mask = ip2long($mask);
    $wcmask = ~ $mask; // long
    $wcmask = ip2long(long2ip($wcmask));

//    print $start_long . " => " . $start_ip . "\n";
//    print $end_long . " => " . $end_ip . "\n";

//    print ip2long($start_ip). "\n";
//    print ip2long($end_ip). "\n";
//    print "\n";
//    print substr('11110000', 0, strlen('11110000')/2);
    $result = new stdClass();
    $result->start_long = $start_long;
    $result->start_ip = $start_ip;
    $result->end_long = $end_long;
    $result->end_ip = $end_ip;
    $result->mask = $mask;
    $result->wcmask = $wcmask;
//    return [$start_long, $end_long, $start_ip, $end_ip, $mask, $wcmask];
    return $result;
}
//$ip = '91.108.8.0/22';
//$res = ip_tools($ip);
//var_dump($res);
//var_dump(long2ip($res[5]));