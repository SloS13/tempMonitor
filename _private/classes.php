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
            $tempRaw = trim(substr($output,strpos($output,'t=')+2));
            $tempC = $tempRaw / 1000;
            $tempF = ($tempC * 9 / 5) + 32;
        } else {
            //handle error, cannot
            $tempF = 0;
        }
	$tempF = number_format (ROUND($tempF,2),2);

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


	public static function longHistory($hours=48) {
		$mysqli = static::getConnection();
		$histArray = array();
                $min = 100000;
                $max = -100000;
                
		$q = "SELECT * FROM (
		select *,
		TIMESTAMPDIFF(MINUTE,readingTime,NOW()) minutesSince,
		HOUR(readingTime) as timeHour,
		MINUTE(readingTime) as timeMinute,
		CONCAT(DAY(readingTime),HOUR(readingTime)) as timeUnique
		 from readings) subq
		WHERE minutesSince<(60*48)
		GROUP BY timeUnique
		ORDER BY minutesSince DESC";

		$r = mysqli_query($mysqli,$q) or die ('Failed reading last temperature');

		while ($row = mysqli_fetch_assoc($r)) {
                  if ($row['temperature'] < $min){$min = $row['temperature'];}
                  if ($row['temperature'] > $max){$max = $row['temperature'];}
		  $new = array('y' => $row['temperature'],'label' => date('D ga',strtotime($row['readingTime'])));
		  $histArray[] = $new;
		}
                
                $return['min'] = $min;
                $return['max'] = $max;
                $return['temps'] = $histArray;
                
		return $return;
	}



    public static function sendMail($subject,$message) {
        $mysqli = static::getConnection();
        $fsRoot = dirname(dirname(__FILE__));
        $path = $fsRoot . '/includes/PHPMailer-master/PHPMailerAutoload.php';
        require_once($path);
       
        $mail             = new PHPMailer();

        $body             = 'This is a test';

        $mail->IsSMTP(); // telling the class to use SMTP
        $mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
                                                   // 1 = errors and messages
                                                   // 2 = messages only
        $mail->SMTPAuth   = true;                  // enable SMTP authentication
        $mail->SMTPSecure = "tls";                 // sets the prefix to the servier
        $mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
        $mail->Port       = 587;                   // set the SMTP port for the GMAIL server
        $mail->Username   = static::getConfigAttr('gmailUser');  // GMAIL username
        $mail->Password   = static::getConfigAttr('gmailPassword');            // GMAIL password

        $mail->SetFrom(static::getConfigAttr('gmailUser'), static::getConfigAttr('gmailName'));

        #$mail->AddReplyTo("name@yourdomain.com","First Last");

        $mail->Subject= $subject;

        

        $mail->MsgHTML($body);

        //DO STUFF HERE TO SEND MAIL TO DIFFERENT ADDRESSES BASED ON WHATS SAVED IN THE DATABASE
        $address = "ka24det@gmail.com";
        $mail->AddAddress($address);


        if(!$mail->Send()) {
          echo "Mailer Error: " . $mail->ErrorInfo;
        } else {
          echo "Message sent!";
        }
        
    }




}

Freezer::init($config);

