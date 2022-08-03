/**
 * @param {String} el target button to toggle disable.
 * @param {Boolean} status disable status, whether disable or not.
 */
function toggleCtaDisable(el,status){
    el = $(el)
    el.prop('disabled',status)
    return status
}
/**
 * 
 * @param {String} form id or class name of the form where the fields to be watched.
 * @param {Array} fields name of the fields to be watched on input.
 * @param {Function} callbackFn function to be  executed on success.
 * @param {Function} rejectCallback function to be executed on failure.
 */
function watchMandatoryFields(form,fields,callbackFn,rejectCallback){
    listenerList = ''
    for(var i=0; i<fields.length; i++){
        listenerList+=form+' [name="'+fields[i]+'"]'
        if(!i == fields.length -1){
            listenerList+=' , '
        }
    }
    $(listenerList).on('input', function() {
        if($(form).valid()){
            if(callbackFn){
                callbackFn();
            }
        }else{
            if(rejectCallback){
                rejectCallback();
            }
        }
    });
    return null
}

/**
 * 
 * @param {String} form id or class of the form.
 * @param {Function} successCallback function to be executed on success.
 * @param {Function} rejectCallback callback function to be executed on failure.
 */
function isFormValid(form,successCallback,rejectCallback){
    var valid = $(form).valid()
    if(valid){
        if(successCallback){
            successCallback();
        }
    }else{
        if(rejectCallback){
            rejectCallback();
        }
    }
    return valid
}