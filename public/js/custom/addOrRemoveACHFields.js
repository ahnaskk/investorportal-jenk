// $(document).ready(function(){
//     //     $(".confirmed-payment").prop('checked', true);
//     //     $(".#select-all").prop('checked', true);


//     })

var mappedInitialValues = ach_fee_amounts || null
$(document).ready(function(){

    function totalFee() {
        var total = 0;
        $('.fees').each(function (index, element) {
            if(!isNaN($(element).val()) && $(element).val()!=0) {
                total += parseFloat($(element).val());
            }
        });
        $('.ach-fee').each(function (index, element) {
            if(!isNaN($(element).val()) && $(element).val()!=0) {
                total += parseFloat($(element).val());
            }
        })
        $('.totalFee').val(total.toFixed(2))
    }
    $(document).on("change", ".payments", function () {
        var total = 0;
        $('.payments').each(function (index, element) {
            if(!isNaN($(element).val()) && $(element).val()!=0) {
                total += parseFloat($(element).val());
            }
        });
        $('.total').val(total.toFixed(2))
    });
    $(document).on("change", ".fees", function () {
        totalFee()
    });
    let fieldMap = [
        {name:'ach_rejection', value: '1'},
        {name:'nsf', value: '2'},
        {name:'bank_change', value: '3'},
        {name:'blocked_account', value: '4'},
        {name:'default_fee', value: '6'},
    ]
    //hide all fields if it does not have value

    //select fields
    $(".add-remove-selector").select2({
        multiple:true,
        placeholder:'Select fields',
        containerCssClass: "error",
        dropdownCssClass: "test"
    })
    $(".add-remove-selector").val(null).trigger('change');
    $(".select-inner-wrapper").addClass("hide");
    $(".fees").each(function(index,el){
        if($(el).val() <= 0 ){
            $(el).addClass("hide");
        }
    })

    $(".outer-wrapper").each(function(index,el){
        el = $(el).children('.fees')
        let idArray = []
        $(el).each(function(index,el){
            if($(el).val() > 0){
                let name = el.getAttribute('name');
                let nameArray = name.split('[')
                name = nameArray[3].replace("]",'')
                fieldMap.forEach(function(field){
                    if(field.name == name){
                        idArray.push(field.value)
                    }
                })
            }
            $(el).parent().children().find('select').val(idArray).trigger('change')
        })
    })

    $(".add-remove-action").click(function(){
        $(this).parents().children('.select-inner-wrapper').toggleClass('hide');
        if($(this).data('isOpen')){
            $(this).data('isOpen', false);
            $(this).text('Add  or remove fields')
            //get selected fields
            let fields = $(this).siblings().find('select').val();
            let inputs = $(this).parents('.outer-wrapper').children('input.fees');
            let valueNameArray = []
            fieldMap.forEach(function(item){
                if(fields.includes(item.value)){
                    valueNameArray.push(item.name)
                }
            })
            inputs.each(function(index,el){
                let name = el.getAttribute('name');
                let nameArray = name.split('[')
                name = nameArray[3].replace("]",'')
                if(valueNameArray.includes(name)){
                    el.classList.remove('hide')
                    el.value =  mappedInitialValues[name]
                }
                else{
                    el.classList.add('hide')
                    el.value = ''
                }
            }) 
            totalFee()
        }else{
            $(this).data('isOpen', true);
            $(this).text('Done')
        }
    })
    totalFee()
    $('#formId').validate({
        ignoreTitle: true,
    })
})