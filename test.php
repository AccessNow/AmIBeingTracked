<?php

require_once 'vendor/autoload.php';

use Symfony\Component\Yaml\Parser;
use GeoIp2\WebService\Client;

define('CARRIER', 0);
define('NAME', 1);
define('TWITTER_AC', 2);

session_start();
$parser = new Parser();

$yaml = $parser->parse(file_get_contents('services/config.yml'));

$ip = $_SERVER['REMOTE_ADDR'];
if ($yaml["envirement"] !== "prod") {
    if ($_GET["ip"]) {
        $ip = $_GET["ip"];
    } else {
        die("To test, please use ?ip=1.1.1.1");
    }
}


$client = new Client($yaml["geoprovider"]["user_id"], $yaml["geoprovider"]["license_key"]);

$record = $client->insights($ip);
$isp = $record->traits->autonomousSystemOrganization;
$carrier = $record->traits->organization;
$country = $record->country->isoCode;
$latitude = $record->location->latitude;
$longitude = $record->location->longitude;
$user_type = $record->traits->userType;
$confidance = $record->country->confidence;

$page_url;
$is_tracked = "-1";
$_SESSION['isp'] = $isp;
// Verify either or not the user is connected via LTE 3G or 4G
if ($user_type !== "cellular" && $user_type !== "traveler") {
    $is_tracked = "-2";
    $page_url = "./no-lte.php";
} else {
    $tracking = '';
    $suspected_tracking = "";
    $carrier_match = FALSE;

    $suspected_strings = array("tac", "fac", "snr", "sp", "imei", "imsi", "tmsi", "mmc", "mnc", "msin", "msisdn", "msrn", "lai", "lac", "lmsi");
    $tracking_header_fields = array_map('str_getcsv', file('headers.csv'));
    $tracking_header_name = array();

    foreach ($tracking_header_fields as $line) {
        array_push($tracking_header_name, $line[0]);
    }
    // Map values from the known carriers
    $csv = array_map('str_getcsv', file('carriers.csv'));
    // Get Header Regardless of the webserver used
    if (!function_exists('getallheaders')) {
        function getallheaders() {
            $headers = '';
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
            return $headers;
        }
    }
    // Look for the carrier within the carrier file
    foreach ($csv as $line) {
        if (strpos(strtolower($isp), strtolower($line[CARRIER])) !== FALSE) {
            $carrier_match = TRUE;
            break;
        }
    }
    // Look for a suspected header from all the response headers
    foreach (getallheaders() as $name => $value) {
        $header = "$name: $value\n";
        $total_header .="//" . $header;

        foreach ($suspected_strings as $str) {
            if (strpos($header, $str)) {
                $suspected_tracking .= "//" . $header;
            }
        }
        // looking for the tracking header
        if (in_array(strtolower($name), $tracking_header_name)) {
            $tracking .= " // " . $header;
            $_SESSION['header_id'] = $name;
        }
        // double direction checking 
        elseif (in_array($tracking_header_name, strtolower($name))) {
            $tracking .= " // " . $header;
            $_SESSION['header_id'] = $name;
        }
    }
    // init values (log files, redirection page)
    $_SESSION['total_header'] = $total_header;

    $site_name = $_SERVER['SERVER_NAME'];
    $log_path = "/var/log/verizon/$site_name/";

    $log_filename = $log_path . "others.log";
    $page_url = "./not-tested.php";
    // Result evaluation (decision of log files, redirection, test result)
    if ($tracking != '') {
        $log_filename = $log_path . "tracked.log";
        $page_url = "./tracked.php";
        $_SESSION['tracking'] = $tracking;
        if ($carrier_match) {
            $is_tracked = '1';
        } else {
            $is_tracked = '2';
            $file = fopen("./carriers.csv", "a+");
            fwrite($file, '"' . $isp . '","' . $carrier . '"' . "\n");
            fclose($file);
        }
    } else {
        $page_url = "./not-tracked.php";
        if ($carrier_match) {
            $log_filename = $log_path . "not_tracked.log";
            $is_tracked = '0';
        } else {
            $is_tracked = '-1';
            $log_filename = $log_path . "others.log";
            $file = fopen('./carriers.csv', "a+");
            fwrite($file, '"' . $isp . '","' . $carrier . '"' . "\n");
            fclose($file);
        }
    }
}

// Recording Data on Mysql DB and log files
$date = date('Y-m-d H:i:s');
$file = fopen($log_filename, "a+");
fwrite($file, "$isp|$date|$ip|$suspected_tracking|$total_header\n");
fclose($file);

$conn = new mysqli($yaml["database"]["host"], $yaml["database"]["user"], $yaml["database"]["password"], $yaml["database"]["database"]);
$sql = "INSERT INTO test (ip, is_tracked, tested_at, country, carrier, carrier_node, latitude, longitude)
	VALUES ('$ip','$is_tracked','$date','$country','$carrier','$isp','$latitude','$longitude')";
$conn->query($sql);
$conn->close();
header("Location: $page_url");