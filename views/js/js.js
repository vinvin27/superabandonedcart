$(function(){
	$('#include_voucher_enable').attr('checked','checked');
	$('#include_voucher_enable').change(function(){

		if( $(this).is(':checked') ){

		 $('.voucher_mode').parent().parent('.form-group').fadeIn();

		}

	});
	
	$('#include_voucher_disable').change(function(){

		if( $(this).is(':checked') ){

		 $('.voucher_mode').parent().parent('.form-group').fadeOut();

		}

	});

});