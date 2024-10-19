$(document).on('click', '.register-course', function(){
    let data = common.getForm()
    data.append('course_session_id', $(this).attr('data-id'))
    data.append('user_id', user_id)

    http.fetch({
        url: `${common.getUrl()}${moduleUrl}register-participant`,
        data: data,
        method: 'POST',
        callback: function(r){
            if(r.status){
                alerting.fireSwal({
                    text: r.data.message,
                    icon: 'success',
                    buttonColor: 'btn btn-success',
                    confirmButton: 'Close',
                    callback: function(){
                        window.location.href = r.data.url
                    }
                })
            }else{
                alerting.error(r.data);
            }
        }
    })
})

$('.register-information').on('click', function(){
    let data = common.getForm();
    data.append('event_id', $(this).attr('data-id'));
    eventModal.show({
        title: 'Maklumat Program',
        buttons: [],
        callback: function(){
            http.fetch({
                url: `${common.getUrl()}${moduleUrl}get-description`,
                data: data,
                method: 'POST',
                callback: function(r){
                    if(r.status){
                        $('#event-modal').find('.modal-title').empty().append(r.data.data.name);
                        $('#event-modal').find('.event-description').empty().append(r.data.data.description);
                        $('#event-modal').find('.event-objective').empty().append(r.data.data.objective);
                    }else{
                        alerting.error(r.data);
                    }
                }
            });
        }
    });
})

