
/**
 * @file 
 * This file handles the JS for Media Module functions
 */
 
 
 /**
  * This handles the activation of drawers on the media browser form
  * @TODO make this a drupal behavior
  */
 $(document).ready( function () { 
  // hide the display information on page load
  $('.media.browser.display').each(function() {
    $(this).hide();
  });
 
  // Activate drawers when the page loads  
  $('.media.browser .ui-tabs-panel .drawers .item-list li.first').addClass('active');
  // Activate displays when the page loads  
  $('.media.browser .ui-tabs-panel .display.first').addClass('active').show();
  
  
	// now we need to bind click functionality on drawers to display
	$('.media.browser .ui-tabs-panel .drawers .item-list ul li, .drawers li a').bind('click', function () {
	  // get the href id that we want to display 
	  // is this an a element? 
	  var display_id = $(this).attr('href');
	  if (display_id == undefined) {
	    var display_id = $(this).children('a').attr('href');
	  }
	  // we need to get the container that this drawer is in
	  var parent = $(this).parents('.ui-tabs-panel').attr('id');	  
	  // hide current active display
	  $('#'+parent+' .display.active').removeClass('active').hide();
	  // set any drawers to not active
	  $('#'+parent+' .drawers li.active').removeClass('active');
	  // make this drawer active
	  $(this).addClass('active');
	  // make the correct display active
	  $(display_id).addClass('active').show();
   });
   
 });
 
 
 /**
  * we need to hide any form elements that were replaced by the media browser form
  * activate the add button, and hide the browser
  */
 $(document).ready(function () {
   $('.media.replace').hide();
   $('.media.browser.activation').each(function () {
     $(this).next('.media.browser').hide();
     $(this).click(function () {
       $(this).next('.media.browser').slideDown('slow');
       $(this).slideUp();
     });
   }); 
   
 });
 
 
 
 
 /**
  * This handles passing the current file data from the media browser
  * to the formater function and returns the correct form elements
  */
$(document).ready(function () {
 
 
});
 
  
 