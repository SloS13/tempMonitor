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
    
    public static function getLastHourAverage($type='json') {
        $mysqli = static::getConnection();
        $r = mysqli_query($mysqli,'select ROUND(avg(temperature),0) as avg
from  readings
where readingTime >= DATE_SUB(NOW(),INTERVAL 1 HOUR); ') or die ('Failed getting average');
        $info = mysqli_fetch_assoc($r);
        
        if ( $type=='json' ) {
            return json_encode($info['avg']);
        }else {
            return $info['avg'];
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
                
                //test if temperature is out of range
                $currentStatus = static::getStatus();
                $settings = static::getSettings('array');
                
                //get last 3 temperatures.  If all are out of range, we will say current temperature is bad
                $q = "SELECT SUM(goodTemp) numGoodTemps FROM 
                    (select 
                    IF (temperature BETWEEN {$settings['minTemp']} AND {$settings['maxTemp']},1,0) as goodTemp from readings order by readingTime DESC LIMIT 0,3) subq";
                $r = mysqli_query($mysqli,$q) or die ('Failed to get last 3 temps');
                $last3Info = mysqli_fetch_assoc($r);
                
                if ($currentStatus['ok'] && ($last3Info['numGoodTemps']!=3 )) {
                    $longStr = static::generateRandomString();
                    $description = "Temperature out of range.  Temperature is at $tempF and does not fall within range of {$settings['minTemp']} to {$settings['maxTemp']}";
                    $q = "INSERT INTO alerts(alertLongID,alertDate,alertConditionDescription) VALUES ('$longStr',NOW(),'$description')";
                    $r = mysqli_query($mysqli,$q) or die ('Failed inserting new alert');
                }
                
                //automatially confirm last alert, email people 
                if (!$currentStatus['ok'] && ($last3Info['numGoodTemps']==3 )) {
                    $q = "UPDATE alerts SET alertStatus=2, alertLog = CONCAT(alertLog,', Temperature normalized, disabling alert') WHERE alertStatus=0";
                    $r = mysqli_query($mysqli,$q) or die ('Unable to disarm automatically');
                }
                
	}
        
        //stick this in here so it runs on a schedule
        static::sendAlerts();
        
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

    public static function getStatus() {
        $mysqli = static::getConnection();
        $return = array();
        
        
        
        $q = 'SELECT * FROM alerts WHERE alertStatus=0';
        $r = mysqli_query($mysqli,$q) or die ('Failed reading alerts');
        if (mysqli_num_rows($r)==0) {
            $return['ok'] = true;
        } else {
            $return['ok'] = false;
            $info = mysqli_fetch_assoc($r);
            $return['alert'] = $info;
        }
        return $return;
    }

    
    public static function sendAlerts() {
        $mysqli = static::getConnection();
        
        $q = 'SELECT * FROM alerts WHERE alertStatus=0 AND alertNotificationsSent=0';
        $r = mysqli_query($mysqli,$q) or die ('Failed reading alerts for notification');
        
        if (mysqli_num_rows($r)) {
            while ($row = mysqli_fetch_assoc($r)) {
                $subject = 'Alert from freezer';
                $message = $row['alertConditionDescription'] . '<br><br> To disable this alert,  <a href="http://'.static::getConfigAttr('baseURL').'">click here</a>';
                static::sendMail($subject,$message);
                $q = 'UPDATE alerts SET alertNotificationsSent=1 WHERE id='.$row['id'];
                $r2 = mysqli_query($mysqli,$q) or die ('Failed setting alert as sent');
            }
        }
        
    }
    
    public static function sendMail($subject,$message) {
        $mysqli = static::getConnection();
        $fsRoot = dirname(dirname(__FILE__));
        $path = $fsRoot . '/includes/PHPMailer-master/PHPMailerAutoload.php';
        require_once($path);
       
        //add emoticon
        $subject = '!!! ' . $subject . ' !!!';
        
        $mail             = new PHPMailer();

        $body             = $message;

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
        $settings = static::getSettings('array');
        if (trim($settings['alertEmails'])!='') {
            $emailsArray = explode(',',$settings['alertEmails']);
            foreach ($emailsArray as $e) {
                $e = trim($e);
                if (filter_var($e, FILTER_VALIDATE_EMAIL)) {
                    $mail->AddAddress($e);
                }
            }
        }
        
        


        if(!$mail->Send()) {
          echo "Mailer Error: " . $mail->ErrorInfo;
        } else {
          echo "Message sent!";
        }
        
    }


 public static function settingsInterface($type='json') {
     $mysqli = static::getConnection();
     $settings = static::getSettings('array');
     
     $html = '<div id="settingsWrapper">
        <form id="settingsForm">
        <input type="hidden" name="settingsFormSubmitted" id="settingsFormSubmitted" value="1">
        <table>
            <tr>
                <td>Min Temp:</td>
                <td><input type="text" id="minTemp" name="minTemp" value="'.$settings['minTemp'].'"></td>
            </tr>
            <tr>
                <td>Max Temp:</td>
                <td><input type="text" id="maxTemp" name="maxTemp" value="'.$settings['maxTemp'].'"></td>
            </tr>
            <tr>
                <td>Notification Interval:</td>
                <td><input type="text" id="minutesBetweenNotifications" name="minutesBetweenNotifications" value="'.$settings['minutesBetweenNotifications'].'"></td>
            </tr>
            <tr>
                <td>Alert Emails:</td>
                <td><input type="text" id="alertEmails" name="alertEmails" value="'.$settings['alertEmails'].'"></td>
            </tr>
            </table>
        </form>
    </div>';
     
     if ($type=='json') {
         return json_encode($html);
     } else {
         return $html;
     }
 }
        
 public static function updateSettings($post) {
     $mysqli = static::getConnection();
     $q = "UPDATE SETTINGS SET 
            minTemp='".mysqli_real_escape_string($mysqli,$post['minTemp'])."', 
            maxTemp='".mysqli_real_escape_string($mysqli,$post['maxTemp'])."', 
            minutesBetweenNotifications='".mysqli_real_escape_string($mysqli,$post['minutesBetweenNotifications'])."', 
            alertEmails='".mysqli_real_escape_string($mysqli,$post['alertEmails'])."'";
            $r = mysqli_query($mysqli,$q) or die ('Failed to update settings ' . $q);
             




 }

}

Freezer::init($config);

