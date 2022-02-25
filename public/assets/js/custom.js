/*********** Navigation *************/
$(".nav-toggle").click(function() {
	$(this).toggleClass("active");
	$(".overlay-b").toggleClass("open");
});

$(".overlay ul li a").click(function() {
	$(".nav-toggle").toggleClass("active");
	$(".overlay-b").toggleClass("open");
});

$(".overlay").click(function() {
	$(".nav-toggle").toggleClass("active");
	$(".overlay-b").toggleClass("open");
});
/*********** owl carousel *************/

var owl = $('.owl-carousel_01');
	owl.owlCarousel({
    loop:true,
	items:1,
	autoplay:false,
	dots:true,
    responsiveClass:true,

});
var owl = $('.owl-carousel_011');
	owl.owlCarousel({
    loop:true,
	items:1,
	autoplay:true,
	dots:true,
    responsiveClass:true,

});

/*********** Header Scroll effect *************/
jQuery(document).ready(function($) {

	$(".mheader").sticky({topSpacing: 0});
  $("#loader").fadeOut("slow", function(){

	$("#preloader").delay(300).fadeOut("slow");
  });     
	
	 $('.m-Nav li').each(function(i){
			var t = $(this);
		setTimeout(function(){ t.addClass('animation'); }, (i+1) * 200);		
			
		});

});



/*********** a href move transition *************/
	
var $root = $('html, body');
	$('a').click(function() {
		var href = $.attr(this, 'href');
		$root.animate({
			scrollTop: $(href).offset().top - 100
		}, 1500, function () {
			window.location.hash = href;
		});
		return false;
	});
 

//wow animations
	var wow = new WOW({
    	offset:100,
    	mobile:false
  	});
	wow.init();	  




	









	