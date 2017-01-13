<?php

require 'vendor/autoload.php';
require 'CassandraQueries.php';
require 'Browser.php';
require 'geoip/geoiplookup.php'; 


$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);
$http = new React\Http\Server($socket);
$geo = new GeoIPLookup();

$cluster = Cassandra::cluster()->withContactPoints( implode( ',', ['10.10.10.100', '10.10.10.110'] ) );
$cluster = $cluster->withPort(9042);
$cluster = $cluster->build();
$session = $cluster->connect("tracker");

$cassandraQueries = new CassandraQueries($session);
$browserDetector = new Browser();

function parse_google_cookie($__traz){
		$data = array();
		foreach((array)preg_split('~([.|])(?=tr)~', $__traz) as $pair){
			list($key, $value) = explode('=', $pair);
			$data[$key] = $value;
		}
		return $data;
}
	
function get_trazContentJS($str){
	$arr = array();

	$arr = explode("+", $str);

	return parse_google_cookie($arr[1]);
}

function get_trazContentHeader($str){
	$arr = array();

	$arr = explode(" ", $str);

	return parse_google_cookie($arr[1]);
}

function getUniqueValuesTraa($__traa){
		$pattern = '/__traa=[0-9]+.([0-9]+)./';
		preg_match( $pattern, $__traa, $result);
		return $result[1];
}

function validateVersion($ip) {

    if(filter_var($ip, FILTER_VALIDATE_IP) == true) {
		return getIPversion($ip);
    } 
	$ip = checkMultipleIPs($ip);
	return getIPversion($ip);
}

function getIPversion($ip){
	if(strpos($ip, ":") !== false && strpos($ip, ".") === false) {
		return 6; //pure format
	} elseif(strpos($ip, ":") !== false && strpos($ip, ".") !== false){
		return 6; //dual format
	} else{
		return 4;
	}
}

function checkMultipleIPs($ip){
	if(strpos($ip, ",") !== false){
		$ip = explode(',', $ip);
		return $ip[0];
	}
	return $ip;
}

$callback = function ($request, $response) use ($cassandraQueries, $browserDetector, $geo){

    $statusCode = 200;
    $headers    = array(
        'Content-type: application/json'
    );    

    $url     = array('url' => $request->getUrl()->getPath() );
    $data    = $request->getQuery();
    $headers = $request->getHeaders();

    $all_data = array_merge( $url, $data, $headers );
	
	$ip = $all_data['X-Forwarded-For'] ?? $all_data['x-forwarded-for'];
	
	$ipVersion = validateVersion($ip);
	
	if ($ipVersion === 6) {
		$geo -> init(__DIR__ . '/geoip/GeoLiteCityv6.dat' );
		$ip = checkMultipleIPs($ip);
	} else {
		$geo -> init(__DIR__ . '/geoip/GeoIPCity.dat' );
	}
	echo $ip . "\n";
	
	$cookie = $all_data['tracc'] ?? $all_data['cookie'];
	$session = getUniqueValuesTraa($cookie);

    if($url['url'] === '/__pixel.gif'){

		$cassandraQueries->importData($all_data);
		//$cookieData = null;
        //Log if cookie wasn't sent
		if($all_data['tracc'] == null ) {
			$cookieData = get_trazContentHeaders($all_data['Cookie']);
			$timestamp = date('d/m/Y H:i:s');
			$fp = fopen('logs/cookies.log', 'a+');
			fwrite($fp, '['.$timestamp.']: ' . "\r\n" . '[tracc]: ' . $all_data['tracc'] . "\r\n" . '[header]: ' . $all_data['Cookie'] . "\r\n");
		} else {
			$cookieData = get_trazContentJS($all_data['tracc']);
		}
			
        //Live Tables
        if ($all_data["trau"][0] == 'q'){
			
			//update hits per minute
            $cassandraQueries->updateHitsPerMinute();

            //update hits per second
            $cassandraQueries->updateHitsPerSecond();
			
			//live_traffic_source table
            $cassandraQueries->updateLiveTrafficSource($cookieData, $session);
			
			//live_content table
			$cassandraQueries->updateLiveContent($session);
			
			//live_location table
            if($ipVersion === 6){
                $locationRecord = $geo->geoip_record_by_addr_v6($ip);
            } else {
                $locationRecord = $geo->geoip_record_by_addr($ip);
            }
            $cassandraQueries->updateLiveLocation($locationRecord, $session);
			
            //live_devices table
            $userAgent =  $all_data['User-Agent'] ?? $all_data['user-agent'];
            if($userAgent == null){
                $device = 'other';
            } else{
                $browserDetector->importUserAgent($userAgent);
                if($browserDetector->isMobile()){
                    $device = 'mobile';
                } else if ($browserDetector->isTablet()){
                    $device = 'tablet';
                } else {
                    $device = 'desktop';
                }
            }
            $cassandraQueries->updateLiveDevices($device, $session);
			
			$geo->geoip_close();		
        }

		//insert into generic table 
        $cassandraQueries->insertGenericDesktopTable();
    }
	
	
    // Here we stop the process
    if(isset($request->getQuery()['kill'])){
        if($request->getQuery()['kill'] === 'true'){
            $loop->stop();
        }
    }
    $response->writeHead($statusCode, $headers);
    $response->end( json_encode( $all_data )  );
};

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', "logs/reactPHP.log");

$http->on('request', $callback);
$socket->listen(99);
$loop->run();
