$(document).ready(function() {  

	$(".field").parent().hide();

	$(".gamehead td.game").click(function() {
			$(this).parent().next(".gameedit").toggle('slow');
	});
	$(".gamehead td.score a b").click(function() {
			$(this).parent().addClass("clicked");
				$(window).unload(function() {  			
			$(this).parent().removeClass("clicked");});
	});

	
	
	$("#gamelist tr td").click(function() {
			//alert($(this).attr("id"));
		

			var myclass = "." + $(this).attr("id");
			
			if($(this).attr("class") =='day' ) {
			//$(".gamehead").hide();
			$(".gameedit").hide();
			$(myclass).each(function(index) {
				var gamehead = "." + $(this).children().first().attr("id");
				$(gamehead).hide();
				$(this).toggle();
			});
			//$(myclass).toggle();			
			}
			//alert(myclass);
			if($(this).attr("class") =='field') { 
			$(".gameedit").hide();
			$(myclass).toggle();
			}
			
			
	});
	
	
	$("#show_all_link").click(function() {
			$(".gamehead").show();
			$(".field").parent().show();
	});
	
});

