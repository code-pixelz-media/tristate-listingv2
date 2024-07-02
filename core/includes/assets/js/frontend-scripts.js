jQuery(document).on("change", "#map-layer-data", function () {
  jQuery("#save_map_layer").text($(this).val());
});

jQuery(document).ready(function ($) {
  // // for price range slider
  // $("#price-range").slider({
  // 	range: true,
  // 	min: $("#price-range").data('min'),//get min val
  // 	max: $("#price-range").data('max'),//get max val
  // 	values: [$("#price-range").data('min'), $("#price-range").data('max')],//postion slider val
  // 	step: 1,
  // 	slide: function (event, ui) {
  // 	  $("#priceRange").val("$" + ui.values[0] + " - $" + ui.values[1]);
  // 	  $("#price-range-min").val('$'+ui.values[0].toLocaleString());
  // 	  $("#price-range-max").val('$'+ui.values[1].toLocaleString());

  // 	},
  // 	change: function (event, ui) {
  // 	  $("#price-range-selected").val(ui.values[0] + "-" + ui.values[1]);
  // 	  $("#search-by-text").trigger('keyup');

  // 	},

  //   });

  // for price per sf slider
//   jQuery('.trimmed-control').on('click', function() {
//     // Toggle the next ul element
//     jQuery(this).closest('h4').next('.ul-content').toggle();
// });

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
  $(document).on("click", ".key-show", function () {
    $("<p>" + $(this).data("text") + "</p>").insertAfter($(this));
    $(this).text("Hide").addClass("hide-now").removeClass("key-show");
  });

  $(document).on("click", ".hide-now", function () {
    $(this).text("Show");
    $(this).next("p").remove();
    $(this).removeClass("hide-now").addClass("key-show");
  });

  $(document).on("click", ".description-container .desc-more", function (e) {
    e.preventDefault();
    var container = $(this).closest(".description-container");
    container.find(".trimmed-desc").hide();
    container.find(".full-desc").show();
  });

  $(document).on("click", ".description-container .desc-less", function (e) {
    e.preventDefault();
    var container = $(this).closest(".description-container");
    container.find(".full-desc").hide();
    container.find(".trimmed-desc").show();
  });

  //   $(document).on('change','#manage-sliders11', function(){
  //     var maxp = $(this).data('maxprice'),
  //         maxr = $(this).data('maxrent'),
  //         maxs = $(this).data('maxsize'),
  //         //price-range===> property price price-range2==>size price-range3==>rent
  //         crs  = $(this).data('current');

  //         console.log(crs);

  //       //when price is changed need to change on rent and size
  //       if(crs == 'price-range'){

  //         // for rent range slider
  //         $( "#price-range3" ).slider( "option", "max", maxr );
  //         $("#rent-range-max").val('$'+maxr.toLocaleString());
  //         //for size range slider
  //         $( "#price-range2" ).slider( "option", "max", maxs );
  //         $("#size-range-max").val('$'+maxs.toLocaleString());
  //       }
  //       //when size is changed need to change on price and rent
  //       if(crs =='price-range2'){
  //         //for price
  //         $("#price-range" ).slider( "option", "max", maxp );
  //         $("#price-range-max").val('$'+maxp.toLocaleString());
  //         // for rent
  //         $("#price-range3" ).slider( "option", "max", maxr );
  //         $("#rent-range-max").val('$'+maxr.toLocaleString());
  //       }
  //       // when rent is changed need to change on price and size
  //       if(crs =='price-range3'){
  //         //for price
  //         $( "#price-range" ).slider( "option", "max", maxp );
  //         $("#price-range-max").val('$'+maxp.toLocaleString());
  //         //for size
  //         $( "#price-range2" ).slider( "option", "max", maxs );
  //         $("#size-range-max").val('$'+maxs.toLocaleString());
  //       }

  //       if(crs == 'all'){
  //         //for price
  //         $("#price-range" ).slider( "option", "max", maxp );
  //         $("#price-range-max").val('$'+maxp.toLocaleString());
  //         // for rent
  //         $("#price-range3" ).slider( "option", "max", maxr );
  //         $("#rent-range-max").val('$'+maxr.toLocaleString());
  //         //for size
  //         $( "#price-range2" ).slider( "option", "max", maxs );
  //         $("#size-range-max").val(maxs.toLocaleString() + ' SF');
  //       }
  // });

  // $(document).on('keyup','#search-by-text' , function(){

  $("#search-by-text1123").on("keyup", function () {
    var priceArray = [],
      pricesfArray = [],
      minsizeArray = [],
      maxsizeArray = [];
    $(".propertylisting-content").each(function () {
      if ($(this).css("display") === "block") {
        var price = $(this).data("price"),
          pricesf = $(this).data("pricesf"),
          minsize = $(this).data("minsize"),
          maxsize = $(this).data("maxsize");

        if (price) {
          priceArray.push(price);
        }
        if (pricesf) {
          pricesfArray.push(pricesf);
        }
        if (minsize) {
          minsizeArray.push(minsize);
        }
        if (maxsize) {
          maxsizeArray.push(maxsize);
        }
      }
    });

    var maxPrice =
        (max = priceArray.reduce(
          (max, current) => (current > max ? current : max),
          -Infinity
        )) !== -Infinity
          ? max
          : $("#price-range").data("max"),
      maxPriceSf =
        (max = pricesfArray.reduce(
          (max, current) => (current > max ? current : max),
          -Infinity
        )) !== -Infinity
          ? max
          : $("#price-range3").data("max"),
      minSize =
        (max = minsizeArray.reduce(
          (max, current) => (current > max ? current : max),
          -Infinity
        )) !== -Infinity
          ? max
          : 5,
      maxSize =
        (max = maxsizeArray.reduce(
          (max, current) => (current > max ? current : max),
          -Infinity
        )) !== -Infinity
          ? max
          : $("#price-range2").data("max");
    console.log("MAX PRICE:" + maxPrice);
    console.log("MAX SF:" + maxPriceSf);
    console.log("MAX SIZE:" + maxSize);

    jQuery("#price-range").slider("values", [0, maxPrice]);

    //price
    // $( "#price-range" ).slider( "option", "max", maxPrice );
    // $('#price-range-max').val('$' + maxPrice.toLocaleString());
    // // rent
    // $( "#price-range3" ).slider( "option", "max", maxPriceSf );
    // $('#rent-range-max').val('$' + maxPriceSf.toLocaleString());
    // // size
    // $( "#price-range2" ).slider( "option", "max", maxSize );
    // $('#size-range-max').val( maxSize.toLocaleString() + ' SF');

    // var priceMinMax =  $('#price-range-selected').val().split('-').map(Number);
    // $('.propertylisting-content').each(function() {

    // 	var price = parseFloat($(this).data('price'));

    // 	var isBetweenMaxMinPrice = (price >= priceMinMax[0]) && (price <= priceMinMax[1]);
    // 	if(!isBetweenMaxMinPrice){
    // 		$(this).css('display','none');
    // 	}else{
    // 		$(this).css('display','block');
    // 	}
    // });
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
  // jQuery("#save_map_layer").click(function () {

  jQuery("body").on("click", "#save_map_layer", function (e) {
    //alert('test');
    e.preventDefault();
    jQuery("#tcr-popup-wrapper").show();
    jQuery(".tcr-popup-overlay").show(); // show the overlay
    jQuery("body").addClass("popupshown");
    // jQuery('body').addClass('popup-open');
  });
  // Close the popup

  jQuery("body").on("click", "#tcr-popup-close-button", function (e) {
    e.preventDefault();
    jQuery("#tcr-popup-wrapper").hide();
    jQuery("body").removeClass("popupshown");
    jQuery(".tcr-popup-overlay").hide(); // hide the overlay
    // jQuery('body').removeClass('popup-open');
  });
});

jQuery(function () {
  // jQuery("#price-range").slider({
  //   range: true,
  //   min: jQuery("#price-range").data('min'),//get min val
  //   max: jQuery("#price-range").data('max'),//get max val
  //   values: [jQuery("#price-range").data('min'), jQuery("#price-range").data('max')],//postion slider val
  //   step: 1,
  //   slide: function (event, ui) {
  //     jQuery("#priceRange").val("$" + ui.values[0] + " - $" + ui.values[1]);
  //     jQuery("#price-range-min").val('$'+ui.values[0].toLocaleString());
  //     jQuery("#price-range-max").val('$'+ui.values[1].toLocaleString());

  //   },
  //   change: function (event, ui) {
  //     jQuery("#price-range-selected").val(ui.values[0] + "-" + ui.values[1]);
  //     jQuery("#search-by-text").trigger('keyup');

  //   },

  // });

  var prmin = jQuery("#price-range-min"),
    prmax = jQuery("#price-range-max"),
    rrmin = jQuery("#rent-range-min"),
    rrmax = jQuery("#rent-range-max"),
    srmax = jQuery("#size-range-max"),
    srmin = jQuery("#size-range-min");

  function rangeInputsKeydown(el, valsEl, sliderId, defaults) {
    var timer;
    el.on("keyup", function (event) {
      // if (event.keyCode === 9 || event.keyCode === 32 || event.keyCode === 8) {
      //   return;
      // }
      clearTimeout(timer);
      var prmin = parseFloat(
        jQuery("#" + valsEl + "-min")
          .val()
          .replace(/[^0-9]/g, "")
      );
      var prmax = parseFloat(
        jQuery("#" + valsEl + "-max")
          .val()
          .replace(/[^0-9]/g, "")
      );
      var prminy = isNaN(prmin) ? defaults[0] : prmin;
      var prmaxy = isNaN(prmax) ? defaults[1] : prmax;
      timer = setTimeout(function () {
        // $("#price-range").slider("option", "max",prmaxy);
        // $("#price-range").slider("option", "min",prminy);

        jQuery("#" + sliderId).slider("values", [prminy, prmaxy]);
        jQuery("#search-by-text-new").trigger("keyup");
        jQuery();
      }, 250);
    });
  }

  // // for price ranges
  rangeInputsKeydown(prmin, "price-range", "price-range", [
    0,
    jQuery("#price-range").data("max"),
  ]);
  rangeInputsKeydown(prmax, "price-range", "price-range", [
    0,
    jQuery("#price-range").data("max"),
  ]);

  // for rent ranges
  rangeInputsKeydown(rrmin, "rent-range", "price-range3", [0, 200000]);
  rangeInputsKeydown(rrmax, "rent-range", "price-range3", [0, 200000]);

  // for size ranges
  rangeInputsKeydown(srmin, "size-range", "price-range2", [0, 25000]);
  rangeInputsKeydown(srmax, "size-range", "price-range2", [0, 25000]);

  // jQuery("#priceRange").val(
  //   "$" +
  //     jQuery("#price-range").slider("values", 0) +
  //     " - $" +
  //     jQuery("#price-range").slider("values", 1)
  // );
  // size range
  // jQuery("#price-range2").slider({
  //   range: true,
  //   min: jQuery("#price-range2").data('min'),//get min val
  //   max: jQuery("#price-range2").data('max'),//get max val
  //   values: [jQuery("#price-range2").data('min'), jQuery("#price-range2").data('max')],//postion slider val
  //   step:1,
  //   slide: function (event, ui) {

  //     jQuery("#priceRange2").val(
  //       "" + ui.values[0].toLocaleString() + " SF to " + ui.values[1].toLocaleString() + " SF "
  //     );
  //     jQuery("#size-range-min").val(ui.values[0].toLocaleString()+ ' SF');
  //     jQuery("#size-range-max").val(ui.values[1].toLocaleString() + " SF");
  //   },
  //   change: function (event, ui) {

  //     jQuery("#size-range-selected").val(ui.values[0] + "-" + ui.values[1]);
  //     var checkClear = jQuery("#size-range-selected").attr("data-clear");
  //     if(checkClear == "0"){jQuery("#size-range-selected").trigger("change");}
  //     jQuery('#search-by-text').trigger('keyup');

  //   },
  // });
  // jQuery("#priceRange2").val(
  //   jQuery("#price-range2").slider("values", 0) +
  //     " SF to " +
  //     jQuery("#price-range2").slider("values", 1) +
  //     " SF "
  // );

  // //for rent range
  // jQuery("#price-range3").slider({
  //   range: true,
  //   min: jQuery("#price-range3").data('min'),//get min val
  //   max: jQuery("#price-range3").data('max'),//get max val
  //   values: [jQuery("#price-range3").data('min'), jQuery("#price-range3").data('max')],
  //   step:1,
  //   slide: function (event, ui) {
  //     jQuery("#priceRange3").val("$" + ui.values[0].toLocaleString() + " - $" + ui.values[1].toLocaleString());
  //     jQuery("#rent-range-min").val("$" +ui.values[0].toLocaleString());
  //     jQuery("#rent-range-max").val("$" +ui.values[1].toLocaleString());
  //   },
  //   change: function (event, ui) {
  //     jQuery("#rent-range-selected").val(ui.values[0] + "-" + ui.values[1]);
  //     var checkClear = jQuery("#rent-range-selected").attr("data-clear");
  //     if(checkClear == "0"){jQuery("#rent-range-selected").trigger("change");}

  //   },
  // });
  // jQuery("#priceRange3").val(
  //   "$" +
  //     jQuery("#price-range3").slider("values", 0) +
  //     " - $" +
  //     jQuery("#price-range3").slider("values", 1)
  // );
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
      Thumbs: false,
      Toolbar: {
        display: {
          right: ["", "", "close"],
        },
      },
      buttons: ["", "", "", "close"],
    });
  }
});

