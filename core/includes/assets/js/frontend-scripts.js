jQuery(document).on('change','#map-layer-data', function(){
  jQuery("#save_map_layer").text($(this).val());

});

jQuery(document).ready(function($){

	
	$(document).find('.tri-more-info').attr('target', '_blank');
	  
	// for price per sf slider 
  $(document).on("click", ".trimmed-control", function () {
    var $this = $(this);
    var $ulContent = $this.closest('h4').next('.ul-content');
    
    // Toggle the plus/minus
    if ($this.text() === "+") {
        $this.text("-");
    } else {
        $this.text("+");
    }
  
    // Show/hide the ul content
    $ulContent.toggle();
  
  });
  
   $(document).on('click','.key-show', function(){
       $('<p>'+$(this).data('text') +'</p>').insertAfter($(this));
       $(this).text('Hide').addClass('hide-now').removeClass('key-show');
   });
   
   $(document).on('click','.hide-now', function(){
      $(this).text('Show');
      $(this).next('p').remove();
      $(this).removeClass('hide-now').addClass('key-show');
      
   });
   
  $(document).on('click', '.description-container .desc-more', function(e) {
    e.preventDefault();
    var container = $(this).closest('.description-container');
    container.find('.trimmed-desc').hide();
    container.find('.full-desc').show();
  });

  $(document).on('click', '.description-container .desc-less', function(e) {
      e.preventDefault();
      var container = $(this).closest('.description-container');
      container.find('.full-desc').hide();
      container.find('.trimmed-desc').show();
  });



$('#search-by-text1123').on('keyup', function(){

    var priceArray =[] , pricesfArray=[] , minsizeArray=[] , maxsizeArray= [] ;
    $('.propertylisting-content').each(function() {
  
        if ($(this).css('display') === 'block') {
            var price = $(this).data('price'),
            pricesf = $(this).data('pricesf'),
            minsize = $(this).data('minsize'),
            maxsize = $(this).data('maxsize');
           
            if(price){
              priceArray.push(price);
            }
            if(pricesf){
              pricesfArray.push(pricesf);
            }
            if(minsize ){
              minsizeArray.push(minsize);
            }
            if(maxsize){
              maxsizeArray.push(maxsize);
            }
         
        }
    });
  
    var maxPrice = ((max = priceArray.reduce((max, current) => (current > max ? current : max), -Infinity)) !== -Infinity ? max : $( "#price-range" ).data('max')),
    maxPriceSf =  ((max = pricesfArray.reduce((max, current) => (current > max ? current : max), -Infinity)) !== -Infinity ? max : $( "#price-range3" ).data('max')),
    minSize  = ((max = minsizeArray.reduce((max, current) => (current > max ? current : max), -Infinity)) !== -Infinity ? max : 5),
    maxSize  = ((max = maxsizeArray.reduce((max, current) => (current > max ? current : max), -Infinity)) !== -Infinity ? max : $( "#price-range2" ).data('max'));

    
	jQuery("#price-range").slider("values", [0, maxPrice]);
   

    
		
  });
  
});
   

