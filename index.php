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

if (isset($_REQUEST['getSettingsInterface'])) {
    $x = Freezer::settingsInterface('array');
    echo json_encode($x);
    exit;
}

if (isset($_REQUEST['settingsFormSubmitted'])) {
    $x = Freezer::updateSettings($_POST);
    echo json_encode('OK');
    exit;
}


    //get data for long history
   $longHistory = Freezer::longHistory();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Maloney Freezer</title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/sb-admin.css" rel="stylesheet">

    <!-- Morris Charts CSS -->
    <link href="css/plugins/morris.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <!-- jQuery -->
    <script src="js/jquery.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/canvasjs/1.7.0/jquery.canvasjs.min.js"></script>
    <script src="/js/script.js"></script>
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
    

<style>
    #tempReadout {
        font-family: 'Orbitron', sans-serif;
        
        font-size:6em;

      }
      
      #tempReadoutxyz {
          background-color:#1dc116;
      }
</style>
</head>

<body>
<div id="data">
        <input type="hidden" id="lastHourAverage" value="<?php echo Freezer::getLastHourAverage('array');?>">
    </div>
    <div id="wrapper">

        <!-- Navigation -->
        <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.html">Maloney Freezer</a>
            </div>
            <!-- Top Menu Items -->
            
            <!-- Sidebar Menu Items - These collapse to the responsive navigation menu on small screens -->
            <div class="collapse navbar-collapse navbar-ex1-collapse">
                <ul class="nav navbar-nav side-nav">
                    <li class="active">
                        <a href="index.html" data-container="container_dashboard"><i class="fa fa-fw fa-dashboard"></i> Dashboard</a>
                    </li>
                    <li>
                        <a href="forms.html" data-container="container_settings"><i class="fa fa-fw fa-gear"></i> Settings</a>
                    </li>
                    
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </nav>

        <div id="page-wrapper">
            <div class="container-fluid" id="container_settings" style="display:none;">
                settings go here
            </div>

            <div class="container-fluid" id="container_dashboard">

                <!-- Page Heading -->
                <div class="row">
                    <div class="col-lg-12">
                        <h1 class="page-header">
                            Dashboard <small>Freezer Overview</small>
                        </h1>
                        <div id="overallStatusWrapper" class="alert">
                            Status not determined at this time
                        </div>
                    </div>
                </div>
                <!-- /.row -->

                

                
                
                <div class="row">
                    
                    <div class="col-lg-5 col-md-6">
                        <div class="panel panel-red" id="tempReadout">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-6" style="white-space:nowrap;">
                                            <span id="lastTempVal">100</span> <sup>o</sup>F
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
    
                </div>
                <!-- /.row -->

                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-bar-chart-o fa-fw"></i> Temperature History</h3>
                            </div>
                            <div class="panel-body">
                                <div id="chartContainer" style="height: 300px; width: 80%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.row -->

                <div class="row">
                    
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-clock-o fa-fw"></i> History</h3>
                            </div>
                            <div class="panel-body">
                                <div class="list-group">
                                    <?php echo Freezer::generateAlertHistoryHTML();?>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                    
                </div>
                <!-- /.row -->

            </div>
            <!-- /.container-fluid -->

        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

    

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>

    <!-- Morris Charts JavaScript -->
    <script src="js/plugins/morris/raphael.min.js"></script>
    <script src="js/plugins/morris/morris.min.js"></script>
    <script src="js/plugins/morris/morris-data.js"></script>

</body>

</html>
