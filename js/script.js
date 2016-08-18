var liveRetrieveInterval = 5000; //milliseconds
var recentTemps = new Array();

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
            console.log(response);
            $('#lastTempVal').html(response.temperature);
            $('#lastTempTime').html(response.minutesSince);

	recentTemps.push(response);
	recentTemps = recentTemps.slice(Math.max(recentTemps.length - 50, 1));
console.log(recentTemps);

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

clearInterval(interval);
var interval = setInterval(function() {
  // method to be executed;
console.log('interval met');
loadLiveTemp();
}, liveRetrieveInterval );

});
