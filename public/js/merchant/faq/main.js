/**
 * Disable CTA to prevent multiple clicks if the form is valid.
 * Turn it back on after 5 seconds if there is a failure.
 */
function disableCTA(){
    toggleCtaDisable('.faqsubmit', true);
    setTimeout(function () {
        toggleCtaDisable('.faqsubmit', false);
    }, 5000);
    $('#faqForm').submit()
}

/**
 * Once the validation is failed, disable the CTA.
 * Watch on mandatory fields to see if the form is valid.
 * if the form is valid enable the CTA.
 */
function watchCTA(){
    toggleCtaDisable('.faqsubmit', true);
    watchMandatoryFields(
    '#faqForm',
    ['link','title'],
    function(){
        toggleCtaDisable('.faqsubmit', false);
    },
    function(){
        toggleCtaDisable('.faqsubmit', true);
    })
}
$('.faqsubmit').click(function (e) {
    e.preventDefault()
    isFormValid("#faqForm",disableCTA,watchCTA)
});

jQuery.validator.addMethod("youtubeLink",function(link){
    if(!link){
        return true
    }
    var re = /^(http(s)??\:\/\/)?(www\.)?((youtube\.com\/watch\?v=)|(youtu.be\/))([a-zA-Z0-9\-_])+/
    if(re.test(link)) return true
    return false
},"Please enter a valid youtube link.");

$("#faqForm").validate({
    rules:{
        link:{
            youtubeLink:true
        },
        title:{
            required:true
        }
    }
})