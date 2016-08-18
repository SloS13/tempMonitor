<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/_private/classes.php';

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
    $return['minutesSince'] = 0;
    echo json_encode($return);
    exit;
}

if (isset($_REQUEST['getLongHistory'])) {
    echo json_encode(Freezer::longHistory());
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
          background-color:#222222;
          font-weight:bold;
          font-size:6em;
border-radius: 12px;
      }
#lastTempTime {
font-size:0.5em;
}

  </style>
  

  <script type="text/javascript">
window.onload = function () {

//Better to construct options first and then pass it as a parameter
	var options = {
		title: {
			text: "Temperature History"
		},
axisX: {
labelAngle: 90
},
                animationEnabled: true,
		axisY: {
       			 title: "Temp F"
		     },
		data: [
		{
			type: "column", //change it to line, area, bar, pie, etc
			dataPoints: <?php echo Freezer::longHistory();?>
		}
		]
	};

	$("#chartContainer").CanvasJSChart(options);

}
</script>





  <script src="/js/script.js"></script>
  
</head>

<body>
    <div style="border:4px solid red; padding:5px; margin:15px;">
        <h1>variable configuration</h1>
        min temp<br>
        max temp<br>
        alert email list<br>
        Time between alerts<br>
        last alert verified (user has acknowledged alert) 0 = no, 1=yes, 2=temperature has returned to normal since this instance <br>
        ** need to send alerts if newest db record is x seconds old (python problems?
-OR- separate table for  status/alerts/etc.
    </div>
    
    <div style="border:4px solid red; padding:5px; margin:15px;">
        PHP cron job to call python and evaluate readings?<br>
        <h1>event types</h1>
       
    </div>
    
    
    
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

    
    <div id="tempReadout">
        <span id="lastTempVal">loading..</span> <sup>o</sup>F
    </div>

    <div id="chartContainer" style="height: 300px; width: 80%;"></div>
    
    
    
</body>
</html>

