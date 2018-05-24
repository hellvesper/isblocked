<?php

/**
 * @param $ip
 * @return object
 */
function ip_tools($ip) {

    if (!strpos($ip, '/'))
        $ip .= '/32';

    list($ip, $mask) = explode('/', $ip);

    $mask = long2ip(-1 << (32 - (int)$mask)); //CIDR prefix 2 mask

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


    $result = new stdClass();
    $result->start_long = $start_long;
    $result->start_ip = $start_ip;
    $result->end_long = $end_long;
    $result->end_ip = $end_ip;
    $result->mask = $mask;
    $result->wcmask = $wcmask;

    return $result;
}
