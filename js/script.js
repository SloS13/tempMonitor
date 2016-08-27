var liveRetrieveInterval = 5000; //milliseconds
var liveRetrieveActive = false; //milliseconds
var recentTemps = new Array();
var interval; //used with interval

function toggleInterval() {
    console.log('toggle Interval called');
    if (liveRetrieveActive) {
        liveRetrieveActive = false;
        $('#liveFeedButton').val('Live Feed Off');
        doInterval();
        $("#liveChartContainer").hide('slow');
    } else {
        liveRetrieveActive = true;
        $('#liveFeedButton').val('Live Feed On');
        liveChart();
        doInterval();
    }
}

function doInterval() {
    if (liveRetrieveActive) {
        interval = setInterval(function() {
        console.log('interval met');
        loadLiveTemp();
      }, liveRetrieveInterval );
      console.log('set thing to '+liveRetrieveInterval+' seconds')
    } else {
        clearInterval(interval);
        console.log('interval cleared');
    }
}

function dialog_disableAlert() {
    swal({
    title: 'Confirm latest alert?',
    text: "Are you sure you want to confirm the latest alert?",
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    confirmButtonText: 'Yes!'
  }).then(function() {
    alert('that has been did');
  })
}

function dialog_settings() {
    var settingsInterfaceHTML;
    $.ajax({
        url: '/',
        type: 'POST',
        async:false,
        data: {
            getSettingsInterface:1
        },
        success:function(result)
        {
            settingsInterfaceHTML = jQuery.parseJSON(result);
            
            
            
        }
    }); //ajax call
    
    
    
    swal({
    title: 'Settings',
    html: settingsInterfaceHTML,
    type: 'none',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    confirmButtonText: 'Yes!'
  }).then(function() {
    var data = $('#settingsForm').serialize();
     $.ajax({
        url: '/',
        type: 'POST',
        async:false,
        data: data,
        success:function(result)
        {
           alert(result);
        }
    }); //ajax call
 })
}

function getOverallStatus() {
    console.log('getOverallStatus Called');
    $.ajax({
        url: '/',
        type: 'POST',
        data: {
            getStatus:1
        },
        success:function(result)
        {
            var response = jQuery.parseJSON(result);
            console.log('oversll status response: ' + response.ok);
            if (response.ok) {
                $('#tempReadout').addClass('panel-green').removeClass('panel-red');
                $('#overallStatusWrapper').removeClass('alert-danger').addClass('alert-info').html('Everything is cool');
            } else {
                $('#tempReadout').addClass('panel-red').removeClass('panel-green');
                $('#overallStatusWrapper').addClass('alert-danger').removeClass('alert-info').html('Problems in Freezerville');

                alert('there is a problem!');
            }
            
            
        }
    }); //ajax call
}

function loadSettings() {
    //do ajax to load settings into form
    $.ajax({
        url: '/',
        type: 'POST',
        data: {
            loadSettings:1
        },
        success:function(result)
        {
            var response = jQuery.parseJSON(result);
            $('#minTemp').val(response.minTemp);
            $('#maxTemp').val(response.maxTemp);
            $('#minutesBetweenNotifications').val(response.minutesBetweenNotifications);
            $('#alertEmails').val(response.alertEmails);
        }
    }); //ajax call
}


function loadLiveTemp() {
     $.ajax({
        url: '/',
        type: 'POST',
        data: {
            getLiveTemp:1
        },
	beforeSend:function(){
		//$('#lastTempVal').html('--.--');
	},
        success:function(result)
        {
            var response = jQuery.parseJSON(result);
            //console.log(response);
            $('#lastTempVal').html(response.temperature);
            $('#lastTempTime').html(response.minutesSince);

            var thisTemp = {x:response.minutesSince, y:response.temperature };
	recentTemps.push(thisTemp);
	recentTemps = recentTemps.slice(- 10);
        
        console.log(recentTemps);
        if (liveRetrieveActive) {liveChart();}
  $( "#lastTempVal" ).animate({
    opacity: 0.7
  }, 100, function() {
    $( "#lastTempVal" ).animate({
    opacity: 1
  }, 100, function() {
    // Animation complete.
  });
  });
	}

    }); //ajax call
}



function loadLastTemp() {
    //do ajax to load settings into form
    $.ajax({
        url: '/',
        type: 'POST',
        data: {
            loadLastTemp:1
        },
        success:function(result)
        {
            var response = jQuery.parseJSON(result);
            console.log(response);
            $('#lastTempVal').html(response.temperature);
            $('#lastTempTime').html(response.minutesSince);
        }
    }); //ajax call
}



function liveChart(){
    var options = {
		title: {
			text: "Live Data"
		},
                axisX: {
                    valueFormatString: " ",
                    tickLength: 0
                },
                axisY: {
                    minimum: parseFloat($('#lastHourAverage').val())-10,
                    maximum: parseFloat($('#lastHourAverage').val())+10,
                    
                },
                animationEnabled: true,
		data: [
		{
			type: "spline", //change it to line, area, column, pie, etc
			dataPoints: recentTemps
		}
		]
	};

	$("#liveChartContainer").CanvasJSChart(options);
        $("#liveChartContainer").show('slow');
    
    
	
}

$(document).ready(function(){
    console.log('document is ready');
    $(document).on('click','#loadSettingsButton',loadSettings);
    $(document).on('click','#loadLastTempButton',loadLastTemp);
    
   
    loadLiveTemp();
    loadSettings();
    getOverallStatus();

    setTimeout(getOverallStatus, 5000);
    


    $(document).on('click','.nav.navbar-nav li a',function(e){
        e.preventDefault();
        $('.nav.navbar-nav li').removeClass('active');
        $(this).find('li').addClass('active');
        $('.container-fluid').hide('slow');
        $('#'+$(this).data('container')).show('slow');
    });
});
