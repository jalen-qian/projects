// JavaScript Document

$(document).ready(function(){
	
	//checkall
	$('#chkall').click(function(){
		if($('#chkall').attr('checked')){
			$(".checkbox").attr('checked','checked');
		}else{
			$(".checkbox").attr('checked','');
		}
	});
});



