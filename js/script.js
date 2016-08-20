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
    } else {
        liveRetrieveActive = true;
        $('#liveFeedButton').val('Live Feed On');
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
    swal({
    title: 'Settings',
    html: "Settings will go here",
    type: 'none',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    confirmButtonText: 'Yes!'
  }).then(function() {
    alert('that has been did');
  })
}

function getOverallStatus() {
    $.ajax({
        url: '/',
        type: 'POST',
        data: {
            getStatus:1
        },
        success:function(result)
        {
            var response = jQuery.parseJSON(result);
            if (response.ok) {
                $('#tempReadout').addClass('ok');
            } else {
                $('#tempReadout').removeClass('ok');
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

	recentTemps.push(response);
	recentTemps = recentTemps.slice(- 10);
        //console.log(recentTemps);

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


$(document).ready(function(){
    console.log('document is ready');
    $(document).on('click','#loadSettingsButton',loadSettings);
    $(document).on('click','#loadLastTempButton',loadLastTemp);
    
   
    loadLiveTemp();
    loadSettings();






});
