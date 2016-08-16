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
            $('#lastTempDate').html(response.readingTime);
        }
    }); //ajax call
}


$(document).ready(function(){
    
    $(document).on('click','#loadSettingsButton',loadSettings);
    $(document).on('click','#loadLastTempButton',loadLastTemp);
    
    
});