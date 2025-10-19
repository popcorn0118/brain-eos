document.addEventListener('DOMContentLoaded', function() {
    let modal_msg = document.getElementById('modal_msg');
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialDate: params.initial_date,
        editable: true,
        dateClick: function(info) {
            console.log(info);
            modal_msg.innerHTML = info.dateStr;
            jQuery("#msg_modal").modal('open');
        },
        businessHours: true,
        locale: 'zh-tw',
        dayMaxEvents: true, // allow "more" link when too many events
        headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: '',
        },
        datesSet: function(info) {
            console.log(info);
            console.log(info.startStr.split('T')[0]);
            console.log(info.endStr.split('T')[0]);
            jQuery.ajax({
                url: params.ajaxurl,
                type: 'POST',
                cache:      false,
                dataType:   "json",
                data: {
                    action: 'eos_erp_get_datesset',
                    start: info.startStr.split('T')[0],
                    end: info.endStr.split('T')[0],
                },
                beforeSend: function(){
                    jQuery("#loading").show();
                },
                success: function(data){
                    // console.log(data);
                    // var events = [];
                    // data.forEach(function(item){
                    //     events.push({
                    //         title: item.title,
                    //         start: item.start,
                    //         end: item.end,
                    //         url: item.url,
                    //     });
                    // });
                    // calendar.removeAllEvents();
                    // calendar.addEventSource(events);
                    // calendar.render();
                },
                error: function(data){
                    console.log(data);
                },
                complete: function(){
                    jQuery("#loading").hide();
                }
            });
        },
    });
    calendar.render();
});