$(document).ready(function() {  
	$(".gamehead td.game").click(function() {
			$(this).parent().next(".gameedit").toggle('slow');
	});
	$(".gamehead td.score a b").click(function() {
			$(this).parent().addClass("clicked");
				$(window).unload(function() {  			
			$(this).parent().removeClass("clicked");});
	});

	$(".field").click(function() {
	
			$(this).parent().next(".gamehead").toggle('fast');
			$(this).parent().next(".gamehead").toggle('fast');
	});
	

	
});