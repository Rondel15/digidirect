require(['jquery', 'jquery/ui'], function($){

    jQuery(document).ready( function() {
        var $ = jQuery.noConflict();

        // alert("1");

        function renderItem(itemData) {
          // alert("2");

          let ticketLink = itemData.url;
          let eventsImg = itemData.logo.url;
          let startDate = new Date(itemData.start.local).getFormatDate();
          let endDate = new Date(itemData.end.local).getFormatDate();

          const events_thumbnail = $('<img id="eventsbrite_thumbnail" src="'+ eventsImg +'"/>');
          const event_name = $('<h3 id="eventsbrite_name" />').text(itemData.name.text);
          const eventDate = $('<p id="eventsbrite_date" />').text(startDate + " - " + endDate);
          const description = $('<p id="eventsbrite_description" />').html(itemData.description.text + " " + '<a id="link_more" href="'+ ticketLink +'" target="_blank"/>more</a>');
          const ticket_url = $('<a id="eventsbrite_link" href="'+ ticketLink +'" target="_blank"/>').html('JOIN THIS EVENT');
          const item = $('<li/>').append(event_name, events_thumbnail, description, eventDate, ticket_url);

          $('#digiEventlist').append(item);

          $(ticket_url).wrap('<div class="wrap_ticket_url"></div>');

          // console.log(new Date(startDate).getFormatDate())

          // console.log(startDate);

        }

        // alert("3");
 
        $(function() {
          $.ajax({
            // url: "https://www.eventbriteapi.com/v3/organizations/80988983007/events/?time_filter=current_future&order_by=start_desc&token=OGT3SWONGWH5KTAZTD2Z",
            url: "https://www.eventbriteapi.com/v3/organizations/80988983007/events/?token=7OCEGMNMNZO6WLUFU2FM&status=live",
            type: "get",
            dataType: "json",
            success: function(data) {
              data.events.forEach(renderItem);
            }
          });

            // alert("4");

        });

    });

});
  
  
  
Date.prototype.getFormatDate = function() {
  
    var monthNames = ["January", "February", "March", "April", "May", "June","July", "August", "September", "October", "November", "December"];

    // var time = this.getHours() + ":" + this.getMinutes();
    var hours = this.getHours();
    var minutes = this.getMinutes();
    var ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    minutes = minutes < 10 ? '0'+minutes : minutes;
    var formattedTime = hours + ':' + minutes + ampm;

    return this.getDate() + ' ' + monthNames[this.getMonth()] + ', ' + this.getFullYear() + '@' + formattedTime;
    //   return this.getDate() + ' ' + monthNames[this.getMonth()] + ', ' + this.getFullYear();
    //   '+(d.getHours() > 12 ? d.getHours() - 12 : d.getHours())+':'+d.getMinutes()+' '+(d.getHours() >= 12 ? "PM" : "AM")

}




