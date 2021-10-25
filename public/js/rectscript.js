function submitAfterValid(formId, massError = false) {
        var initText = $('.btn').html()
        var imgLoading = "<img src='../img/loading-buffering.gif' width='20px'>"
        $('.btn').html(imgLoading+' Processing...')
        $('.btn').attr('disabled', 'disabled')

        var datastring = $("#"+formId).serialize()
        var formData = new FormData($("#"+formId)[0]);

        var url = $("#"+formId).attr('action')
        
        $('.rect-validation').css({ "border": "1px solid #428fc7" })
        $('.error-message').remove()
        $(".progress-loading").remove()
        blinkElement('.btn')
        setInterval(blinkElement, 1000);

        $.ajax({
            type: "POST",
            url: url,
            data : formData,
            contentType : false,
            processData : false,
            success: function(response) {
                $('.btn').removeAttr('disabled')
                $(".progress-loading").remove()
                $('.btn').html(initText)
                if (response.status) {
                    window.location.href = response.redirect_to
                } else {
                    messageErrorGeneral("#"+formId, response.message)
                    if (massError && response.mass_errors) {
                        $(".modal").modal('hide')
                        $("#massError-"+formId).modal('show')
                        var htmlTable = ""
                        $.each(response.mass_errors, function( index, error ) {
                            htmlTable += "<tr><td>"+error.row+"</td><td>"+error.errormsg[0]+"</td></tr>"
                        });
                        $(".tbody-errors").html(htmlTable)
                        $('#supportDtCust').DataTable();
                    }else{
                        $.each(response.validation_errors, function( index, error ) {
                            var currentID = $("#"+error.id)
                            $(currentID).css({ "border": "1px solid #c74266" })
                            messageErrorForm(currentID, error.message)
                        });
                    }
                }
            },
            error: function(xhr, status, error) {
                $('.btn').html(initText)
                $('.btn').removeAttr('disabled')
                $(".progress-loading").remove()
                var messageErr = "Something Went Wrong"
                if (xhr.responseJSON) {
                    messageErr = xhr.responseJSON.message
                }
                messageErrorGeneral("#"+formId, messageErr)
            }
        });
}

function blinkElement(elem) {
    $(elem).fadeOut(500);
    $(elem).fadeIn(300);
    // $(elem).animate({opacity:0.6}, 500)
    // $(elem).animate({opacity:1}, 500)
}

function messageErrorForm(currentID, message) {
    $("<div class='error-message' style='color:#c74266; float:right; font-size:12px;'>" + message + "</div>")
                        .insertBefore(currentID).hide().show('medium')
}

function messageErrorGeneral(currentID, message) {
    $("<div class='error-message alert alert-danger'>" +message + "</div>")
                        .insertBefore(currentID).hide().show('medium')
}