jQuery(document).ready(function () {
  var multipleSelects = jQuery(".js-example-basic-multiple").select2({
    theme: "default",
    tags: "true",
  });

  jQuery("#filter-clear").click(function () {
    multipleSelects.val(null).trigger("change");
    multipleSelects.each(function (index, element) {
      jQuery(element)
        .find("option")
        .each(function () {
          jQuery(this).prop("disabled", false);
        });
    });
    multipleSelects.destroy();

    if (jQuery("#tristate-input").val() !== "") {
      jQuery("#tristate-input").val("").trigger("keyup");
    }
  });



  jQuery("#js-example-basic-multiple option").attr("data-id");

  jQuery(".tcr_tab_content").hide();
  jQuery(".tcr_tab_content:first").show();

  /* if in tab mode */
  jQuery("ul.tcr_tabs li").click(function () {
    jQuery(".tcr_tab_content").hide();
    var activeTab = jQuery(this).attr("rel");
    jQuery("#" + activeTab).fadeIn();

    jQuery("ul.tcr_tabs li").removeClass("active");
    jQuery(this).addClass("active");

    jQuery(".tab_drawer_heading").removeClass("d_active");
    jQuery(".tab_drawer_heading[rel^='" + activeTab + "']").addClass(
      "d_active"
    );
  });
  /* if in drawer mode */
  jQuery(".tab_drawer_heading").click(function () {
    jQuery(".tcr_tab_content").hide();
    var d_activeTab = jQuery(this).attr("rel");
    jQuery("#" + d_activeTab).fadeIn();

    jQuery(".tab_drawer_heading").removeClass("d_active");
    jQuery(this).addClass("d_active");

    jQuery("ul.tcr_tabs li").removeClass("active");
    jQuery("ul.tcr_tabs li[rel^='" + d_activeTab + "']").addClass("active");
  });

  /* Extra class "tab_last" 
   to add border to right side
   of last tab */
  jQuery("ul.tcr_tabs li").last().addClass("tab_last");

  var slider = new Swiper(".gallery-slider", {
    slidesPerView: 1,
    centeredSlides: false,
    loop: true,
    loopedSlides: 5,
    navigation: {
      nextEl: ".swiper-button-next0",
      prevEl: ".swiper-button-prev0",
    },
  });

  var thumbs = new Swiper(".gallery-thumbs", {
    slidesPerView: "auto",
    spaceBetween: 10,
    centeredSlides: false,
    loopedSlides: 5,
    loop: true,
    slideToClickedSlide: true,
  });
  slider.controller.control = thumbs;
  thumbs.controller.control = slider;
});

jQuery(document).ready(function () {
  // Open the popup
  jQuery("#save_map_layer").click(function () {
    //alert('test');
    jQuery("#tcr-popup-wrapper").show();
    jQuery(".tcr-popup-overlay").show(); // show the overlay
    jQuery("body").addClass("popupshown");
    // jQuery('body').addClass('popup-open');
  });
  // Close the popup
    jQuery("body").on("click", "#tcr-popup-close-button", function(e) {
      e.preventDefault();
    jQuery("#tcr-popup-wrapper").hide();
    jQuery("body").removeClass("popupshown");
    jQuery(".tcr-popup-overlay").hide(); // hide the overlay
    // jQuery('body').removeClass('popup-open');
  });
});



jQuery(function () {


  var prmin = jQuery("#price-range-min"),
   prmax = jQuery("#price-range-max"),
   rrmin = jQuery("#rent-range-min"),
   rrmax = jQuery("#rent-range-max"),
   srmax = jQuery("#size-range-max"),
   srmin = jQuery("#size-range-min");


  function rangeInputsKeydown(el,valsEl,sliderId,defaults){
    var timer;
    el.on('keyup', function(event) {

      clearTimeout(timer);
      var prmin = parseFloat(jQuery("#" + valsEl +"-min").val().replace(/[^0-9]/g, '')) ;
      var  prmax = parseFloat(jQuery("#" + valsEl +"-max").val().replace(/[^0-9]/g, ''));
      var prminy = isNaN(prmin) ? defaults[0] : prmin;
      var prmaxy =  isNaN(prmax) ? defaults[1] : prmax;
      timer = setTimeout(function() {
       
        
        jQuery("#" + sliderId).slider("values", [prminy, prmaxy]);
        jQuery("#search-by-text-new").trigger('keyup');
        jQuery()
    }, 250);
    });
  }
  
  // // for price ranges
  rangeInputsKeydown(prmin,'price-range','price-range',[0,jQuery("#price-range").data('max')]);
  rangeInputsKeydown(prmax,'price-range','price-range',[0,jQuery("#price-range").data('max')]);

  // for rent ranges
  rangeInputsKeydown(rrmin,'rent-range','price-range3',[0,200000]);
  rangeInputsKeydown(rrmax,'rent-range','price-range3',[0,200000]);

  // for size ranges
  rangeInputsKeydown(srmin,'size-range','price-range2',[0,25000]);
  rangeInputsKeydown(srmax,'size-range','price-range2',[0,25000]);

  
});

