jQuery(document).ready(function() {


  jQuery('.js-example-basic-multiple').select2({
    theme: "classic",
    tags: "true",
  });

  jQuery('#js-example-basic-multiple option').attr('data-id')
  
  jQuery(".tcr_tab_content").hide();
  jQuery(".tcr_tab_content:first").show();

/* if in tab mode */
  jQuery("ul.tcr_tabs li").click(function() {
  
    jQuery(".tcr_tab_content").hide();
    var activeTab = jQuery(this).attr("rel"); 
    jQuery("#"+activeTab).fadeIn();		
  
    jQuery("ul.tcr_tabs li").removeClass("active");
    jQuery(this).addClass("active");

  jQuery(".tab_drawer_heading").removeClass("d_active");
  jQuery(".tab_drawer_heading[rel^='"+activeTab+"']").addClass("d_active");
  
  });
/* if in drawer mode */
jQuery(".tab_drawer_heading").click(function() {
    
    jQuery(".tcr_tab_content").hide();
    var d_activeTab = jQuery(this).attr("rel"); 
    jQuery("#"+d_activeTab).fadeIn();
  
  jQuery(".tab_drawer_heading").removeClass("d_active");
    jQuery(this).addClass("d_active");
  
  jQuery("ul.tcr_tabs li").removeClass("active");
  jQuery("ul.tcr_tabs li[rel^='"+d_activeTab+"']").addClass("active");
  });


/* Extra class "tab_last" 
   to add border to right side
   of last tab */
jQuery('ul.tcr_tabs li').last().addClass("tab_last");

   
var slider = new Swiper(".gallery-slider", {
  slidesPerView: 1,
  centeredSlides: true,
  loop: true,
  loopedSlides: 5, 
  navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev"
  }
});

var thumbs = new Swiper(".gallery-thumbs", {
  slidesPerView: "auto",
  spaceBetween: 10,
  centeredSlides: false,
  loopedSlides: 4, 
  loop: true,
  slideToClickedSlide: true,
});
slider.controller.control = thumbs;
thumbs.controller.control = slider;

})

jQuery(function() {
  jQuery("#price-range").slider({range: true, min: 0, max: 200000, values: [0, 200000], slide: function(event, ui) {jQuery("#priceRange").val("$" + ui.values[0] + " - $" + ui.values[1]);}
  });
  jQuery("#priceRange").val("$" + jQuery("#price-range").slider("values", 0) + " - $" + jQuery("#price-range").slider("values", 1));

  jQuery("#price-range2").slider({range: true, min: 0, max: 200000, values: [0, 200000], slide: function(event, ui) {jQuery("#priceRange2").val("$" + ui.values[0] + " - $" + ui.values[1]);}
});
jQuery("#priceRange2").val("$" + jQuery("#price-range2").slider("values", 0) + " - $" + jQuery("#price-range2").slider("values", 1));

jQuery("#price-range3").slider({range: true, min: 0, max: 200000, values: [0, 200000], slide: function(event, ui) {jQuery("#priceRange3").val("$" + ui.values[0] + " - $" + ui.values[1]);}
});
jQuery("#priceRange3").val("$" + jQuery("#price-range3").slider("values", 0) + " - $" + jQuery("#price-range3").slider("values", 1));
  

});