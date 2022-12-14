var baseUrl = $("meta[name=base_url]").attr("content");    
blockTempValidation()

function submitAfterValid(formId, massError = false) {
        var initText = $('#btn-for-'+formId).html()

        var imgLoading = "<img src='"+baseUrl+"/img/loading-buffering.gif' width='20px'>"
        $('#btn-for-'+formId).html(imgLoading+' Processing...')
        $('#btn-for-'+formId).attr('disabled', 'disabled')

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
                $('#btn-for-'+formId).removeAttr('disabled')
                $(".progress-loading").remove()
                $('#btn-for-'+formId).html(initText)
                if (response.status) {
                    messageStatusGeneral("#"+formId, response.message, 'success')

                    if (response.redirect_to) {
                        if (response.newtab) {
                            window.open(response.redirect_to, '_blank');
                        }else{
                            window.location.href = response.redirect_to
                        }
                    }else{
                        setTimeout(function() { 
                            location.reload(true)
                        }, 3000);
                    }
                } else {
                    messageStatusGeneral("#"+formId, response.message)
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
                // console.log(xhr.responseJSON.errors);
                $('#btn-for-'+formId).html(initText)
                $('#btn-for-'+formId).removeAttr('disabled')
                $(".progress-loading").remove()
                var messageErr = "Something Went Wrong"
                if (xhr.responseJSON) {
                    messageErr = xhr.responseJSON.message
                    $.each(xhr.responseJSON.errors,function(field_name,error){
                        messageErr += "<li>"+error+"</li>"
                        //$("#"+formId+' [name='+field_name+']').append('<span class="text-strong textdanger">' +error+ '</span>')
                    })
                }
                
                messageStatusGeneral("#"+formId, messageErr)
            }
        });
}


function ajaxDownloadFile(formId, attrs) {
    var initText = $('#btn-for-'+formId).html()

    var imgLoading = "<img src='"+baseUrl+"/img/loading-buffering.gif' width='20px'>"
    
    $('.rect-validation').css({ "border": "1px solid #428fc7" })
    $('.error-message').remove()
    $(".progress-loading").remove()
    blinkElement('.btn')
    setInterval(blinkElement, 1000);
    $('.nav-link').attr('onclick', 'alertAnyProcess()')
    $('#btn-for-'+formId).html(imgLoading+' Processing...')
    $('#btn-for-'+formId).attr('disabled', 'disabled')


    fetch(attrs.action)
    .then(response => {
        return response.blob().then((data) => {
            var conDis = response.headers.get('Content-Disposition');
            
            var name = attrs.filename
            if(conDis.includes("filename*=UTF-8''")){
                name = response.headers.get('Content-Disposition').split("filename*=UTF-8''")[1];
            }

           return {
             data: data,
             filename: name,
             contentType: response.headers.get('Content-Type'),
           };
        });
     })
     .then(({ data, filename , contentType}) => {
        const blob = new Blob([data], { type: contentType });
        const downloadUrl = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = downloadUrl;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        $('#btn-for-'+formId).removeAttr('disabled')
        $(".progress-loading").remove()
        $('#btn-for-'+formId).html(initText)
        $('.nav-link').removeAttr('onclick')
        new Noty({
            type: "success",
            text: "File is downloaded successfully"
          }).show();
    })
    .catch(() => {
        $('#btn-for-'+formId).removeAttr('disabled')
        $(".progress-loading").remove()
        $('#btn-for-'+formId).html(initText)
        $('.nav-link').removeAttr('onclick')
        new Noty({
            type: "danger",
            text: "There is an error"
          }).show();
    });
}

