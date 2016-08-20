<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/_private/classes.php';

error_reporting(E_ALL);

//handle posts
if (isset($_POST['loadSettings'])) {
    echo Freezer::getSettings();
    exit;
}

if (isset($_POST['loadTemps'])) { //not written yet, for charts?
    echo Freezer::getTemps();
    exit;
}

//reads from sensors via python
if (isset($_REQUEST['storeTemps'])) {
    echo Freezer::storeTemps();
    exit;
}

if (isset($_REQUEST['loadLastTemp'])) {
    echo Freezer::getLastTemp();
    exit;
}


if (isset($_REQUEST['getLiveTemp'])) {
    $return['temperature'] =  Freezer::storeTemps(false);
    $return['minutesSince'] = time();
    echo json_encode($return, JSON_NUMERIC_CHECK);
    exit;
}

if (isset($_REQUEST['getLongHistory'])) {
    echo json_encode(Freezer::longHistory());
    exit;
}

if (isset($_REQUEST['testEmail'])) {
    Freezer::sendMail('Here is subject','Here is message');
    exit;
}

if (isset($_REQUEST['getStatus'])) {
    $status = Freezer::getStatus();
    echo json_encode($status);
    exit;
}

?>

<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">

  <title>Maloney Freezer</title>
  <meta name="description" content="Maloney Freezer">
  <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="   crossorigin="anonymous"></script>
  
  <!-- Bootstrap -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">
  
  <link href='https://fonts.googleapis.com/css?family=Orbitron:400,500,700,900' rel='stylesheet' type='text/css'>

  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/canvasjs/1.7.0/jquery.canvasjs.min.js"></script>
  
  
  <script type="text/javascript">
  WebFontConfig = {
    google: { families: [ 'Orbitron:400,500,700,900:latin' ] }
  };
  (function() {
    var wf = document.createElement('script');
    wf.src = 'https://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
    wf.type = 'text/javascript';
    wf.async = 'true';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(wf, s);
  })(); </script>

  
  <style>
      body {
         font-family: 'Orbitron', sans-serif;
         background-color:#333333;
         color:#009999;
      }
      
      #tempReadout {
        display:inline-block;
        padding:15px;
        margin:15px auto;
        background-color:#990000;
        font-weight:bold;
        font-size:6em;
        border-radius: 12px;
      }
      
      #tempReadout.ok {
          background-color:#1dc116;
      }
      
      
        #lastTempTime {
            font-size:0.5em;
        }

  </style>
  
  <?php
    //get data for long history
   $longHistory = Freezer::longHistory();
  ?>

  <script type="text/javascript">
window.onload = function () {

//Better to construct options first and then pass it as a parameter
	var histOptions = {
		title: {
			text: "Temperature History"
		},
axisX: {
labelAngle: 90
},
                animationEnabled: true,
		axisY: {
       			 title: "Temp F",
                         minimum: <?php echo $longHistory['min']-10;?>,
                         maximum: <?php echo $longHistory['max']+10;?>
		     },
		data: [
		{
			type: "column", //change it to line, area, bar, pie, etc
			dataPoints: <?php echo json_encode($longHistory['temps'],JSON_NUMERIC_CHECK);?>
		}
		]
	};

	$("#chartContainer").CanvasJSChart(histOptions);

}
</script>


<!--sweet alert-->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/sweetalert2/4.1.8/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/sweetalert2/4.1.8/sweetalert2.min.js"></script>



  <script src="/js/script.js"></script>
  
</head>

<body>
   
    
    
    
    <div id="settingsWrapper">
        <form id="settingsForm">
            Min Temp:<input type="text" id="minTemp" name="minTemp" value="Loading..."><br>
            Max Temp:<input type="text" id="maxTemp" name="maxTemp" value="Loading..."><br>
            minutesBetweenNotifications:<input type="text" id="minutesBetweenNotifications" name="minutesBetweenNotifications" value="Loading..."><br>
            Alert Emails:<input type="text" id="alertEmails" name="alertEmails" value="Loading..."><br>
        </form>
    </div>
    <input type="button" value="Load Settings" id="loadSettingsButton"><br>
    <input type="button" value="Load Last Temp" id="loadLastTempButton" style="display:none;">
 <input type="button" value="Load Live Temp" onclick="loadLiveTemp();" style="display:none;">
 <input type="button" value="Get Status" onclick="getOverallStatus();" style="">
 <input type="button" value="Disable Alert" onclick="dialog_disableAlert();" style="">
 <input type="button" value="Settings" onclick="dialog_settings();" style="">
 <input type="button" value="Live Feed Off" onclick="toggleInterval();" style="" id="liveFeedButton">
 <input type="button" value="Live Chart" onclick="liveChart();" style="" id="liveChartButton">
 
<p style="clear:both;"></p>
 <div id="liveChartContainer" style="height:500px;"></div>
 <p style="clear:both;"></p>
 
    <div id="tempReadout">
        <span id="lastTempVal">loading..</span> <sup>o</sup>F
    </div>

    <div id="chartContainer" style="height: 300px; width: 80%;"></div>
    
    
    
</body>
</html>