// Filter page mobile drawer menu
jQuery(document).ready(function () {
  jQuery("#menu-btn").click(function () {
    var isOpen = jQuery(".left-content").hasClass("open");

    // Check if menu is already open
    if (!isOpen) {
      jQuery(".left-content").toggleClass("open");
      jQuery(".left-content").animate({
        right: "0",
      });
    } else {
      jQuery(".left-content").toggleClass("open");
      jQuery(".left-content").animate({
        right: "-300px",
      });
    }

    jQuery("#menu-btn i").toggleClass("fa-angle-left fa-angle-right");
  });

  jQuery("#menu__close").click(function () {
    jQuery(".left-content").removeClass("open");
    jQuery(".left-content").animate({
      right: "-300px",
    });

    jQuery("#menu-btn i")
      .removeClass("fa-angle-right")
      .addClass("fa-angle-left");
  });
});

// spaces table accordion spacestoggle

jQuery(document).ready(function ($) {
  jQuery("#spacestoggle").on("click", function () {
    jQuery(".spaces-child-content").toggleClass("spacechildshow");
    jQuery("#spacestoggle i").toggleClass("fa-chevron-down fa-chevron-up");
  });
if ($('[data-fancybox="gallery"]').length > 0) {
  Fancybox.bind('[data-fancybox="gallery"]', {
    Thumbs : false,
    Toolbar: {
      display: {
        right: ["", "", "close"],
      },
    },
    buttons : [
      '',
      '',
      '',
      'close'
  ]
  });
}
});

//Select the column in filer page by niresh
jQuery(document).ready(function () {
jQuery("#selectcolumn").on("change", function(){
  if($('#selectcolumn').val()== 1)
  { 
    jQuery(".property-list-wrapper .property-filter").addClass('column-one');
    jQuery(".property-list-wrapper .property-filter").removeClass('column-two');
    jQuery(".property-list-wrapper .property-filter").removeClass('column-three');
  }
  if($('#selectcolumn').val()== 2)
  { 
    jQuery(".property-list-wrapper .property-filter").addClass('column-two');
    jQuery(".property-list-wrapper .property-filter").removeClass('column-one');
    jQuery(".property-list-wrapper .property-filter").removeClass('column-three');
  }
  if($('#selectcolumn').val()== 3)
  { 
    jQuery(".property-list-wrapper .property-filter").addClass('column-three');
    jQuery(".property-list-wrapper .property-filter").removeClass('column-one');
    jQuery(".property-list-wrapper .property-filter").removeClass('column-two');
  }
});
});

// More filters for state page
jQuery(document).ready(function () {
  jQuery("#more-filter-content").insertAfter(".Filterform > .MuiBox-root");
  jQuery('#more-filter-content').hide();
  jQuery("#state-more-filter").on("click", function () {
  jQuery('#more-filter-content').toggleClass("ts-adv-show");
  jQuery('.close-icon').toggleClass('closeactive');
  jQuery('.slider-box').appendTo('#more-filter-content');

  if(jQuery('#more-filter-content').hasClass('ts-adv-show')) {
    jQuery('.close-icon').show();
  }
  else {
    jQuery('.close-icon').hide();
  }

  });
  jQuery("#state-more-filter").trigger('click');
  function handleFilterButtonClick() {
    var button = document.getElementById("state-more-filter");
    var closeIcon = document.querySelector(".close-icon");

    if (!closeIcon) {

      closeIcon = document.createElement("span");
      closeIcon.classList.add("close-icon");
      closeIcon.innerHTML = "&#x2715;"; 

      button.insertAdjacentElement("afterend", closeIcon);

      closeIcon.addEventListener("click", function() {
        button.click();
        closeIcon.remove();
      });
    }
  }
  if(jQuery(document).find('.filter-wrapper').hasClass('ts-state-page')){
  var moreFilterButton = document.getElementById("state-more-filter");
  moreFilterButton.addEventListener("click", function() {

    handleFilterButtonClick();
  });
  
  jQuery('.propertylisting-content').each(function(){
   
    jQuery(this).on('click',function(e){
      e.preventDefault();
      window.open(jQuery(this).data('permalink'), '_blank').focus();
     
    });
    
  });
}
if(!jQuery(document).find('.filter-wrapper').hasClass('ts-state-page')){
  $('a[target=""]').attr('target', '_blank');
}


});

if (jQuery(window).width() < 767 ) {

  jQuery(".main-filter-page .search-by-text-new.state-page-keyword").insertAfter(".elementor-element-dffec4d").addClass("filter-fixed-main-filter");
  
}