function submitAjaxValid(formId, attrs) {
    var initText = $('#btn-for-'+formId).html()

    var imgLoading = "<img src='"+baseUrl+"/img/loading-buffering.gif' width='20px'>"
    
    $('.rect-validation').css({ "border": "1px solid #428fc7" })
    $('.error-message').remove()
    $(".progress-loading").remove()
    blinkElement('.btn')
    setInterval(blinkElement, 1000);
    $('.nav-link').attr('onclick', 'alertAnyProcess()')
    $('#btn-for-'+formId).html(imgLoading+' Processing...')
    $('#btn-for-'+formId).attr('disabled', 'disabled')
    
    $.ajax({
        type: "POST",
        url: attrs.action,
        data : attrs.data,
        success: function(response) {
            $('#btn-for-'+formId).removeAttr('disabled')
            $(".progress-loading").remove()
            $('#btn-for-'+formId).html(initText)
            if (response.status) {
                messageStatusGeneral("#"+formId, response.message, 'success')
                if (response.redirect_to) {
                    if (response.newtab) {
                        window.open(response.redirect_to, '_blank');
                    }else{
                        window.location.href = response.redirect_to
                    }
                }else{
                    setTimeout(function() { 
                        location.reload(true)
                    }, 3000);
                }
            } else {
                messageStatusGeneral("#"+formId, response.message)
            }
            $('.nav-link').removeAttr('onclick')
        },
        error: function(xhr, status, error) {
            $('#btn-for-'+formId).html(initText)
            $('#btn-for-'+formId).removeAttr('disabled')
            $(".progress-loading").remove()
            var messageErr = "Something Went Wrong"
            if (xhr.responseJSON) {
                if (xhr.responseJSON.message != "") {
                    messageErr = xhr.responseJSON.message
                    $.each(xhr.responseJSON.errors,function(field_name,error){
                        messageErr += "<li>"+error+"</li>"
                    })
                }
            }
            $('.nav-link').removeAttr('onclick')
            messageStatusGeneral("#"+formId, messageErr)
        }
    });
}


function submitAsyncPost(formId, attrs) {
    var initText = $('#btn-for-'+formId).html()

    var imgLoading = "<img src='"+baseUrl+"/img/loading-buffering.gif' width='20px'>"
    var preventReload = (attrs.preventReload)? attrs.preventReload : false;

    $('#btn-for-'+formId).html(imgLoading+' Processing...')
    $('#btn-for-'+formId).attr('disabled', 'disabled')
    
    $('.rect-validation').css({ "border": "1px solid #428fc7" })
    $(".progress-loading").remove()
    blinkElement('.btn')
    setInterval(blinkElement, 1000);

    $.ajax({
        type: "POST",
        url: attrs.action,
        data : attrs.data,
        success: function(response) {
            $('#btn-for-'+formId).removeAttr('disabled')
            $(".progress-loading").remove()
            $('#btn-for-'+formId).html(initText)
            if (response.status) {
                new Noty({
                    type: "success",
                    text: response.message
                  }).show();
                  if (preventReload == false) {
                    setTimeout(function() { 
                        location.reload(true)
                    }, 3000);
                  }
            } else {
                new Noty({
                    type: "danger",
                    text: response.message
                  }).show();
            }
        },
        error: function(xhr, status, error) {
            $('#btn-for-'+formId).html(initText)
            $('#btn-for-'+formId).removeAttr('disabled')
            $(".progress-loading").remove()
            var messageErr = "Something Went Wrong"
            if (xhr.responseJSON) {
                if (xhr.responseJSON.message != "") {
                    messageErr = xhr.responseJSON.message
                    $.each(xhr.responseJSON.errors,function(field_name,error){
                        messageErr += "<li>"+error+"</li>"
                    })
                }
            }
            new Noty({
                type: "danger",
                text: messageErr
              }).show();
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

function messageStatusGeneral(currentID,message ,status = 'danger' ) {
    $("<div class='error-message alert alert-"+status+" alert-dismissible fade show'>" +message + " <button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>")
                        .insertBefore(currentID).hide().show('medium')
}

function blockTempValidation() {
    if ($('.validation-row-temp').find('text-danger')) {
    }
    $.each($('.validation-row-temp'), function( k, v ) {
        console.log(k);
        if ($('.validation-row-temp:eq('+k+') li span').find('text-danger')) {
            console.log(k);
        }
    })
}

function alertAnyProcess() {
    alert("Masih ada proses download, aksi anda akan terpending")
}