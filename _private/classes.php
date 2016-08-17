<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/_private/config.php';

class Freezer {
    private static $mysqli;
    private static $config;

    public static function init($config) {
        static::$config = $config;
    }

    private static function _dbConnect() {
            $port = 3306;
            
        
            static::$mysqli = new mysqli(static::$config['dbHost'], static::$config['dbUser'],
                                         static::$config['dbPassword'], static::$config['dbName'],$port);
            static::$mysqli->set_charset("utf8");
            
            if (static::$mysqli->connect_errno) {
                die("Failed to connect to MySQL: (" . static::$mysqli->$connect_errno . ") " . static::$mysqli->connect_error) ;
            }
    }
    

    public static function getConnection() {
        if (!isset(static::$mysqli)) {
            static::_dbConnect();
        }
        return Freezer::$mysqli;
    }

    public static function getConfigAttr($attr) {
        if (isset(static::$config[$attr])) {
            return static::$config[$attr];
        }
        return null;
    }

    public static function logIt($logType,$logText) {
        $mysqli = static::getConnection();
        
        $userID = $_SESSION['userID'];
        if ($userID < 1) {$userID=0;}
        
        $stmt = $mysqli->prepare("INSERT INTO tbllog(logUserID,logType,logText) VALUES (?,?,?)");
        $stmt->bind_param( "iss", $userID, $logType, $logText );
        $stmt->execute();
        
        $return['stmt'] = $stmt;
        return $return;
    }
    
   

    public static function generateRandomString($length = 25) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public static function getSettings($type='json') {
        $mysqli = static::getConnection();
        $r = mysqli_query($mysqli,'SELECT * FROM settings') or die ('Failed to load settings');
        $info = mysqli_fetch_assoc($r);
        
        if ( $type=='json' ) {
            return json_encode($info);
        }else {
            return $info;
        }
    }
    
    //save temp(s) to database.  This can also be used to just get current temperature and not save
    public static function storeTemps($saveToDB = true) {
        $mysqli = static::getConnection();
        $output = shell_exec('/var/www/html/python/./getTemp');

        if ( substr_count($output,'YES') ) {
            $tempRaw = substr($output, -6);
            $tempC = $tempRaw / 1000;
            $tempF = ($tempC * 9 / 5) + 32;
        } else {
            //handle error, cannot
            $tempF = 0;
        }
	$tempF = ROUND($tempF,2);

        if ($saveToDB) {
	        $q = "INSERT INTO readings(sensorNumber,temperature,readingTime) VALUES (1,'{$tempF}',NOW())";
        	$r = mysqli_query($mysqli,$q) or die ('Failed inserting temperature');
	}
	return $tempF;
    }
    
    //get temp(s) from database
    public static function getLastTemp($type='json') {
        $mysqli = static::getConnection();
        
        $q = "select *,
CONCAT(TIMESTAMPDIFF(MINUTE,readingTime,NOW()),' minutes ago') as minutesSince from readings order by id DESC LIMIT 0,1";
        $r = mysqli_query($mysqli,$q) or die ('Failed reading last temperature');
        $info = mysqli_fetch_assoc($r);
        
        if ( $type=='json' ) {
            return json_encode($info);
        }else {
            return $info;
        }
    }

}

Freezer::init($config);