//Select the column in filer page by niresh
jQuery(document).ready(function () {
  jQuery("#selectcolumn").on("change", function () {
    if ($("#selectcolumn").val() == 1) {
      jQuery(".property-list-wrapper .property-filter").addClass("column-one");
      jQuery(".property-list-wrapper .property-filter").removeClass(
        "column-two"
      );
      jQuery(".property-list-wrapper .property-filter").removeClass(
        "column-three"
      );
    }
    if ($("#selectcolumn").val() == 2) {
      jQuery(".property-list-wrapper .property-filter").addClass("column-two");
      jQuery(".property-list-wrapper .property-filter").removeClass(
        "column-one"
      );
      jQuery(".property-list-wrapper .property-filter").removeClass(
        "column-three"
      );
    }
    if ($("#selectcolumn").val() == 3) {
      jQuery(".property-list-wrapper .property-filter").addClass(
        "column-three"
      );
      jQuery(".property-list-wrapper .property-filter").removeClass(
        "column-one"
      );
      jQuery(".property-list-wrapper .property-filter").removeClass(
        "column-two"
      );
    }
  });
});

// More filters for state page
jQuery(document).ready(function () {
  jQuery("#more-filter-content").hide();
  jQuery("#state-more-filter").on("click", function () {

  
    jQuery("#more-filter-content").toggleClass("ts-adv-show");
    jQuery(".close-icon").toggleClass("closeactive");
    jQuery(".slider-box").appendTo("#more-filter-content");

    if (jQuery("#more-filter-content").hasClass("ts-adv-show")) {
      jQuery(".close-icon").show();
    } else {
      jQuery(".close-icon").hide();
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

      closeIcon.addEventListener("click", function () {
        button.click();
        closeIcon.remove();
      });
    }
  }
  if (jQuery(document).find(".filter-wrapper").hasClass("ts-state-page")) {
    jQuery('.propertylisting-content').each(function(){
      jQuery(this).wrap('<a href="'+ jQuery(this).data('permalink')+'"></a>');
    });
    var moreFilterButton = document.getElementById("state-more-filter");
    moreFilterButton.addEventListener("click", function () {
      handleFilterButtonClick();
    });
  }
});
