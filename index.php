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
?>

<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">

  <title>Maloney Freezer</title>
  <meta name="description" content="Maloney Freezer">
  <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="   crossorigin="anonymous"></script>
  <script src="/js/script.js"></script>
  
  <!-- Bootstrap -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">
  
  <link href='https://fonts.googleapis.com/css?family=Orbitron:400,500,700,900' rel='stylesheet' type='text/css'>
  
  
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
      * {
         font-family: 'Orbitron', sans-serif; 
      }
  </style>
  
  <script src="/js/script.js"></script>
  
</head>

<body>
    history with graph<br><br>
    current temperature<br><br>
    configuration editor<br><br>
    
    <div style="border:4px solid red; padding:5px; margin:15px;">
        <h1>variable configuration</h1>
        min temp<br>
        max temp<br>
        alert email list<br>
        Time between alerts<br>
        last alert verified (user has acknowledged alert) 0 = no, 1=yes, 2=temperature has returned to normal since this instance <br>
        ** need to send alerts if newest db record is x seconds old (python problems?
    </div>
    
    <div style="border:4px solid red; padding:5px; margin:15px;">
        1 table with settings<br>
        1 table with events which includes notifications and temperature readings<br>
        PHP cron job to call python and evaluate readings?<br>
        <h1>event types</h1>
        t = temperature<br>
        a = alert<br>
        
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
    <input type="button" value="Load Last Temp" id="loadLastTempButton">
    
    
    lastTempVal:<span id="lastTempVal">NO DATA</span><br><br>
    lastTempDate:<span id="lastTempDate">NO DATA</span><br><br>
    
    
    
    
</body>
</html>

