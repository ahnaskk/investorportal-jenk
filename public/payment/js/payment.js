/**
 * Created by shahad on 24/11/20.
 */
$('.card-number').on("keydown", function(e) {
	var cursor = this.selectionStart;
	if (this.selectionEnd != cursor) return;
	if (e.which == 46) {
		if (this.value[cursor] == " ") this.selectionStart++;
	} else if (e.which == 8) {
		if (cursor && this.value[cursor - 1] == " ") this.selectionEnd--;
	}
}).on("input", function() {
	var value = this.value;
	var cursor = this.selectionStart;
	var matches = value.substring(0, cursor).match(/[^0-9]/g);
	if (matches) cursor -= matches.length;
	value = value.replace(/[^0-9]/g, "").substring(0, 16);
	var formatted = "";
	for (var i=0, n=value.length; i<n; i++) {
		if (i && i % 4 == 0) {
			if (formatted.length <= cursor) cursor++;
			formatted += " ";
		}
		formatted += value[i];
	}
	if (formatted == this.value) return;
	this.value = formatted;
	this.selectionEnd = cursor;
});


var expiryMask = function() {
	var inputChar = String.fromCharCode(event.keyCode);
	var code = event.keyCode;
	var allowedKeys = [8];
	if (allowedKeys.indexOf(code) !== -1) {
		return;
	}

	event.target.value = event.target.value.replace(
		/^([1-9]\/|[2-9])$/g, '0$1/'
	).replace(
		/^(0[1-9]|1[0-2])$/g, '$1/'
	).replace(
		/^([0-1])([3-9])$/g, '0$1/$2'
	).replace(
		/^(0?[1-9]|1[0-2])([0-9]{2})$/g, '$1/$2'
	).replace(
		/^([0]+)\/|[0]+$/g, '0'
	).replace(
		/[^\d\/]|^[\/]*$/g, ''
	).replace(
		/\/\//g, '/'
	);
}

var splitDate = function($domobj, value) {
	var regExp = /(1[0-2]|0[1-9]|\d)\/(20\d{2}|19\d{2}|0(?!0)\d|[1-9]\d)/;
	var matches = regExp.exec(value);
	$domobj.siblings('input[name$="expiryMonth"]').val(matches[1]);
	$domobj.siblings('input[name$="expiryYear"]').val(matches[2]);
}

$('#date-exp').on('keyup', function(){
	var max_chars = 5;
	if ($(this).val().length >= max_chars) {
		$(this).val($(this).val().substr(0, max_chars));
	}
	expiryMask();
});

$('.card-cvc').on('keyup', function(){
	var max_chars = 3;
	if ($(this).val().length >= max_chars) {
		$(this).val($(this).val().substr(0, max_chars));
	}
});

$('#date-exp').on('focusout', function(){
	splitDate($(this), $(this).val());
});


$(function () {

	var $form = $(".require-validation");
	$('form.require-validation').bind('submit', function (e) {
		var $form = $(".require-validation"),
			inputSelector = ['input[type=email]', 'input[type=password]',
			                 'input[type=text]', 'input[type=file]',
			                 'textarea'].join(', '),
			$inputs = $form.find('.required').find(inputSelector),
			$errorMessage = $form.find('div.error'),
			valid = true;
		$errorMessage.addClass('d-none');

		$('.has-error').removeClass('has-error');
		$inputs.each(function (i, el) {
			var $input = $(el);
			if ($input.val() === '') {
				$input.parent().addClass('has-error');
				$errorMessage.removeClass('d-none');
				e.preventDefault();
			}
		});

		if (!$form.data('cc-on-file')) {
			$('#subthis').prop('disabled', true);
			//alert(1);
			e.preventDefault();
			var expgroup = $("#date-exp").val();
			var expArray = expgroup.split( '/' );
			var expmm = ( expArray[ 0 ] );
			var expyy = ( expArray[ 1 ] );

			Stripe.setPublishableKey($form.data('stripe-publishable-key'));
			Stripe.createToken({
				                   number   : $('.card-number').val().replace(/[^0-9]/g, ""),
				                   cvc      : $('.card-cvc').val(),
				                   exp_month: expmm,
				                   exp_year : expyy,
			                   }, stripeResponseHandler);
		}

	});

	$("#amount").keyup(function(){
		percentage_amount = ((3.75 / 100) * $(this).val());
		total_amount = (percentage_amount + parseFloat($(this).val())).toFixed(2);
		if(isNaN(total_amount)){
			total_amount = "0.00";
		}
		$("#final_amount").html(total_amount);
		$("#total_amount").val(total_amount);
		$("#amount_to_display").val(total_amount);
	});




	function stripeResponseHandler(status, response) {

		if (response.error) {
			$('#subthis').prop('disabled', false);
			$('.error')
				.removeClass('d-none')
				.find('.alert')
				.text(response.error.message);
		} else {
			// token contains id, last4, and card type
			var token = response['id'];
			// insert the token into the form so it gets submitted to the server
			$form.find('input[type=text]').empty();
			$form.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");
			//alert(2);
			$form.get(0).submit();
		}
	}

	function percentage(partialValue, totalValue) {
		return ((100 * partialValue) / totalValue)+ totalValue;
	}

	$('#amount').on('input', function () {
		this.value = this.value.match(/^\d+\.?\d{0,2}/);
	});
	var amount = $("#total_amount").val()
	$("#amount").val(amount).keyup();

});