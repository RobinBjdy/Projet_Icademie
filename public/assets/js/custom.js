function ajax(param) {
    var xhr = new XMLHttpRequest();

    var url = param.url;
    var type = param.type ? param.type : "GET";
    var data = param.data ? param.data : {};

    xhr.onload = function () {
        if (this.status == 200) {
            param.success(xhr.responseText);
        }
        else {
            param.error(xhr.responseText);
        }
    }

    xhr.open(type, url, true);
    xhr.send(data);

    return xhr;
}

document.querySelectorAll(".ajax-submit").forEach(form => {
    form.addEventListener("submit", (event) => {

        if (event.submitter.formTarget != "_blank") {
            event.preventDefault();

            ajax({
                url: form.action,
                type: "POST",
                data: new FormData(form),

                success: function (response) {
                    sendToast(response, "success")

                    $('#reservation_materiel').modal('hide')

                    document.getElementById("google-calendar").src="https://calendar.google.com/calendar/embed?src=robin.projet.icademie%40gmail.com&ctz=Europe%2FParis";
                },

                error: function (response) {
                    sendToast(response, "error")
                }
            });
        }
    });
});

// TOAST
function sendToast (message, type) {
    document.querySelector('#toasts').insertAdjacentHTML(
      'afterbegin',
      `<div class="toast ${type}" role="alert" data-bs-autohide="true" data-bs-delay="3000" aria-live="assertive" aria-atomic="true">
      <div class="toast-body">
        ${message}
        <a type="button" data-bs-dismiss="toast" aria-label="Close"><i class="fa-solid fa-xmark"></i></a>
      </div>
    </div>`      
    )

    let myAlert = document.querySelector('.toast')
    let bsAlert = new bootstrap.Toast(myAlert)
    bsAlert.show()
}

document.querySelectorAll(".delete-event-google").forEach(form => {
    form.addEventListener("click", (event) => {
        event.preventDefault();

        var id = event.target.dataset.id

        var route = Routing.generate("delete_event_google", {"id": id})

        deleteButton = document.getElementById('valid_delete')

        document.getElementById('valid_delete').onclick = function() {
            
            ajax({
                url: route,
                type: "GET",
    
                success: function (response) {
                    sendToast(response, "success")
                    $('#delete_event_google').modal('hide')

                    document.getElementById("reservations-" + id).remove()
                },
    
                error: function (response) {
                    sendToast(response, "error")
                }
            });

        };
    });
});

document.querySelectorAll(".edit-event-google").forEach(form => {
    form.addEventListener("click", (event) => {
        event.preventDefault();

        var id = event.target.dataset.id

        if(id == undefined){
            sendToast("Une erreur est survenue", "error")
        }else{

            ajax({
                url: event.target.dataset.route,
                type: "GET",

                success: function (response) {
                    $('#edit_event_google').find('.modal-body').html(response)

                    document.querySelectorAll(".ajax-submit").forEach(form => {
                        form.addEventListener("submit", (event) => {
                    
                            if (event.submitter.formTarget != "_blank") {
                                event.preventDefault();

                                ajax({
                                    url: form.action,
                                    type: "POST",
                                    data: new FormData(form),
                    
                                    success: function (response) {
                                        data = JSON.parse(response)

                                        sendToast(data.response, "success")
                    
                                        $('#edit_event_google').modal('hide')

                                        debut = new Date(data.reservation.debut.date)

                                        monthDebut = (debut.getMonth() + 1).toString().padStart(2, "0")
                                        document.getElementById(data.reservation.id + '-debut').innerHTML = debut.getDate().toString().padStart(2, "0") + '/' +  monthDebut + '/' + debut.getFullYear()
                                        document.getElementById(data.reservation.id + '-debut-hour').innerHTML = debut.getHours().toString().padStart(2, "0") + ':' + debut.getMinutes().toString().padStart(2, "0")

                                        fin = new Date(data.reservation.fin.date)

                                        monthFin = (fin.getMonth() + 1).toString().padStart(2, "0")
                                        document.getElementById(data.reservation.id + '-fin').innerHTML = fin.getDate().toString().padStart(2, "0") + '/' +  monthFin + '/' + fin.getFullYear()
                                        document.getElementById(data.reservation.id + '-fin-hour').innerHTML = fin.getHours().toString().padStart(2, "0") + ':' + fin.getMinutes().toString().padStart(2, "0")

                                    },
                    
                                    error: function (response) {
                                        sendToast(response, "error")
                                    }
                                });
                            }
                        });
                    });
                },

                error: function (response) {
                    sendToast(response, "error")
                }
            });
        }
    });
});

