<?php
// Add a function to check user access on template redirect

//add_action('wp_head', 'drt_restrict_page_access');
//add_action('wp_footer', 'drt_display_notice_after_footer');

add_action('template_redirect', 'drt_restrict_page_access', 11);

function drt_restrict_page_access()
{
  // Check if the current page is the page with ID 80575
  if (is_page(80575)) {
    // Check if the user is not logged in
    if (!is_user_logged_in()) {
      // Check if the transient exists
      $notice_transient = get_transient('drt_page_access_notice');

      // If transient does not exist, generate the notice and set the transient
      if (false === $notice_transient) {
        // Generate the notice
        $notice = '<div style="text-align:center;margin: 100px 20px 40px 20px; padding: 10px; background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;">Please <a href="' . wp_login_url() . '">login</a> to access this page.</div>';

        // Set transient to store the notice for 1 hour (3600 seconds)
        set_transient('drt_page_access_notice', $notice, 3600);
      } else {
        // If transient exists, retrieve the notice
        $notice = $notice_transient;
      }

      // Output the notice
      get_header();
      echo $notice;
      get_footer();

      // Stop further execution
      exit;
    }
  }
}


function get_property_broker_title($property_id) {

  $broker_id = get_post_meta($property_id, '_buildout_broker_id', true);
  if(!empty($broker_id)){
    
    global $wpdb;
    
    
  }





  // Step 3: Use WP_Query to find the broker post with the user_id meta key
  $args = array(
      'post_type' => 'brokers',
      'meta_query' => array(
          array(
              'key' => 'user_id',
              'value' => $user_id,
              'compare' => '='
          )
      ),
      'posts_per_page' => 1
  );

  $query = new WP_Query($args);

  if ($query->have_posts()) {
      $query->the_post();
      $broker_title = get_the_title();
      wp_reset_postdata(); // Reset the global post object
      return $broker_title;
  } else {
      return null; // Broker post not found
  }
}
function meta_of_api_sheet($propid,$metaKey){

  $type = get_post_meta($propid,'_import_from',true);
  
  if($type == 'sheets'){
  
  }
  
  $g_sheet_meta = get_post_meta($propid,'_gsheet_'.$metaKey,true);
  $buildout_meta = get_post_meta($propid,'_buildout_'.$metaKey,true);
  return !empty($g_sheet_meta) ? $g_sheet_meta : (!empty($buildout_meta) ? $buildout_meta : '');
}

function tristate_get_marker_data($ID){

  $sale_marker = TRISTATECRLISTING_PLUGIN_URL . '/assets/img/sale.png';
  $lease_marker = TRISTATECRLISTING_PLUGIN_URL . '/assets/img/lease.png';
  $title = meta_of_api_sheet($ID, 'sale_listing_web_title');
  $buildout_lease = meta_of_api_sheet($ID, 'lease');
  $buildout_sale = meta_of_api_sheet($ID, 'sale');
  $streets = meta_of_api_sheet($ID, 'cross_street') ;
  $state = meta_of_api_sheet($ID, 'state');
  $zip = meta_of_api_sheet($ID, 'zip');
  $city = meta_of_api_sheet($ID, 'city');
  $subtitle = implode(', ', array_filter(array($streets, $city, $state, $zip), 'strlen'));
  $address = meta_of_api_sheet($ID, 'address');
  $county = meta_of_api_sheet($ID, 'county');
  $country_code = meta_of_api_sheet($ID, 'country_code');
  $address_c = implode(', ', array_filter(array($county, $country_code, ), 'strlen'));
  $image = false;
  if ($photos = get_post_meta($ID, '_buildout_photos', true)) {
      $photo = reset($photos);
      $image = $photo->formats->thumb ?? '';
  }

  $marker_img = ($buildout_lease == '1' && $buildout_sale == '1') ? $lease_marker :
                (($buildout_lease == '1') ? $lease_marker :
                (($buildout_sale == '1') ? $sale_marker : false));
                
  
      
  $type = ($buildout_lease == '1' && $buildout_sale == '1') ? 'FOR LEASE' :
          (($buildout_lease == '1') ? 'FOR LEASE' :
          (($buildout_sale == '1') ? 'FOR SALE' : false));
          
    if($buildout_lease == '1' && $buildout_sale == '1' ){
      $selected_array = isset($_POST['selected_type']) ? $_POST['selected_type'] : array();
      $selected_string = implode(', ', $selected_array);
      if(!empty($selected_array)){
        if($selected_string=='for Lease') {
          $marker_img = $lease_marker;
          $type= 'FOR LEASE';
        }
        if($selected_string=='for Sale') {
          $marker_img = $sale_marker;
          $type= 'FOR SALE';
        }
      }

    }

  $m_d = [
    'lat' => get_post_meta($ID, '_buildout_latitude', true),
    'long' => get_post_meta($ID, '_buildout_longitude', true),
    'post_id' => $ID,
    'marker_image' => $marker_img,
    'popup_data' => [
        'title' => $title,
        'sub_title' => [
            'address_a' => $address,
            'address_b' => $subtitle,
            'address_c' => $address_c,
        ],
        'type' => $type,
        'image' => $image,
        'link' => get_the_permalink($ID)
    ]
  ];
  
  return $m_d;
}


function get_rent_minmax($type="min", $formatted=true) {
  global $wpdb;
  
  $max_rent= $wpdb->get_var("
  SELECT MAX(CAST(pm.meta_value AS UNSIGNED)) 
  FROM $wpdb->postmeta pm
  INNER JOIN $wpdb->posts p ON pm.post_id = p.ID
  WHERE pm.meta_key = '__gsheet__monthly_rent'
  AND p.post_type = 'properties'
");


  $formatted_max_val = number_format($max_rent);
  $formatted_min_val = '$0'; 
  
  if($formatted){
  
    $retval= $type == 'min' ? $formatted_min_val : '$' .$formatted_max_val;
    
  }else{
  
    $retval= $type == 'min' ? (int) 0 :(int) $max_rent;
    
  }
  
  return $retval;



}

function get_size_minmax($type="min", $formatted=true) {
  global $wpdb;
  
  $max_size= $wpdb->get_var("
  SELECT MAX(CAST(pm.meta_value AS UNSIGNED)) 
  FROM $wpdb->postmeta pm
  INNER JOIN $wpdb->posts p ON pm.post_id = p.ID
  WHERE pm.meta_key = '_gsheet__max_size_fm'
  AND p.post_type = 'properties'
");

 
  $formatted_max_val = number_format($max_size);
  $formatted_min_val = '0 SF'; 
  
  if($formatted){
  
    $retval= $type == 'min' ? $formatted_min_val :  $formatted_max_val.' SF';
    
  }else{
  
    $retval= $type == 'min' ? (int) 0 :(int) $max_size;
    
  }
  
  return $retval;

}


// for getting price 
function get_price_minmax($type="min", $formatted=true) {
  global $wpdb;

  $max_price = $wpdb->get_var("
  SELECT MAX(CAST(pm.meta_value AS UNSIGNED)) 
  FROM $wpdb->postmeta pm
  INNER JOIN $wpdb->posts p ON pm.post_id = p.ID
  WHERE pm.meta_key = '_buildout_sale_price_dollars'
  
  AND p.post_type = 'properties'
");

  $formatted_max_price = number_format($max_price);
  $formatted_min_price = '$0'; 
  
  if($formatted){
  
    $retval= $type == 'min' ? $formatted_min_price : '$' .$formatted_max_price;
    
  }else{
  
    $retval= $type == 'min' ? (int) 0 :(int) $max_price;
    
  }
  
  return $retval;

}


function __total()
{
  global $wpdb;
  $post_type = 'properties';
  $query = "SELECT COUNT(ID) as count 
            FROM $wpdb->posts 
            LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key = '_buildout_proposal')
            WHERE $wpdb->posts.post_type = %s 
            AND $wpdb->posts.post_status = 'publish' 
            AND ($wpdb->postmeta.meta_value != '1' OR $wpdb->postmeta.meta_id IS NULL)";
  $results = $wpdb->get_results($wpdb->prepare($query, $post_type), ARRAY_A);
  return intval($results[0]['count']);
}

add_shortcode('TSC-inventory-pub', 'drt_shortcode');


function get_dynamic_post_meta($ID, $keys){

    if (!empty(get_post_meta($ID, $keys[0], true))) {
        return get_post_meta($ID, $keys[0], true);
    }elseif(!empty(get_post_meta($ID, $keys[1], true))){
        return get_post_meta($ID, $keys[1], true);
    }else{
      return '';
    }
}

add_shortcode('drt', 'drt_shortcode');

function drt_shortcode($atts)
{
  ob_start(); // Start output buffering
  $markers_data = [];
?>
<style>
/* .select2-container--default .select2-results__options[aria-live="assertive"] {
    min-height: 2em;
}

.select2-container--default .select2-results__option {
    transition: none !important;
    -webkit-transition: none !important;
    -moz-transition: none !important;
    -o-transition: none !important;
}
.select2-container--default .select2-results__option--loading {
    display: block; 
    height: 2em;    
} */
/* .select2-container--default .select2-results__option {
  top:0px;
    transition: none !important;
    -webkit-transition: none !important;
    -moz-transition: none !important;
    -o-transition: none !important;
}
.select2-container--default .select2-results__option--loading {
  display: none;
    height: 0px; 
    overflow: hidden !important; 
    margin: 0px !important; 
    padding: 0px !important; 
    visibility: hidden;
} */

.select2-results__option.select2-results__option--disabled.loading-results {
    padding: 0 !important;
}



    </style>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Initially hide both divs
      document.getElementById('for_sale').style.display = 'none';
      document.getElementById('for_lease').style.display = 'none';
      document.getElementById('sale_lease').style.display = 'none';
      //sale_lease

      // Function to update visibility based on checkbox states
      function updateVisibility() {
        var forSaleCheckbox = document.getElementById('type_for_sale');
        var forLeaseCheckbox = document.getElementById('type_for_lease');

        if (forSaleCheckbox.checked) {
          document.getElementById('sale_lease').style.display = 'block';
          document.getElementById('for_sale').style.display = 'block';
        } else {
          document.getElementById('for_sale').style.display = 'none';
        }

        if (forLeaseCheckbox.checked) {
          document.getElementById('sale_lease').style.display = 'block';
          document.getElementById('for_lease').style.display = 'block';
        } else {
          document.getElementById('for_lease').style.display = 'none';
        }

        if (!forSaleCheckbox.checked && !forLeaseCheckbox.checked) {
          document.getElementById('sale_lease').style.display = 'none';
        }
      }

      // Attach the event listeners to checkboxes
      document.getElementById('type_for_sale').addEventListener('change', updateVisibility);
      document.getElementById('type_for_lease').addEventListener('change', updateVisibility);

      // Call once on page load
      updateVisibility();
    });

    jQuery(document).ready(function($) {

      $('#_gsheet_listing_type input[type="checkbox"]').trigger('click');


            // search by tying startr
/*       document.getElementById('search-by-text').addEventListener('input', function() {
    var query = this.value.toLowerCase();
    var listings = document.querySelectorAll('.propertylisting-content');

    listings.forEach(function(listing) {
        var text = listing.innerText.toLowerCase();
        if (text.includes(query)) {
            listing.style.display = '';
        } else {
            listing.style.display = 'none';
        }
    });
}); */

const searchInput = document.getElementById('search-by-text');
    const propertyContent = document.getElementById('propertylisting-content');
    const resultCount = document.getElementById('tristate-result-count');

    // Function to update result count
    function updateResultCount(count, total) {
        resultCount.innerHTML = `Showing ${count} of ${total} Listing`;
    }

    // Function to filter property listings
    function filterListings() {
        const query = searchInput.value.toLowerCase();
        const listings = propertyContent.getElementsByClassName('propertylisting-content');
        let matchedCount = 0;

        for (let listing of listings) {
            const text = listing.innerText.toLowerCase();
            if (text.includes(query)) {
                listing.style.display = 'block';
                matchedCount++;
            } else {
                listing.style.display = 'none';
            }
        }

        updateResultCount(matchedCount, listings.length);
    }

    // Event listener for search input
    searchInput.addEventListener('input', filterListings);

    // Initially display all listings and set the result count
    filterListings();
      // search by typing end


      $('#filter-clear11').on('click', function() {
        //    alert('hello test');
        //$('#tristate-input').val("");
        $('#_gsheet_use, #tri_agents, #_gsheet_neighborhood, #_gsheet_zip,#_buildout_city, #_gsheet_state, #_gsheet_vented,#price-range2,#price-range,#price-range3').val(null).trigger('change');
        // Reset Select2 select by ID ('tri_agents')
        //$('#tri_agents,#_gsheet_use').val(null).trigger('change');
        $('#_gsheet_listing_type input[type="checkbox"]').prop('checked', true);
        // $("#for_sale,#for_lease").hide();
        // Reset ui-slider-range for price-range2
        $('#price-range .ui-slider-range,#price-range2 .ui-slider-range, #price-range3 .ui-slider-range').css({
          'left': '0%',
          'width': '100%'
        });


        $("#tristate-input").val("");
        // Remove disabled attributes from options
        $('#_gsheet_use option, #_gsheet_neighborhood option,#_gsheet_zip option,#_buildout_city option,#_gsheet_state option').each(function() {
          $(this).prop('disabled', false);
          $(this).removeAttr('aria-disabled');
        });
        var rangeHiddenFields = $("#price-range-selected,#rent-range-selected,#size-range-selected");
        rangeHiddenFields.attr("data-clear", "1");
        // $("#price-range").slider("values", [jQuery("#price-range").data('min'), jQuery("#price-range").data('max')]);
        // $("#price-range" ).slider( "option", "max",  jQuery("#price-range").data('max') );
        // $("#price-range" ).slider( "option", "min",  jQuery("#price-range").data('min') );
        // //  $("#priceRange").val("$0 - $4000000");
        // $("#price-range2").slider("values", [jQuery("#price-range2").data('min'), jQuery("#price-range2").data('max')]);
        // $("#price-range2" ).slider( "option", "max",  jQuery("#price-range2").data('max') );
        // $("#price-range2" ).slider( "option", "min",  jQuery("#price-range2").data('min') );
        // // $("#priceRange2").val("0 SF to 25000 SF");
        // $("#price-range3").slider("values", [jQuery("#price-range3").data('min'), jQuery("#price-range3").data('max')]);
        // $("#prie-range3" ).slider( "option", "max",  jQuery("#prie-range3").data('max') );
        // $("#prie-range3" ).slider( "option", "min",  jQuery("#prie-range3").data('min') );
        // $("#priceRange3").val("$0 - $200000");
        // $(".range-inputs").each(function() {
        //   $(this).val($(this).data("default"));
        // });
        $('#price-range-selected,#rent-range-selected,#size-range-selected').val("");


        // Perform any additional actions if needed
        var data = {
          action: 'live_search',
          clear: 'yes'
        };
        if (JSON.stringify(prevSearchParams) === JSON.stringify(data) && prevSearchResult) {
          $('#propertylisting-content').html(prevSearchResult); // Display previous result
          rangeHiddenFields.attr("data-clear", "0");
        } else {
          performAjaxRequest(data); // Make AJAX request
          rangeHiddenFields.attr("data-clear", "0");
        }
        
        // Set the handle positions for the sliders
        $('#price-range .ui-slider-handle, #price-range2 .ui-slider-handle, #price-range3 .ui-slider-handle').each(function() {
          $(this).css('left', '0%'); // Adjust this percentage as needed
        });
      });


      var prevBrokerIds = <?php echo !empty($_POST['broker_ids']) ? json_encode($_POST['broker_ids']) : 'null'; ?>;
      var prevSearchParams = null;
      var prevSearchResult = null;
      // Function to get selected listing types
      function getSelectedListingTypes() {
        var selectedTypes = [];
        $('#_gsheet_listing_type input[type="checkbox"]:checked').each(function() {
          selectedTypes.push($(this).val());
        });
        return selectedTypes;
      }

      function performAjaxRequest(data) {
        $.ajax({
          url: '<?php echo admin_url('admin-ajax.php'); ?>',
          type: 'POST',
          data: data,
          success: function(response) {

            $('#propertylisting-content').html(response);
            prevSearchResult = response; // Update previous search result
            prevSearchParams = data; // Update previous search parameters
          },
          error: function(error) {
            console.error("Error fetching properties:", error);
          }
        });
      }



      function drtInitializeSelect2(elementId, actionName) {
    var $selectElement = $('#' + elementId);

    $selectElement.select2({
        dropdownAutoWidth: true,
        language: {
            searching: function() {
                return ''; // No text is displayed during searching
            }
        },
        ajax: {
            transport: function(params, success, failure) {
                // Preparing data to be sent with the request
                var requestData = {
                    action: actionName,
                    broker_ids: $('#tri_agents').val(),
                    _buildout_city: $('#_buildout_city').val(),
                    _gsheet_use: $('#_gsheet_use').val(),
                    selected_type: getSelectedListingTypes(),
                    _gsheet_neighborhood: $('#_gsheet_neighborhood').val(),
                    _gsheet_zip: $('#_gsheet_zip').val(),
                    _gsheet_state: $('#_gsheet_state').val(),
                    _gsheet_vented: $('#_gsheet_vented').val(),
                    property_price_range: $('#price-range-selected').val(),
                    property_size_range: $('#size-range-selected').val(),
                    property_rent_range: $('#rent-range-selected').val(),
                };

                // Making the AJAX request
                $.ajax({
                  url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: requestData,
                    beforeSend: function() {
                        $('.select2-search--dropdown').addClass('hidden');
                    },
                    success: function(data) {
                        // Sorting and processing data
                        data.sort(function(a, b) {
                            return a.text.localeCompare(b.text);
                        });

                        data.forEach(function(option) {
                            option.disabled = !option.matched;
                        });

                        success({
                            results: data
                        });

                        $('.select2-search--dropdown').removeClass('select2-search--hide');
                    },
                    error: failure,
                    cache: true // Enable caching of AJAX requests
                });
            }
        }
    });


    // Close dropdown when clear icon is clicked
    $selectElement.on('select2:clearing', function(e) {
        setTimeout(() => $(this).select2('close'), 10);
    });

    // Also handle dropdown close when item is unselected
    $selectElement.on('select2:unselect', function(e) {
        setTimeout(() => $(this).select2('close'), 10);
    });

    // Debouncing AJAX requests
    function debounce(func, delay) {
        let debounceTimer;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => func.apply(context, args), delay);
        };
    }
}

      window.onload = function() {
    var ids = [
        'dropdown_zip_code', 'dropdown_city', 'dropdown_state', 'dropdown_uses',
        'dropdown_agents', 'dropdown_neighbourhoods', 'dropdown_vented', 'dropdown_listing_type'
    ];
    ids.forEach(function(id) {
        var element = document.getElementById(id);
        if (element) {
            element.style.display = 'block';
        }
    });

    var classes = [
        'dropdown_vented', 'dropdown_state', 'dropdown_city', 
        'dropdown_zip_code', 'dropdown_neighbourhoods', 'dropdown_uses', 'dropdown_agents'
    ];
    classes.forEach(function(className) {
        var elements = document.getElementsByClassName(className);
        while (elements.length > 0) {
            elements[0].parentNode.removeChild(elements[0]);
        }
    });
};
   


      /*       window.onload = function() {
          // Add blur effect to .left-content when window loads
          //$('.left-content').css('filter', 'blur(50%)');

          // Initialize tri_agents dropdown first
          const triAgentsData = getCachedData('tri_agents');
          if (triAgentsData) {
              initializeSelect2WithData('tri_agents', triAgentsData);
              // Remove blur effect from .left-content when tri_agents dropdown load   
             // $('.left-content').css('filter', 'none');
          } else {
              // If tri_agents data is not cached, initialize it with AJAX
              drtInitializeSelect2('tri_agents', 'get_agents_dropdown_cb');
          }

          // IDs of other elements to initialize with Select2
          const selectIds = [
              '_gsheet_use',
              '_gsheet_neighborhood',
              '_gsheet_state',
              '_gsheet_zip',
              '_buildout_city',
              '_gsheet_vented',
          ];

          // Check for cached data and initialize Select2 for other elements
          selectIds.forEach(id => {
              const cachedData = getCachedData(id);
              if (cachedData) {
                  initializeSelect2WithData(id, cachedData);
              } else {
                  const callbackName = getCallbackName(id);
                  drtInitializeSelect2(id, callbackName);
              }
          });

          // Attach a click event handler to .select2-selection to prevent default dropdown opening
          $('.select2-selection').on('click', function(event) {
              event.stopPropagation();
              event.preventDefault();
          }).trigger('click');

          // Close Select2 dropdowns
          closeSelect2('tri_agents');
          selectIds.forEach(closeSelect2);

          // Remove blur effect from .left-content when all AJAX requests are complete
          $(document).ajaxStop(function() {
            //   $('.left-content').css('filter', 'none');
          });

          // Function to get cached data for a given ID
          function getCachedData(id) {
              const data = localStorage.getItem(id);
              return data ? JSON.parse(data) : null;
          }

          // Function to initialize Select2 with cached data
          function initializeSelect2WithData(id, data) {
              $('#' + id).select2({
                  data: data
              });
          }

          // Function to determine the callback name based on the element ID
          function getCallbackName(id) {
              switch(id) {
                  case '_buildout_city':
                      return 'get_buildout_dropdown_cb';
                  case '_gsheet_state':
                      return 'get_state_dropdown_cb';
                  case '_gsheet_zip':
                      return 'get_zip_dropdown';
                  case '_gsheet_vented':
                      return 'get_vented_dropdown_cb';
                  case '_gsheet_neighborhood':
                      return 'get_neighborhood_dropdown_cb';
                  default:
                      return 'get_' + id.substr(1) + '_dropdown';
              }
          }

          // Function to close a specific Select2 dropdown
          function closeSelect2(id) {
              var $select = $('#' + id);
              if ($select.data('select2')) {
                  $select.select2('close');
              }
          }
      }; */




      /*     window.onload = function() {
    // IDs of elements to initialize with Select2
    const selectIds = [
      'tri_agents',
        '_gsheet_use',
        '_gsheet_neighborhood',
        '_gsheet_state',
        '_gsheet_zip',
        '_buildout_city',
        '_gsheet_vented',
        
    ];

    // Initialize Select2 on each element
    selectIds.forEach(id => {
        const callbackName = getCallbackName(id);
        drtInitializeSelect2(id, callbackName);
    });

    // Attach a click event handler to .select2-selection to prevent default dropdown opening
    $('.select2-selection').on('click', function(event) {
        event.stopPropagation();
        event.preventDefault();
    }).trigger('click');

    // Close Select2 dropdowns
    selectIds.forEach(closeSelect2);

    // Function to determine the callback name based on the element ID
    function getCallbackName(id) {
        switch(id) {
            case 'tri_agents':
                return 'get_agents_dropdown_cb';
            case '_buildout_city':
                return 'get_buildout_dropdown_cb';
            case '_gsheet_state':
                return 'get_state_dropdown_cb';
            case '_gsheet_vented':
                return 'get_vented_dropdown_cb';
            case '_gsheet_neighborhood':
                return 'get_neighborhood_dropdown_cb';
            default:
                return 'get_' + id.substr(1) + '_dropdown';
        }
    }

    // Function to close a specific Select2 dropdown
    function closeSelect2(id) {
        var $select = $('#' + id);
        if ($select.data('select2')) {
            $select.select2('close');
        }
    }
}; */







      /*       window.onload = function() {
               drtInitializeSelect2('_gsheet_state', 'get_state_dropdown_cb');
              drtInitializeSelect2('_gsheet_zip', 'get_zip_dropdown');
              drtInitializeSelect2('_gsheet_use', 'get_gsheet_use_dropdown');
              drtInitializeSelect2('_buildout_city', 'get_buildout_dropdown_cb');
              drtInitializeSelect2('_gsheet_vented', 'get_vented_dropdown_cb');
              drtInitializeSelect2('_gsheet_neighborhood', 'get_neighborhood_dropdown_cb');
              drtInitializeSelect2('tri_agents', 'get_agents_dropdown_cb');
                // Trigger click event on .select2-selection to prevent default dropdown opening
          $('.select2-selection').on('click', function(event) {
              // Prevent further propagation and default action
              event.stopPropagation();
              event.preventDefault();
          }).trigger('click');
          
          // Close Select2 dropdowns for the specified elements
          ['tri_agents','_gsheet_state', '_gsheet_zip', '_gsheet_use', '_buildout_city', '_gsheet_vented','_gsheet_neighborhood'].forEach(function(elementId) {
              var $select = $('#' + elementId);
              if ($select.data('select2')) {
                  $select.select2('close');
              }
          });

        
      };


            jQuery(document).ready(function($) {
              // Initialize Select2 for each element

            });

       */




      var isDropdownsInitialized = false;
      // Attach input event handler to relevant elements
      $('#tri_agents,#_gsheet_use,#_gsheet_zip,#_gsheet_state,#_buildout_city,#_gsheet_vented,#_gsheet_neighborhood,#_gsheet_listing_type').on('input', function() {
        // Send a single AJAX request with combined data
        const currentClickId = $(this).attr('id');
        $.ajax({
          url: '<?php echo admin_url('admin-ajax.php'); ?>',
          type: 'POST',
          data: {
            action: 'live_search',
            search_text: $('#tristate-input').val(),
            broker_ids: $("#tri_agents").val(),
            neighborhood_ids: $("#_gsheet_neighborhood").val(),
            _buildout_city: $('#_buildout_city').val(),
            _gsheet_use: $('#_gsheet_use').val(),
            selected_type: getSelectedListingTypes(),
            _gsheet_neighborhood: $('#_gsheet_neighborhood').val(),
            _gsheet_zip: $('#_gsheet_zip').val(),
            _gsheet_state: $('#_gsheet_state').val(),
            _gsheet_vented: $('#_gsheet_vented').val(),
            property_price_range: $('#price-range-selected').val(),
            property_size_range: $('#size-range-selected').val(),
            property_rent_range: $('#rent-range-selected').val(),
            isDropdownsInitialized: isDropdownsInitialized
          },

          success: function(response) {
            // Process the response
/* 
            if (!isDropdownsInitialized) {
              initializeDropdownsAndSelect2($(this).attr('id'));
              isDropdownsInitialized = true; // Set the flag as true after initialization
            } */
            $('#propertylisting-content').html(response);
            if (!isDropdownsInitialized) {
          initializeDropdownsAndSelect2(currentClickId);
          console.log(currentClickId);
          $('#' + currentClickId).select2('close');
          //tri_agents
          isDropdownsInitialized = true; // Set the flag as true after initialization
        } else {
          // Ensure the current dropdown remains open
         // $('#' + currentClickId).select2('open');
        }

          },
          error: function(xhr, status, error) {
            console.error(error); // Log any errors
          }
        });
      });

      // Initialize dropdowns and Select2
      function initializeDropdownsAndSelect2(currentClickId) {
       // console.log(currentClickId);
        // Initialize tri_agents dropdown first
        const triAgentsData = getCachedData('tri_agents');
        if (triAgentsData) {
          initializeSelect2WithData('tri_agents', triAgentsData);
        } else {
          // If tri_agents data is not cached, initialize it with AJAX
          drtInitializeSelect2('tri_agents', 'get_agents_dropdown_cb');
        }

        // IDs of other elements to initialize with Select2, excluding the current click ID
        const selectIds = [
          '_gsheet_use',
          '_gsheet_neighborhood',
          '_gsheet_state',
          '_gsheet_zip',
          '_gsheet_vented',
          '_buildout_city',
          currentClickId,
        ].filter(id => id !== currentClickId);

        // Check for cached data and initialize Select2 for other elements
        selectIds.forEach(id => {
          const cachedData = getCachedData(id);
          if (cachedData) {
            initializeSelect2WithData(id, cachedData);
          } else {
            const callbackName = getCallbackName(id);
            drtInitializeSelect2(id, callbackName);
          }
        });

        // Attach a click event handler to .select2-selection to prevent default dropdown opening
        $('.select2-selection').on('click', function(event) {
          event.stopPropagation();
          event.preventDefault();

        }).trigger('click');

        // Close Select2 dropdowns
        
        selectIds.forEach(closeSelect2);
        closeSelect2('tri_agents');
      }


      // Function to get cached data for a given ID
      function getCachedData(id) {
        const data = localStorage.getItem(id);
        return data ? JSON.parse(data) : null;
      }

      // Function to initialize Select2 with cached data
      function initializeSelect2WithData(id, data) {
        $('#' + id).select2({
          data: data
        });
      }

      //get_gsheet_use_dropdown
      // Function to determine the callback name based on the element ID
      function getCallbackName(id) {
        switch (id) {
          case '_buildout_city':
            return 'get_buildout_dropdown_cb';
          case '_gsheet_use':
            return 'get_gsheet_use_dropdown';
          case '_gsheet_state':
            return 'get_state_dropdown_cb';
          case '_gsheet_zip':
            return 'get_zip_dropdown';
          case '_gsheet_vented':
            return 'get_vented_dropdown_cb';
          case '_gsheet_neighborhood':
            return 'get_neighborhood_dropdown_cb';
          default:
            return 'get_' + id.substr(1) + '_dropdown';
        }
      }

      // Function to close a specific Select2 dropdown
      function closeSelect2(id) {
        var $select = $('#' + id);
        if ($select.data('select2')) {
          $select.select2('close');
        }
      }





      /*  ---------------------Save map layer------------- */

      jQuery("#submit_map_layer").on("click", function(e) {
        e.preventDefault();
        var search_id = $('#previous_map_post_id').val();
        var user_id = $('#map_layer_user_id').val();
        var timestamp = $('#map_layer_timestamp').val();
        var get_map_title = $('#map_post_title').val();
        var get_map_layer_title = $('#map_layer_title').val();
        var viewSearch = $('#layers-link-button');
        var get_filter_poist_id = [];
        $('input[name="get_properties_id"]').each(function() {
          var value = $(this).val();
          get_filter_poist_id.push(value);
        });

        var final_listing_ids = get_filter_poist_id.join();


        if (get_filter_poist_id.length === 0) {
          alert("No Filter is selected! Please Select filter");
        } else {


          $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
              action: 'tristate_save_results_as_layer',
              search_id: search_id,
              user_id: user_id,
              timestamp: timestamp,
              get_map_title: get_map_title,
              layer_name: get_map_layer_title,
              listing_ids: final_listing_ids,
            },

            success: function(response) {
              $('#map_layer_show_message').text(response.data.message);
              $('#map-layer-content').css('display', 'none');

              sessionStorage.setItem('latest_search_link', response.data.recent_link);
              viewSearch.css('display', 'block');
              viewSearch.attr('href', response.data.recent_link);
            },
            error: function(error) {
              console.error("Error fetching properties:", error);
            }
          });
        }
      });


    });
  </script>


  <!-- -------------------------- -->
  <?php
  /*   $cached_content = get_transient('property_listing_content');

if (false === $cached_content) {
    ob_start();  */
  ?>

  <div class="filter-wrapper" id="filter-wrapper">
    <div class="MuiBox-root">
      <div class="left-content">
        <div class="Filterform">
          <div class="MuiBox-root">
            <!-- <div class="MuiFormControl-root MuiTextField-root css-i44wyl">
              <input aria-invalid="false" id="tristate-input" placeholder="search by keyword" type="text" class="MuiInputBase-input MuiOutlinedInput-input css-1x5jdmq">
            </div> -->


            <?php

            function drt_get_dropdown_for_meta($meta_key)
            {
              global $wpdb;

              // Replace 'wp_' with your WordPress table prefix if it's different
              $table_name = $wpdb->prefix . 'postmeta';
              $query = $wpdb->prepare("SELECT DISTINCT pm.meta_value FROM $table_name AS pm 
INNER JOIN {$wpdb->prefix}posts AS p ON pm.post_id = p.ID WHERE pm.meta_key = %s AND p.post_status = 'publish' AND p.post_type = 'properties' ORDER BY meta_value ASC", $meta_key);

if($meta_key=='_gsheet_use') {
  $query = $wpdb->prepare("SELECT DISTINCT pm.meta_value FROM $table_name AS pm 
  INNER JOIN {$wpdb->prefix}posts AS p ON pm.post_id = p.ID WHERE pm.meta_key = %s AND p.post_status = 'publish' AND p.post_type = 'properties' ORDER BY meta_value ASC", $meta_key);
}
              // Custom SQL query to fetch unique trimmed values based on meta key
              if ($meta_key === '_gsheet_state122') {
                $query = $wpdb->prepare("SELECT DISTINCT TRIM(meta_value) AS meta_value FROM $table_name WHERE meta_key = %s OR meta_key = %s ORDER BY meta_value ASC", $meta_key, '_buildout_state');
              } else {
                //
 /*  $query = $wpdb->prepare("
  SELECT DISTINCT pm.meta_value 
  FROM $table_name AS pm 
  INNER JOIN {$wpdb->prefix}posts AS p ON pm.post_id = p.ID 
  WHERE pm.meta_key = %s 
  AND p.post_status = 'publish' ORDER BY meta_value ASC
", $meta_key); */



             //  $query = $wpdb->prepare("SELECT DISTINCT TRIM(meta_value) AS meta_value FROM $table_name WHERE meta_key = %s ORDER BY meta_value ASC", $meta_key);
             //   $query = $wpdb->prepare("SELECT DISTINCT TRIM(meta_value) AS meta_value FROM $table_name WHERE meta_key = %s ORDER BY meta_value ASC", $meta_key);
              }

              if ($meta_key === '_gsheet_state') {
                $query = $wpdb->prepare("SELECT DISTINCT pm.meta_value FROM $table_name AS pm INNER JOIN {$wpdb->prefix}posts AS p ON pm.post_id = p.ID WHERE pm.meta_key IN (%s, %s) AND p.post_status = 'publish' AND p.post_type = 'properties'", '_buildout_state', '_gsheet_state');
                }
                if ($meta_key === '_buildout_city') {
                  $query = $wpdb->prepare("SELECT DISTINCT pm.meta_value FROM $table_name AS pm INNER JOIN {$wpdb->prefix}posts AS p ON pm.post_id = p.ID WHERE pm.meta_key IN (%s, %s) AND p.post_status = 'publish' AND p.post_type = 'properties'", '_buildout_city', '_gsheet_city');
                  }

              // Fetching results from the database
              $results = $wpdb->get_results($query);

              // Generating the select element
              echo '<select class="js-example-basic-multiple" name="' . $meta_key . '[]" multiple="multiple" id="' . $meta_key . '">';

              // Processing and displaying the results
              if ($results) {
                foreach ($results as $result) {
                  $uses = $result->meta_value;
                  echo '<option value="' . $uses . '" data-uses="' . $uses . '">' . $uses . '</option>';
                }
              } else {
                echo '<option>No uses found</option>';
              }

              echo '</select>';
            }

            function drt_get_checkboxes_for_types($meta_key)
            {
            ?>
              <div class="tristate_cr_d-flex checkbox-wrapper" id="_gsheet_listing_type">
                <div>
                  <label for="for Sale">For Sale</label>
                  <input type="checkbox" name="listing_type" value="for Sale" id="type_for_sale">
                </div>
                <div>
                  <label for="for Lease">For Lease</label>
                  <input type="checkbox" name="listing_type" value="for Lease" id="type_for_lease">
                </div>
              </div>
            <?php
            }

            ?>
            <style>
    

            </style>
            <div>
              <label>Agents</label>
              <!--   <select id="tri_agents" class="js-example-basic-multiple" name="agents[]" multiple="multiple">
              </select> -->
              <input type="text" class="dropdown_agents">
              <div id="dropdown_agents">
              <?php
         
              $args = array(
                'post_type' => 'brokers',
                'posts_per_page' => -1, // Get all brokers
                'orderby'        => 'title', // Sort by title (broker name)
                'order'          => 'ASC',   // Sort in ascending order
              );

              $brokers = new WP_Query($args);

              if ($brokers->have_posts()) {
                echo '<select id="tri_agents" class="js-example-basic-multiple" name="agents[]" multiple="multiple">';
                while ($brokers->have_posts()) {
                  $brokers->the_post();
                  $broker_id = get_the_ID();
                  $broker_name = get_the_title();
                  echo '<option value="' . $broker_name . '" data-uid="' . $broker_id . '" data-agent_name="' . $broker_name . '"  >' . $broker_name . ' </option>';
                }
                echo '</select>';
                wp_reset_postdata();
              } else {
                // No brokers found
                echo '<p>No brokers found.</p>';
              }
              ?>
              </div>

            </div>

            <div class="drt-uses" id="filter-dropdown">
              <label>Uses</label>
              <input type="text" class="dropdown_uses">
              <div id="dropdown_uses">
              <?php   drt_get_dropdown_for_meta('_gsheet_use'); ?>
              </div>
              
            </div>
            <div>
              <label>Neighbourhoods</label>
              <input type="text" class="dropdown_neighbourhoods">
              <div id="dropdown_neighbourhoods">
              <?php drt_get_dropdown_for_meta('_gsheet_neighborhood'); ?>
              </div>
            
            </div>

            <div>
              <label>Zip Codes</label>      
              <input type="text" class="dropdown_zip_code">
             
              <div id="dropdown_zip_code">
              <?php drt_get_dropdown_for_meta('_gsheet_zip'); ?>
              </div>
             
            </div>

            <div>
              <label>Cities</label>
              <input type="text" class="dropdown_city">
              <div id="dropdown_city">
              <?php drt_get_dropdown_for_meta('_buildout_city'); ?>
              </div>
              
            </div>

            <div>
              <label>State</label>
              <input type="text" class="dropdown_state">
              <div id="dropdown_state">
              <?php drt_get_dropdown_for_meta('_gsheet_state'); ?>
              </div>
             
            </div>

            <div>
              <label>Vented</label>
              <input type="text" class="dropdown_vented">
              <div id="dropdown_vented">
              <?php drt_get_dropdown_for_meta('_gsheet_vented'); ?>
              </div>
            
            </div>

            <div>

        
              
              <div id="dropdown_lisiting_type">
              
              <?php echo drt_get_checkboxes_for_types('_gsheet_listing_type'); ?>
              </div>
                
             

            </div>

            <div id="sale_lease">
            <div>
                <div class="slider-box" id="for_sale">
                  <label for="priceRange">Price :</label>
                  <input style="display:none" type="text" id="priceRange" readonly>
                  <div class="range-min-max">
                    <input type="text" class="range-inputs" id="price-range-min" data-default="<?php echo get_price_minmax(); ?>" name="price_range_min" value="<?php echo get_price_minmax(); ?>">
                    <input type="text" class="range-inputs" id="price-range-max" data-default="<?php echo get_price_minmax('max');?>" name="price_range_max" value="<?php echo get_price_minmax('max'); ?>">
                  </div>
                  <div id="price-range" class="slider" data-min="<?php echo get_price_minmax('min',false) ?>" data-max="<?php echo get_price_minmax('max',false); ?>"></div>
                  <input type="hidden" name="price-range" data-live="0" data-clear="0" id="price-range-selected" onchange="rangeChanged(this)">
                </div>

              </div>
              <!-- For Rent -->
       
              <div>
                <div class="slider-box" id="for_lease">
                  <label for="priceRange">Price per SF:</label>
                  <input style="display:none" type="text" id="priceRange3" readonly>
                  <div class="range-min-max">
                    <input type="text" class="range-inputs" id="rent-range-min"data-default="<?php echo get_rent_minmax(); ?>" name="price_range_min" value="<?php echo get_rent_minmax(); ?>">
                    <input type="text" class="range-inputs" id="rent-range-max" data-default="<?php echo get_rent_minmax('max');?>" name="price_range_max" value="<?php echo get_rent_minmax('max'); ?>">
                  </div>
                  <div id="price-range3" class="slider" data-min="<?php echo get_rent_minmax('min',false) ?>" data-max="<?php echo get_rent_minmax('max',false); ?>"></div>
                  <input type="hidden" name="rent-range" data-clear="0" id="rent-range-selected" onchange="rangeChanged(this)">
                </div>
              </div>
            </div>

            <div>
              <div class="slider-box">
                <label for="priceRange">Size:</label>
                <input style="display:none" type="text" id="priceRange2" readonly>

                <div class="range-min-max">
                  <input type="text" class="range-inputs" id="size-range-min" data-default="<?php echo get_size_minmax(); ?>" name="size_range_min" value="<?php echo get_size_minmax(); ?>">
                  <input type="text" class="range-inputs" id="size-range-max"  data-default="<?php echo get_size_minmax('max');?>" name="price_range_max" value="<?php echo get_size_minmax('max'); ?>">
                </div>
                <div id="price-range2" class="slider" data-min="<?php echo get_size_minmax('min',false) ?>" data-max="<?php echo get_size_minmax('max',false); ?>"></div>
                <input type="hidden" name="size-range" id="size-range-selected" data-live="0" data-clear="0" onchange="rangeChanged(this)">
              </div>
            </div>

            <div class="price-range-btm">
              <div class="MuiBox-root css-69324s">
                <div>
                  <button tabindex="0" type="button" id="save_map_layer" data-count="" class="MuiButtonBase-root MuiButton-root MuiButton-contained MuiButton-containedPrimary MuiButton-sizeMedium MuiButton-containedSizeMedium MuiButton-colorPrimary MuiButton-root MuiButton-contained MuiButton-containedPrimary MuiButton-sizeMedium MuiButton-containedSizeMedium MuiButton-colorPrimary css-1hw9j7s">
                    Save <?php echo __total() ?> results to a new map layer <span class="MuiTouchRipple-root css-w0pj6f"></span>
                  </button>
                  <!-- Popup content -->

                  <div class="tcr-popup-overlay"></div>

                  <div class="tcr-popup-wrapper" id="tcr-popup-wrapper">

                    <div class="tcr-popup-content" id="tcr-req-acc-output">
                      <?php if (is_user_logged_in()) : ?>
                        <h4>SAVE TO A NEW MAP LAYER</h4>
                        <form method="POST">
                          <div id="map-layer-content">
                            <ul>
                              <input type="hidden" name="userid" id="map_layer_user_id" value="<?php echo get_current_user_id(); ?>">
                              <input type="hidden" name="timestamp" id="map_layer_timestamp" value="<?php echo time(); ?>">
                              <?php
                              if (isset($_GET['search_id'])) {
                                $get_search_id =  $_GET['search_id'];
                                echo '<input type="hidden" name="previous_map_post_id" id="previous_map_post_id" value="' . ($get_search_id) . '"  readonly>';
                              } else {
                                echo '<li><label>Map Title</label>';
                                echo '<input type="text" name="map_post_title" id="map_post_title" required>';
                              }
                              ?>

                              </li>
                              <li>
                                <label>Layer Title</label>
                                <input type="text" name="map_layer_title" id="map_layer_title" required>
                              </li>
                            </ul>

                            <input type="hidden" name="map_layer_post_ids" id="map_layer_post_ids">
                            <input type="submit" id="submit_map_layer" name="submit_layer" value="save to a new map layer">
                          </div>
                        </form>
                      <?php else : ?>
                        <h4>Please <a href="<?php echo wp_login_url(get_permalink()); ?>">login</a> to save the map layer.</h4>

                      <?php endif; ?>
                      <div id="map_layer_show_message"></div>
                    </div>

                    <button id="tcr-popup-close-button">X</button>
                  </div>
                  <!-- Popup content end -->
                </div>
              </div>
              <div class="MuiBox-root css-69324s">
                <div class="filter-search">

                  <button id="filter-clear11" tabindex="0" type="button" class="MuiButtonBase-root MuiButton-root MuiButton-contained MuiButton-containedPrimary MuiButton-sizeMedium MuiButton-containedSizeMedium MuiButton-colorPrimary MuiButton-root MuiButton-contained MuiButton-containedPrimary MuiButton-sizeMedium MuiButton-containedSizeMedium MuiButton-colorPrimary bg-yellow css-1hw9j7s color-white"> Clear Filter <span class="MuiTouchRipple-root css-w0pj6f"></span>
                  </button>

                  <?php

                  if (isset($_GET['search_id'])) {
                    $get_search_id =  $_GET['search_id'];

                    echo '<a href="' . get_permalink($get_search_id) . '" target="_blank" rel="noopener noreferrer"> <button id="" tabindex="0" type="button" class="MuiButtonBase-root MuiButton-root MuiButton-contained MuiButton-containedPrimary MuiButton-sizeMedium MuiButton-containedSizeMedium MuiButton-colorPrimary MuiButton-root MuiButton-contained MuiButton-containedPrimary MuiButton-sizeMedium MuiButton-containedSizeMedium MuiButton-colorPrimary bg-black css-1hw9j7s color-white"> View Search <span class="MuiTouchRipple-root css-w0pj6f"></span>
                  </button>
                 </a>';
                  }
                  ?>
                </div>
              </div>



              <div class="MuiBox-root css-69324s">

                <p>
                  <a class="button" id="layers-link-button" style="display: none;" href="#" target="_blank">View Custom Map</a>
                </p>

                <script>
                  if (sessionStorage.getItem('latest_search_link')) {
                    var layersLinkButton = document.getElementById('layers-link-button');
                    layersLinkButton.style.display = 'block';
                    layersLinkButton.setAttribute('href', sessionStorage.getItem('latest_search_link'));

                  } else {

                    document.getElementById('layers-link-button').style.display = 'none';
                  }
                </script>

              </div>
            </div>

          </div>
        </div>
      </div>
      <div class="right-content">
        <?php 
          // Perform the query to fetch search results
          $args = array(
            'post_type'      => 'properties',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'relation' => 'OR',
                    array(
                        'key'     => '_buildout_lease',
                        'value'   => '1',
                        'compare' => '=',
                        'type'    => 'NUMERIC',
                    ),
                    array(
                        'key'     => '_buildout_sale',
                        'value'   => '1',
                        'compare' => '=',
                        'type'    => 'NUMERIC',
                    ),
                ),
            )
        );
        
          $search_query = new WP_Query($args);
          $default_found_results = $search_query->found_posts;
        ?>
        <div id="menu-btn"><i class="fa fa-angle-left"></i></div>
        <div class="right-map">
          <!-- <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d407542.86304287874!2d-74.32724652492182!3d40.69942908913206!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!z4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2snp!4v1711702301417!5m2!1sen!2snp" allowfullscreen="allowFullScreen" width="100%" height="450px" style="position: relative; display: block;"></iframe> -->
          <div id="tristate-map" style="height:600px; width:100%;position:relative;display:block;"></div>
        </div>
        <div id="search_count_area">
          <div class="MuiFormControl-root MuiTextField-root css-i44wyl">
            <input aria-invalid="false" id="tristate-input" placeholder="search by keyword" type="text" class="MuiInputBase-input MuiOutlinedInput-input css-1x5jdmq">
          </div>
          <div class="column-select-result-count">
      <div id="tristate-result-count" data-count="<?php echo __total(); ?>">
            <?php echo 'Showing ' . $default_found_results . ' of ' .$default_found_results . ' Listing' ?>
          
          </div>
      <div class="tristate-column-select">
              <select name="" id="selectcolumn">
                <option value="1">Column One</option>
                <option value="2">Column Two</option>
                <option value="3" selected>Column Three</option>
              </select>
            </div>
      </div>

        </div>

        <div class="search-by-text">
            <input aria-invalid="false" id="search-by-text" placeholder="search by text" type="text">
          </div>

        <div class="post-output"></div>




        <div class="property-list-wrapper">
          <div class="MuiBox-root">
            <div class="MuiStack-root property-filter css-12xuzbq" id="propertylisting-content">

            <?php
              // Output the search results
              if ($search_query->have_posts()) {
                $loop = TRISTATECRLISTING_PLUGIN_DIR . 'templates/loop.php';
                while ($search_query->have_posts()) {$search_query->the_post(); 
                  $ID = get_the_id();
                  if(file_exists($loop)){ load_template($loop,false, ['ID'=> $ID,'ajax'=>true]);}
                    $markers_data[] = tristate_get_marker_data($ID);

                }
                wp_reset_postdata();
              } else {
                echo '<p>No results found.</p>';
              }
              ?>


            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function rangeChanged(input) {
      jQuery(document).ready(function($) {

        function getSelectedListingTypes2() {
          var selectedTypes = [];
          $('#_gsheet_listing_type input[type="checkbox"]:checked').each(function() {
            selectedTypes.push($(this).val());
          });
          return selectedTypes;
        }
        var checkClear = jQuery("#" + input.id).attr("data-clear");
       
        if (checkClear == "0" ) {
          $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
              action: 'live_search',
              _buildout_city: $('#_buildout_city').val(),
              search_text: $('#tristate-input').val(),
              broker_ids: $('#tri_agents').val(),
              _gsheet_use: $('#_gsheet_use').val(),
              // selected_type: $('#_gsheet_listing_type').val(),
              selected_type: getSelectedListingTypes2(),
              _gsheet_neighborhood: $('#_gsheet_neighborhood').val(),
              _gsheet_zip: $('#_gsheet_zip').val(),
              _gsheet_state: $('#_gsheet_state').val(),
              _gsheet_vented: $('#_gsheet_vented').val(),
              property_price_range: $('#price-range-selected').val(),
              property_size_range: $('#size-range-selected').val(),
              property_rent_range: $('#rent-range-selected').val(),
            },

            success: function(response) {

              $('#propertylisting-content').html(response); // Display previous result

            },
            error: function(error) {
              console.error("Error fetching properties:", error);
            }
          });

        }


      });
    }
  </script>
  <!-- text data 1 -->
  <textarea style="display: none;" id="marker_data_all"><?php echo json_encode($markers_data) ?></textarea>
  <?php
  /*      $cached_content = ob_get_clean(); 
              set_transient('property_listing_content', $cached_content);
          }
          
          echo $cached_content;  */

  // return ob_get_clean();
  ?>
  <script>
    jQuery(document).ready(function($) {
      // Get the input element
      var input = $('#tristate-input');
      var timer;
      var cachedResults = {}; // Object to store cached search results

      // Function to handle the AJAX request
      function makeRequest() {
        // Get the input value
        var searchText = input.val().trim();

        // Check if the search text exists in the cached results
        if (searchText in cachedResults) {
          // If cached results exist, display them
          $('#propertylisting-content').html(cachedResults[searchText]);
          return; // Return early, no need to make AJAX request
        }

        // Prepare the data to be sent
        var data = {
          action: 'live_search',
          search_text: searchText,
          broker_ids: $("#tri_agents").val(),
          neighborhood_ids: $("#_gsheet_neighborhood").val(),
          _buildout_city: $('#_buildout_city').val(),
          _gsheet_use: $('#_gsheet_use').val(),
          //selected_type: getSelectedListingTypes(),
          _gsheet_neighborhood: $('#_gsheet_neighborhood').val(),
          _gsheet_zip: $('#_gsheet_zip').val(),
          _gsheet_state: $('#_gsheet_state').val(),
          _gsheet_vented: $('#_gsheet_vented').val(),
          property_price_range: $('#price-range-selected').val(),
          property_size_range: $('#size-range-selected').val(),
          property_rent_range: $('#rent-range-selected').val(),
        };

        // Send the AJAX request
        $.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
          // Update the UI with the response
          $('#propertylisting-content').html(response);

          // Cache the search results
          cachedResults[searchText] = response;

          // Update result count
          var resultCountElement = $('#tristate-result-count');
          var getSearchResults = $('#get_filter_results');
          var saveLayer = $('#save_map_layer');
          if (resultCountElement.length && getSearchResults.length) {
            resultCountElement.text(getSearchResults.text());
            getSearchResults.remove();

          }
        });
      }

      // Attach event listener for keyup event
      input.on('keyup', function() {
        // Clear the previous timer
        clearTimeout(timer);

        // Set a timer to delay the AJAX request
        timer = setTimeout(makeRequest, 250);
      });
    });
  </script>
<?php


}

?>
<?php

add_action('wp_ajax_live_search', 'live_search_callback');
add_action('wp_ajax_nopriv_live_search', 'live_search_callback');

function live_search_callback()
{
  // Get the search text from the request
  $search_text = isset($_POST['search_text']) ? sanitize_text_field($_POST['search_text']) : '';
  // Initialize meta query
  $args = array(
    'post_type'      => 'properties',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_query'     => array('relation' => 'AND')
  );
  

  $crs = 'all';

  // Meta query conditions
  $meta_queries = array(
    '_gsheet_neighborhood',
    '_buildout_city',
    '_gsheet_use',
     'selected_type' => '_gsheet_listing_type',
    '_gsheet_zip',
    '_gsheet_vented',
  );

  foreach ($meta_queries as $key => $value) {
    $meta_key = is_numeric($key) ? $value : $key;
    if (!empty($_POST[$value])) {
      $args['meta_query'][] = array(
        'key'     => $meta_key,
        'value'   => $_POST[$value],
        'compare' => 'IN'
      );
    }
  }

  if (!empty($_POST['selected_type'])) {
    $selected_array = $_POST['selected_type'];
    if (in_array('for Sale', $selected_array) && in_array('for Lease', $selected_array)) {

      $args['meta_query'][] = array(
        'relation' => 'OR',
        array(
          'key'     => '_buildout_lease',
          'value'   => '1',
          'compare' => '=',
          'type'    => 'NUMERIC',
        ),
        array(
          'key'     => '_buildout_sale',
          'value'   => '1',
          'compare' => '=',
          'type'    => 'NUMERIC',
        )
      );
    } elseif (in_array('for Sale', $selected_array)) {

      $args['meta_query'][] = array(
        'key'     => '_buildout_sale',
        'value'   => '1',
        'compare' => '=',
        'type'    => 'NUMERIC',
      );
    } elseif (in_array('for Lease', $selected_array)) {

      $args['meta_query'][] = array(
        'key'     => '_buildout_lease',
        'value'   => '1',
        'compare' => '=',
        'type'    => 'NUMERIC',
      );
    }
  }

  // Additional conditions
  if (!empty($_POST['neighborhoodIds'])) {
    $args['meta_query'][] = array(
      'key'     => '_gsheet_neighborhood',
      'value'   => $_POST['neighborhoodIds'],
      'compare' => 'IN'
    );
  }

  if (!empty($_POST['_gsheet_state'])) {
    $args['meta_query'][] = array(
      'relation' => 'OR',
      array(
        'key'     => '_gsheet_state',
        'value'   => $_POST['_gsheet_state'],
        'compare' => 'IN'
      ),
      array(
        'key'     => '_buildout_state',
        'value'   => $_POST['_gsheet_state'],
        'compare' => 'IN'
      )
    );
  }

  if (!empty($search_text)) {
    $args['orderby']  = 'relevance';
    $args['s']        = $search_text;
    $args['sentence'] = false;
  }



  // Broker IDs condition
/*   $brokerIds = isset($_POST['broker_ids']) ? $_POST['broker_ids'] : array();
  if (!empty($brokerIds)) {
    $args['meta_query'][] = array(
      'key'     => '_gsheet_listing_agent',
      'value'   => $brokerIds,
      'compare' => 'IN'
    );
  } */
  $brokerIds = isset($_POST['broker_ids']) ? $_POST['broker_ids'] : array();
if (!empty($brokerIds)) {
    $args['meta_query'][] = array(
        'relation' => 'OR', // Using 'OR' to check either of the keys
        array(
            'key'     => '_gsheet_listing_agent',
            'value'   => $brokerIds,
            'compare' => 'IN'
        ),
        array(
            'key'     => '_buildout_listing_agent',
            'value'   => $brokerIds,
            'compare' => 'IN'
        ),
    );
}
  

  if (!empty($_POST['property_price_range'])) {
    $crs = 'price-range';
    $range = explode('-', sanitize_text_field($_POST['property_price_range']));
    $trimmed_range = array_map('trim', $range);
    if ($trimmed_range[0] == "0" && $trimmed_range[1] == get_price_minmax('max', false) ) {
    } else {
      $args['meta_query'][] = array(
        'relation' => 'AND',
        array(
          'key'     => '_buildout_sale_price_dollars',
          'value'   => $trimmed_range[0],
          'compare' => '>=',
          'type'    => 'NUMERIC',
        ),
        array(
          'key'     => '_buildout_sale_price_dollars',
          'value'   => $trimmed_range[1],
          'compare' => '<=',
          'type'    => 'NUMERIC',
        )
      );
    }
  }

  if (!empty($_POST['property_size_range'])) {

    $range = explode('-', sanitize_text_field($_POST['property_size_range']));
    $trimmed_range = array_map('trim', $range);
    $crs = 'price-range2';
    if ($trimmed_range[0] == "0" && $trimmed_range[1] == get_size_minmax('max', false)) {
    } else {
      $args['meta_query'][] = array(
        'relation' => 'AND',
        array(
          'key'     => '_gsheet_min_size_fm',
          'value'   => $trimmed_range[0],
          'compare' => '>=',
          'type'    => 'NUMERIC',
        ),
        array(
          'key'     => '_gsheet__max_size_fm',
          'value'   => $trimmed_range[1],
          'compare' => '<=',
          'type'    => 'NUMERIC',
        )
      );
    }
  }
  if (!empty($_POST['property_rent_range'])) {

    $range = explode('-', sanitize_text_field($_POST['property_rent_range']));
    $trimmed_range = array_map('trim', $range);
    $crs = 'price-range3';

    if ($trimmed_range[0] == "0" &&  $trimmed_range[1] == get_rent_minmax('max', false)) {
    } else {
      $args['meta_query'][] = array(
        'relation' => 'AND',
        array(
          'key'     => '__gsheet__monthly_rent',
          'value'   => $trimmed_range[0],
          'compare' => '>=',
          'type'    => 'NUMERIC',
        ),
        array(
          'key'     => '__gsheet__monthly_rent',
          'value'   => $trimmed_range[1],
          'compare' => '<=',
          'type'    => 'NUMERIC',
        )
      );
    }
  }

  // Run the query
  $drt_query = new WP_Query($args);

  $total_search_results = $drt_query->found_posts;
  $totals = __total();

  $results_string = "<div id='get_filter_results'><p>Showing {$total_search_results} of {$totals} listing</p></div>";
  $button_string = "SAVE " . $total_search_results . " RESULTS TO A NEW MAP LAYER";

  echo $results_string;

  $max_p_val = [0];
  $max_r_val = [0];
  $min_size_max = [0];
  $max_size_max=[0];
  if ($drt_query->have_posts()) {
    $loop = TRISTATECRLISTING_PLUGIN_DIR . 'templates/loop.php';
    while ($drt_query->have_posts()) { $drt_query->the_post();
      $ID = get_the_id();
      if(file_exists($loop)){ load_template($loop,false, ['ID'=> get_the_id()]);}
      
        $mark_data[] = tristate_get_marker_data($ID);
        $_price_sf   = meta_of_api_sheet($ID, 'price_sf');
        // for sf price
        $new_max_p_sf= preg_replace('/\$?(\d+)\.\d{2}/', '$1', $_price_sf);
        if($_price_sf !=='0' && !empty($_price_sf))  $max_p_val[] = (int) $new_max_p_sf;
        
        // for buildout price
        $bo_price    = meta_of_api_sheet($ID, 'sale_price_dollars');
        if($bo_price !== '0' && !empty($bo_price)) $max_p_val[] = (int) $bo_price;
        
        // for rent 
        $rent = get_post_meta($ID,'__gsheet__monthly_rent',true);
        $new_rent =  preg_replace('/\$?(\d+)\.\d{2}/', '$1', $rent);
    
        if($new_rent !== '0' && !empty($new_rent)) $max_r_val[] = (int) $new_rent;
        
        // for maximum value of minimum size
        $min_size       = get_post_meta($ID, '_gsheet_min_size_fm',true);
        if(!empty($min_size)) $min_size_max[] = (int) $min_size;
        
        // for maximum value of minimum size
        $max_size       = get_post_meta($ID, '_gsheet__max_size_fm',true);
        if(!empty($max_size)) $max_size_max[] = (int) $max_size;
    }
    wp_reset_postdata();
    ?>

  <?php
  } else {
    // if not found
    echo '<p id="not-found" data-results="0">No results found.</p>';
    
  }
  

  ?>
  <!-- text data 2 -->
  <input type="hidden" id="manage-sliders" data-current="<?php echo !empty($crs)?$crs : 'all';  ?>" data-maxprice="<?php echo max($max_p_val) ?>" data-maxrent="<?php echo max($max_r_val) ?>" data-maxsize="<?php echo max($max_size_max ) ?>" value="ajax" >
  <textarea style="display: none;" id="ajax-marker-data" rows="4" cols="50"> <?php echo json_encode($mark_data); ?> </textarea>
  
  <script>
    document.getElementById("save_map_layer").innerText = "<?php echo $button_string ?>";
    jQuery(document).ready(function($) {

      var resultCountElement = document.getElementById('tristate-result-count');
      var getSearchResults = document.getElementById('get_filter_results');
      if (resultCountElement && getSearchResults) {
        resultCountElement.textContent = getSearchResults.textContent;
        getSearchResults.parentNode.removeChild(getSearchResults);
      }
      var newStr = "<?php echo $results_string; ?>";
      var buttonStr = "<?php echo $button_string; ?>";
      $("#ajax-marker-data").trigger('change');

      var total_search_results = <?php echo $total_search_results; ?>; // Assuming $total_search_results is a PHP variable containing the total search results
  
  var $propertyListingContent = $('#propertylisting-content');

  if (total_search_results == 1) {
    $propertyListingContent.addClass('column-one');
  } else {
    $propertyListingContent.removeClass('column-one');
  }

  if (total_search_results == 2) {
    $propertyListingContent.addClass('column-two');
  } else {
    $propertyListingContent.removeClass('column-two');
  }
    });
  </script>
  <!-- Different Script for making dynamica range sliders -->
  <script>

    jQuery(document).ready(function($){
      $('#manage-sliders').trigger('change');

    });
  
  </script>

<?php


  die();
}

add_action('wp_ajax_get_uses_dropdown', 'get_uses_dropdown_callback');
add_action('wp_ajax_nopriv_get_uses_dropdown', 'get_uses_dropdown_callback');

function get_uses_dropdown_callback()
{
  // Get the selected broker IDs from the AJAX request
  $selected_broker_ids = isset($_POST['broker_ids']) ? $_POST['broker_ids'] : array();

  global $wpdb;

  // Replace 'wp_' with your WordPress table prefix if it's different
  $table_name = $wpdb->prefix . 'postmeta';
  $meta_key = '_gsheet_use';

  // If no broker IDs are provided, fetch all options and enable them
  if (empty($selected_broker_ids)) {
    $query_all = $wpdb->prepare("
            SELECT DISTINCT meta_value 
            FROM $table_name 
            WHERE meta_key = %s", $meta_key);
    $results_all = $wpdb->get_results($query_all);
    $data = array();

    foreach ($results_all as $result) {
      $data[] = array(
        'id' => $result->meta_value,
        'text' => $result->meta_value,
        'disabled' => false  // Enable all options if no broker IDs are selected
      );
    }
    wp_send_json($data);
    die();
  }

  // Custom SQL query to fetch unique values based on meta key and selected broker IDs
  $query = $wpdb->prepare("SELECT DISTINCT meta_value 
        FROM $table_name 
        WHERE meta_key = %s
        AND post_id IN (
            SELECT post_id 
            FROM $table_name 
            WHERE meta_key = '_gsheet_listing_agent' 
            AND meta_value IN ('" . implode("','", $selected_broker_ids) . "')
        )", $meta_key);

  // Fetching results from the database
  $results = $wpdb->get_results($query);

  // Array to store the values obtained from the second query
  $matched_uses = array();
  foreach ($results as $result) {
    $matched_uses[] = $result->meta_value;
  }

  // Fetch all uses to populate the dropdown and disable unmatched
  $query_all = $wpdb->prepare("
        SELECT DISTINCT meta_value 
        FROM $table_name 
        WHERE meta_key = %s", $meta_key);
  $results_all = $wpdb->get_results($query_all);
  $data = array();

  foreach ($results_all as $result) {
    $uses = $result->meta_value;
    $is_matched = in_array($uses, $matched_uses);
    $data[] = array(
      'id' => $uses,
      'text' => $uses,
      'disabled' => !$is_matched  // Disable options not matched with selected broker IDs
    );
  }

  wp_send_json($data);
  die();
}








// Assuming this is inside a WordPress theme or plugin

// add_action('wp_ajax_get_neighbourhoods_dropdown', 'get_neighbourhoods_dropdown_callback');
// add_action('wp_ajax_nopriv_get_neighbourhoods_dropdown', 'get_neighbourhoods_dropdown_callback');

function get_dropdown_select_options_drdown()
{

  $selected_broker_ids = isset($_POST['broker_ids']) ? $_POST['broker_ids'] : array();
  $selected_city =  isset($_POST['_buildout_city']) ? $_POST['_buildout_city'] : array();
  $selected_use  =  isset($_POST['_gsheet_use']) ? $_POST['_gsheet_use'] : array();
  $selected_neighbourhoods = isset($_POST['_gsheet_neighborhood']) ? $_POST['_gsheet_neighborhood'] : array();
  $selected_zip = isset($_POST['_gsheet_zip']) ? $_POST['_gsheet_zip'] : array();
  $selected_state = isset($_POST['_gsheet_state']) ? $_POST['_gsheet_state'] : array();
  $selected_vented = isset($_POST['_gsheet_vented']) ? $_POST['_gsheet_vented'] : array();

  global $wpdb;
  $table_name = $wpdb->prefix . 'postmeta';
  $meta_key = '_gsheet_neighborhood';

  $query = "SELECT DISTINCT meta_value FROM $table_name WHERE meta_key = %s";

  // Parameters for the prepared statement
  $params = array($meta_key);

  if (!empty($selected_neighbourhoods)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_neighborhood' 
          AND meta_value IN ('" . implode("','", $selected_neighbourhoods) . "')
      )";
  }

  // Adding conditions for selected broker IDs if not empty
/*   if (!empty($selected_broker_ids)) {
    $query .= " AND post_id IN (
            SELECT post_id 
            FROM $table_name 
            WHERE meta_key = '_gsheet_listing_agent' 
            AND meta_value IN ('" . implode("','", $selected_broker_ids) . "')
        )";
  }
 */

  if (!empty($selected_broker_ids)) {
    $broker_ids_str = implode("','", $selected_broker_ids);
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE (meta_key = '_gsheet_listing_agent' AND meta_value IN ('$broker_ids_str'))
             OR (meta_key = '_buildout_listing_agent' AND meta_value IN ('$broker_ids_str'))
      )";
}

  // Adding conditions for selected city if not empty
  if (!empty($selected_city)) {
    $query .= " AND post_id IN (
            SELECT post_id 
            FROM $table_name 
            WHERE meta_key = '_buildout_city' 
            AND meta_value IN ('" . implode("','", $selected_city) . "')
        )";
  }

  // Adding conditions for selected use if not empty
  if (!empty($selected_use)) {
    $query .= " AND post_id IN (
            SELECT post_id 
            FROM $table_name 
            WHERE meta_key = '_gsheet_use' 
            AND meta_value IN ('" . implode("','", $selected_use) . "')
        )";
  }

  // Adding conditions for selected zip if not empty
  if (!empty($selected_zip)) {
    $query .= " AND post_id IN (
            SELECT post_id 
            FROM $table_name 
            WHERE meta_key = '_gsheet_zip' 
            AND meta_value IN ('" . implode("','", $selected_zip) . "')
        )";
  }

  // Adding conditions for selected state if not empty
/*   if (!empty($selected_state)) {
    $query .= " AND post_id IN (
            SELECT post_id 
            FROM $table_name 
            WHERE meta_key = '_gsheet_state' 
            AND meta_value IN ('" . implode("','", $selected_state) . "')
        )";
  } */

  if (!empty($selected_state)) {
    $query .= " AND (post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_state' 
        AND meta_value IN ('" . implode("','", $selected_state) . "')
    ) OR post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_buildout_state' 
        AND meta_value IN ('" . implode("','", $selected_state) . "')
    ))";
  }
  // Adding conditions for selected vented if not empty
  if (!empty($selected_vented)) {
    $query .= " AND post_id IN (
            SELECT post_id 
            FROM $table_name 
            WHERE meta_key = '_gsheet_vented' 
            AND meta_value IN ('" . implode("','", $selected_vented) . "')
        )";
  }

  // Preparing the query
  $query = $wpdb->prepare($query, $params);

  // Fetching results from the database
  $original_results = $wpdb->get_results($query);

  // Array to store the values obtained from the second query
  $matched_neighbourhoods = array();

  // Storing the values obtained from the first query into the array
  if ($original_results) {
    foreach ($original_results as $result) {
      $matched_neighbourhoods[] = $result->meta_value;
    }
  }

  // Fetching all results from the database
  $query_all = $wpdb->prepare("SELECT DISTINCT meta_value FROM $table_name WHERE meta_key = %s", $meta_key);
  $results_all = $wpdb->get_results($query_all);

  // Processing and displaying the results
  if ($results_all) {
    foreach ($results_all as $result) {
      $neighbourhood = $result->meta_value;
      // Check if $neighbourhood is found in $matched_neighbourhoods array
      $is_matched = in_array($neighbourhood, $matched_neighbourhoods);
      // If $selected_broker_ids is empty, do not disable the option
      // Otherwise, disable the option if $neighbourhood is not found in $matched_neighbourhoods array
      $disabled = empty($selected_broker_ids) ? '' : ($is_matched ? '' : 'disabled');
      echo '<option value="' . $neighbourhood . '" data-neighbourhood="' . $neighbourhood . '" ' . $disabled . '>' . $neighbourhood . '</option>';
    }
  } else {
    echo '<option>No Neighbourhoods Found</option>';
  }

  wp_die();
}
// get zipcode dropdown
add_action('wp_ajax_get_zip_dropdown', 'get_zip_dropdown_callback');
add_action('wp_ajax_nopriv_get_zip_dropdown', 'get_zip_dropdown_callback');
// get zipcode dropdown
function get_zip_dropdown_callback()
{
  $selected_broker_ids = isset($_POST['broker_ids']) ? $_POST['broker_ids'] : array();
  $selected_city = isset($_POST['_buildout_city']) ? $_POST['_buildout_city'] : array();
  $selected_use = isset($_POST['_gsheet_use']) ? $_POST['_gsheet_use'] : array();
  $selected_neighbourhoods = isset($_POST['_gsheet_neighborhood']) ? $_POST['_gsheet_neighborhood'] : array();
  $selected_zip = isset($_POST['_gsheet_zip']) ? $_POST['_gsheet_zip'] : array();
  $selected_state = isset($_POST['_gsheet_state']) ? $_POST['_gsheet_state'] : array();
  $selected_vented = isset($_POST['_gsheet_vented']) ? $_POST['_gsheet_vented'] : array();

  global $wpdb;
  $table_name = $wpdb->prefix . 'postmeta';
  $meta_key = '_gsheet_zip';

  // $query = "SELECT DISTINCT meta_value FROM $table_name WHERE meta_key = %s ";

  $query = "SELECT DISTINCT pm.meta_value 
          FROM $table_name pm 
          INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
          WHERE pm.meta_key = %s 
          AND p.post_status = 'publish' 
          AND p.post_type = 'properties' ";

  // Parameters for the prepared statement
  $params = array($meta_key);

  /*   if (!empty($selected_zip)) {
    $query .= " AND post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_zip' 
        AND meta_value IN ('" . implode("','", $selected_zip) . "')
    )";
  } */

  // Adding conditions for selected broker IDs if not empty
/*   if (!empty($selected_broker_ids)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_listing_agent' 
          AND meta_value IN ('" . implode("','", $selected_broker_ids) . "')
      )";
  } */

  if (!empty($selected_broker_ids)) {
    $broker_ids_str = implode("','", $selected_broker_ids);
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE (meta_key = '_gsheet_listing_agent' AND meta_value IN ('$broker_ids_str'))
             OR (meta_key = '_buildout_listing_agent' AND meta_value IN ('$broker_ids_str'))
      )";
}

  $selected_type = isset($_POST['selected_type']) ? $_POST['selected_type'] : array();
  if (!empty($selected_type)) {
    $query .= " AND post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_listing_type' 
        AND meta_value IN ('" . implode("','", $selected_type) . "')
    )";
  }

  // Adding conditions for selected city if not empty
  if (!empty($selected_city)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_buildout_city' 
          AND meta_value IN ('" . implode("','", $selected_city) . "')
      )";
  }

  // Adding conditions for selected use if not empty
  if (!empty($selected_use)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_use' 
          AND meta_value IN ('" . implode("','", $selected_use) . "')
      )";
  }

  // Adding conditions for selected neighbourhoods if not empty
  if (!empty($selected_neighbourhoods)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_neighborhood' 
          AND meta_value IN ('" . implode("','", $selected_neighbourhoods) . "')
      )";
  }

  // Adding conditions for selected state if not empty
  /* if (!empty($selected_state)) {
      $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_state' 
          AND meta_value IN ('" . implode("','", $selected_state) . "')
      )";
  } */

  if (!empty($selected_state)) {
    $query .= " AND (post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_state' 
        AND meta_value IN ('" . implode("','", $selected_state) . "')
    ) OR post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_buildout_state' 
        AND meta_value IN ('" . implode("','", $selected_state) . "')
    ))";
  }

  // Adding conditions for selected vented if not empty
  if (!empty($selected_vented)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_vented' 
          AND meta_value IN ('" . implode("','", $selected_vented) . "')
      )";
  }

  // Preparing the query
  $query = $wpdb->prepare($query, $params);

  // Fetching results from the database
  $original_results = $wpdb->get_results($query);

  // Array to store the values obtained from the first query
  $matched_zip = array();

  // Storing the values obtained from the first query into the array
  if ($original_results) {
    foreach ($original_results as $result) {
      $matched_zip[] = $result->meta_value;
    }
  }

  // Fetching all results from the database
  //$query_all = $wpdb->prepare("SELECT DISTINCT meta_value FROM $table_name WHERE meta_key = %s", $meta_key);
  $query_all = $wpdb->prepare("
    SELECT DISTINCT pm.meta_value 
    FROM $table_name AS pm 
    INNER JOIN {$wpdb->prefix}posts AS p ON pm.post_id = p.ID 
    WHERE pm.meta_key = %s 
    AND p.post_status = 'publish' 
    AND p.post_type = 'properties'
", $meta_key);

  $results_all = $wpdb->get_results($query_all);
  $data = array();

  foreach ($results_all as $result) {
    $zip = $result->meta_value;
    $is_matched = in_array($zip, $matched_zip);
    $data[] = array(
      'id' => $zip,
      'text' => $zip,
      'matched' => $is_matched // Store if ZIP code is matched or not
    );
  }

  // Send JSON response
  wp_send_json($data);
  wp_die();
}




// get gsheet_use dropdown
add_action('wp_ajax_get_gsheet_use_dropdown', 'get_gsheet_use_dropdown_callback');
add_action('wp_ajax_nopriv_get_gsheet_use_dropdown', 'get_gsheet_use_dropdown_callback');

// get zipcode dropdown
// get gsheet_use dropdown
add_action('wp_ajax_get_gsheet_use_dropdown', 'get_gsheet_use_dropdown_callback');
add_action('wp_ajax_nopriv_get_gsheet_use_dropdown', 'get_gsheet_use_dropdown_callback');

// get zipcode dropdown
function get_gsheet_use_dropdown_callback()
{
  // Collect input values
  $selected_broker_ids = isset($_POST['broker_ids']) ? $_POST['broker_ids'] : array();
  $selected_city = isset($_POST['_buildout_city']) ? $_POST['_buildout_city'] : array();
  $selected_use = isset($_POST['_gsheet_use']) ? $_POST['_gsheet_use'] : array();
  $selected_neighbourhoods = isset($_POST['_gsheet_neighborhood']) ? $_POST['_gsheet_neighborhood'] : array();
  $selected_zip = isset($_POST['_gsheet_zip']) ? $_POST['_gsheet_zip'] : array();
  $selected_state = isset($_POST['_gsheet_state']) ? $_POST['_gsheet_state'] : array();
  $selected_vented = isset($_POST['_gsheet_vented']) ? $_POST['_gsheet_vented'] : array();
  $selected_type = isset($_POST['selected_type']) ? $_POST['selected_type'] : array();

  global $wpdb;
  $table_name = $wpdb->prefix . 'postmeta';
  $meta_key = '_gsheet_use';

  $query = $wpdb->prepare("SELECT DISTINCT pm.meta_value FROM $table_name AS pm 
    INNER JOIN {$wpdb->prefix}posts AS p ON pm.post_id = p.ID 
    WHERE pm.meta_key = %s 
    AND p.post_status = 'publish' 
    AND p.post_type = 'properties'
  ", $meta_key);

  // Fetch all ZIP codes
  $results_all = $wpdb->get_results($query);

/*   // Array to store matched ZIP codes
  $matched_zip = array();

  // Filter ZIP codes based on criteria
  if (!empty($selected_zip)) {
    $matched_zip_query = $wpdb->prepare("SELECT DISTINCT pm.meta_value FROM $table_name AS pm WHERE pm.meta_key = '_gsheet_zip' AND pm.meta_value IN ('" . implode("','", $selected_zip) . "')
    ");
    $matched_zip_results = $wpdb->get_results($matched_zip_query);
    foreach ($matched_zip_results as $result) {
      $matched_zip[] = $result->meta_value;
    }
  } */

/*   // Array to store matched ZIP codes
  $matched_zip = array();

  // Filter ZIP codes based on criteria
  if (!empty($selected_zip)) {
    $matched_zip_query = $wpdb->prepare("
      SELECT DISTINCT pm.meta_value 
      FROM $table_name AS pm 
      WHERE pm.meta_key = '_gsheet_zip' 
      AND pm.meta_value IN ('" . implode("','", $selected_zip) . "')
    ");
    $matched_zip_results = $wpdb->get_results($matched_zip_query);
    foreach ($matched_zip_results as $result) {
      $matched_zip[] = $result->meta_value;
    }
  } */

  // Prepare query conditions based on selected filters
  $conditions = array();

  if (!empty($selected_zip)) {
    $conditions[] = $wpdb->prepare("post_id IN (SELECT post_id FROM $table_name WHERE meta_key = '_gsheet_zip' AND meta_value IN ('" . implode("','", $selected_zip) . "'))");
  }

  /* if (!empty($selected_broker_ids)) {
    $conditions[] = $wpdb->prepare("post_id IN (SELECT post_id FROM $table_name WHERE meta_key = '_gsheet_listing_agent' AND meta_value IN ('" . implode("','", $selected_broker_ids) . "'))");
  } */


  if (!empty($selected_broker_ids)) {
    $conditions[] = $wpdb->prepare("
    (post_id IN (
      SELECT post_id 
      FROM $table_name 
      WHERE meta_key = '_gsheet_listing_agent' 
      AND meta_value IN ('" . implode("','", $selected_broker_ids) . "')
    ) OR post_id IN (
      SELECT post_id 
      FROM $table_name 
      WHERE meta_key = '_buildout_listing_agent' 
      AND meta_value IN ('" . implode("','", $selected_broker_ids) . "')
    ))
  ");
  }

  if (!empty($selected_type)) {
    $conditions[] = $wpdb->prepare("post_id IN (SELECT post_id FROM $table_name WHERE meta_key = '_gsheet_listing_type' 
        AND meta_value IN ('" . implode("','", $selected_type) . "'))
    ");
  }

  if (!empty($selected_city)) {
    $conditions[] = $wpdb->prepare("
      post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_buildout_city' 
        AND meta_value IN ('" . implode("','", $selected_city) . "')
      )
    ");
  }

  if (!empty($selected_neighbourhoods)) {
    $conditions[] = $wpdb->prepare("
      post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_neighborhood' 
        AND meta_value IN ('" . implode("','", $selected_neighbourhoods) . "')
      )
    ");
  }

  if (!empty($selected_state)) {
    $conditions[] = $wpdb->prepare("
    (post_id IN (
      SELECT post_id 
      FROM $table_name 
      WHERE meta_key = '_gsheet_state' 
      AND meta_value IN ('" . implode("','", $selected_state) . "')
    ) OR post_id IN (
      SELECT post_id 
      FROM $table_name 
      WHERE meta_key = '_buildout_state' 
      AND meta_value IN ('" . implode("','", $selected_state) . "')
    ))
  ");
  }


  

  if (!empty($selected_vented)) {
    $conditions[] = $wpdb->prepare("
      post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_vented' 
        AND meta_value IN ('" . implode("','", $selected_vented) . "')
      )
    ");
  }

  // Apply conditions to the main query
  if (!empty($conditions)) {
    $query .= " AND post_id IN (
      SELECT post_id 
      FROM $table_name 
      WHERE " . implode(" AND ", $conditions) . "
    )";
  }

  // Fetch filtered ZIP codes
  $original_results = $wpdb->get_results($query);

  // Store filtered ZIP codes
  foreach ($original_results as $result) {
    $matched_zip[] = $result->meta_value;
  }

  // Prepare response data
  $data = array();
  foreach ($results_all as $result) {
    $zip = $result->meta_value;
    $is_matched = in_array($zip, $matched_zip);
    $data[] = array(
      'id' => $zip,
      'text' => $zip,
      'matched' => $is_matched // Store if ZIP code is matched or not
    );
  }

  // Send JSON response
  wp_send_json($data);
  wp_die();
}


// get state dropdown
add_action('wp_ajax_get_state_dropdown_cb', 'get_state_dropdown_cb_callback');
add_action('wp_ajax_nopriv_get_state_dropdown_cb', 'get_state_dropdown_cb_callback');

// get zipcode dropdown
function get_state_dropdown_cb_callback()
{
  $selected_broker_ids = isset($_POST['broker_ids']) ? $_POST['broker_ids'] : array();
  $selected_city = isset($_POST['_buildout_city']) ? $_POST['_buildout_city'] : array();
  $selected_use = isset($_POST['_gsheet_use']) ? $_POST['_gsheet_use'] : array();
  $selected_neighbourhoods = isset($_POST['_gsheet_neighborhood']) ? $_POST['_gsheet_neighborhood'] : array();
  $selected_zip = isset($_POST['_gsheet_zip']) ? $_POST['_gsheet_zip'] : array();
  $selected_state = isset($_POST['_gsheet_state']) ? $_POST['_gsheet_state'] : array();
  $selected_vented = isset($_POST['_gsheet_vented']) ? $_POST['_gsheet_vented'] : array();

  global $wpdb;
  $table_name = $wpdb->prefix . 'postmeta';
  $meta_key = '_gsheet_state';

  //$query = "SELECT DISTINCT meta_value FROM $table_name WHERE meta_key = %s ";
  //$query = "SELECT DISTINCT meta_value FROM $table_name WHERE (meta_key = %s OR meta_key = '_buildout_state') ";

  //$query = "SELECT DISTINCT TRIM(meta_value) AS meta_value FROM $table_name WHERE meta_key = '_gsheet_state' OR meta_key = '_buildout_state' ";
  $post_table = $wpdb->prefix . 'posts';
  $query = "SELECT DISTINCT TRIM(pm.meta_value) AS meta_value 
          FROM $table_name pm 
          INNER JOIN $post_table p ON pm.post_id = p.ID 
          WHERE (pm.meta_key = '_gsheet_state' OR pm.meta_key = '_buildout_state') 
          AND p.post_status = 'publish' 
          AND p.post_type = 'properties'";

  // Parameters for the prepared statement
  $params = array($meta_key);

  if (!empty($selected_zip)) {
    $query .= " AND post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_zip' 
        AND meta_value IN ('" . implode("','", $selected_zip) . "')
    )";
  }

  // Adding conditions for selected broker IDs if not empty
/*   if (!empty($selected_broker_ids)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_listing_agent' 
          AND meta_value IN ('" . implode("','", $selected_broker_ids) . "')
      )";
  } */


  if (!empty($selected_broker_ids)) {
    $broker_ids_str = implode("','", $selected_broker_ids);
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE (meta_key = '_gsheet_listing_agent' AND meta_value IN ('$broker_ids_str'))
             OR (meta_key = '_buildout_listing_agent' AND meta_value IN ('$broker_ids_str'))
      )";
}



/*   $selected_type = isset($_POST['selected_type']) ? $_POST['selected_type'] : '';
  if (!empty($selected_type) && empty(isset($_POST['clear'])) ) {
    $query .= " AND post_id IN (SELECT post_id FROM $table_name WHERE meta_key = '_gsheet_listing_type' AND meta_value IN ('" . implode("','", $selected_type) . "'))";
  } */
  // Adding conditions for selected city if not empty
  if (!empty($selected_city)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_buildout_city' 
          AND meta_value IN ('" . implode("','", $selected_city) . "')
      )";
  }

  // Adding conditions for selected use if not empty
  if (!empty($selected_use)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_use' 
          AND meta_value IN ('" . implode("','", $selected_use) . "')
      )";
  }

  // Adding conditions for selected neighbourhoods if not empty
  if (!empty($selected_neighbourhoods)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_neighborhood' 
          AND meta_value IN ('" . implode("','", $selected_neighbourhoods) . "')
      )";
  }

  // Adding conditions for selected state if not empty
  // if (!empty($selected_state)) {
  //     $query .= " AND post_id IN (
  //         SELECT post_id 
  //         FROM $table_name 
  //         WHERE meta_key = '_gsheet_state' 
  //         AND meta_value IN ('" . implode("','", $selected_state) . "')
  //     )";
  // }

  // Adding conditions for selected vented if not empty
  if (!empty($selected_vented)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_vented' 
          AND meta_value IN ('" . implode("','", $selected_vented) . "')
      )";
  }


  // Preparing the query
  $query = $wpdb->prepare($query, $params);

  // Fetching results from the database
  $original_results = $wpdb->get_results($query);

  // Array to store the values obtained from the first query
  $matched_zip = array();

  // Storing the values obtained from the first query into the array
  if ($original_results) {
    foreach ($original_results as $result) {
      $matched_zip[] = $result->meta_value;
    }
  }

  // Fetching all results from the database
  // $query_all = $wpdb->prepare("SELECT DISTINCT meta_value FROM $table_name WHERE meta_key = %s", $meta_key);
  $query_all = "SELECT DISTINCT TRIM(meta_value) AS meta_value FROM $table_name WHERE meta_key = '_gsheet_state' OR meta_key = '_buildout_state' ";
  $results_all = $wpdb->get_results($query_all);
  $data = array();

  foreach ($results_all as $result) {
    $zip = $result->meta_value;
    $is_matched = in_array($zip, $matched_zip);
    $data[] = array(
      'id' => $zip,
      'text' => $zip,
      'matched' => $is_matched // Store if ZIP code is matched or not
    );
  }

  // Send JSON response
  wp_send_json($data);
  wp_die();
}



// get zipcode dropdown
add_action('wp_ajax_get_buildout_dropdown_cb', 'get_buildout_dropdown_cb_callback');
add_action('wp_ajax_nopriv_get_buildout_dropdown_cb', 'get_buildout_dropdown_cb_callback');
// get zipcode dropdown
function get_buildout_dropdown_cb_callback()
{
  $selected_broker_ids = isset($_POST['broker_ids']) ? $_POST['broker_ids'] : array();
  $selected_city = isset($_POST['_buildout_city']) ? $_POST['_buildout_city'] : array();
  $selected_use = isset($_POST['_gsheet_use']) ? $_POST['_gsheet_use'] : array();
  $selected_neighbourhoods = isset($_POST['_gsheet_neighborhood']) ? $_POST['_gsheet_neighborhood'] : array();
  $selected_zip = isset($_POST['_gsheet_zip']) ? $_POST['_gsheet_zip'] : array();
  $selected_state = isset($_POST['_gsheet_state']) ? $_POST['_gsheet_state'] : array();
  $selected_vented = isset($_POST['_gsheet_vented']) ? $_POST['_gsheet_vented'] : array();

  global $wpdb;
  $table_name = $wpdb->prefix . 'postmeta';
  $meta_key = '_buildout_city';

  //$query = "SELECT DISTINCT meta_value FROM $table_name WHERE meta_key = %s ";
  $post_table = $wpdb->prefix . 'posts';
  $query = "SELECT DISTINCT pm.meta_value 
          FROM $table_name pm 
          INNER JOIN $post_table p ON pm.post_id = p.ID 
          WHERE pm.meta_key = %s 
          AND p.post_status = 'publish' 
          AND p.post_type = 'properties' ";

  // Parameters for the prepared statement
  $params = array($meta_key);

  if (!empty($selected_zip)) {
    $query .= " AND post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_zip' 
        AND meta_value IN ('" . implode("','", $selected_zip) . "')
    )";
  }

  // Adding conditions for selected broker IDs if not empty
/*   if (!empty($selected_broker_ids)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_listing_agent' 
          AND meta_value IN ('" . implode("','", $selected_broker_ids) . "')
      )";
  } */


  if (!empty($selected_broker_ids)) {
    $broker_ids_str = implode("','", $selected_broker_ids);
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE (meta_key = '_gsheet_listing_agent' AND meta_value IN ('$broker_ids_str'))
             OR (meta_key = '_buildout_listing_agent' AND meta_value IN ('$broker_ids_str'))
      )";
}


  // Adding conditions for selected city if not empty
  /* if (!empty($selected_city)) {
      $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_buildout_city' 
          AND meta_value IN ('" . implode("','", $selected_city) . "')
      )";
  } */

/*   $selected_type = isset($_POST['selected_type']) ? $_POST['selected_type'] : array();
  if (!empty($selected_type)) {
    $query .= " AND post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_listing_type' 
        AND meta_value IN ('" . implode("','", $selected_type) . "')
    )";
  } */

  // Adding conditions for selected use if not empty
  if (!empty($selected_use)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_use' 
          AND meta_value IN ('" . implode("','", $selected_use) . "')
      )";
  }

  // Adding conditions for selected neighbourhoods if not empty
  if (!empty($selected_neighbourhoods)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_neighborhood' 
          AND meta_value IN ('" . implode("','", $selected_neighbourhoods) . "')
      )";
  }

  // Adding conditions for selected state if not empty
  // if (!empty($selected_state)) {
  //     $query .= " AND post_id IN (
  //         SELECT post_id 
  //         FROM $table_name 
  //         WHERE meta_key = '_gsheet_state' 
  //         AND meta_value IN ('" . implode("','", $selected_state) . "')
  //     )";
  // }

  if (!empty($selected_state)) {
    $query .= " AND (post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_state' 
        AND meta_value IN ('" . implode("','", $selected_state) . "')
    ) OR post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_buildout_state' 
        AND meta_value IN ('" . implode("','", $selected_state) . "')
    ))";
  }


  // Adding conditions for selected vented if not empty
  if (!empty($selected_vented)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_vented' 
          AND meta_value IN ('" . implode("','", $selected_vented) . "')
      )";
  }




  // Preparing the query
  $query = $wpdb->prepare($query, $params);

  // Fetching results from the database
  $original_results = $wpdb->get_results($query);

  // Array to store the values obtained from the first query
  $matched_zip = array();

  // Storing the values obtained from the first query into the array
  if ($original_results) {
    foreach ($original_results as $result) {
      $matched_zip[] = $result->meta_value;
    }
  }

  // Fetching all results from the database
  //$query_all = $wpdb->prepare("SELECT DISTINCT meta_value FROM $table_name WHERE meta_key = %s", $meta_key);
  $query_all = $wpdb->prepare("
    SELECT DISTINCT meta_value 
    FROM $table_name 
    WHERE meta_key = %s 
    AND post_id IN (SELECT ID FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND post_type = 'properties')
", $meta_key);

  $results_all = $wpdb->get_results($query_all);
  $data = array();

  foreach ($results_all as $result) {
    $zip = $result->meta_value;
    $is_matched = in_array($zip, $matched_zip);
    $data[] = array(
      'id' => $zip,
      'text' => $zip,
      'matched' => $is_matched // Store if ZIP code is matched or not
    );
  }

  // Send JSON response
  wp_send_json($data);
  wp_die();
}



// get zipcode dropdown
add_action('wp_ajax_get_vented_dropdown_cb', 'get_vented_dropdown_cb_callback');
add_action('wp_ajax_nopriv_get_vented_dropdown_cb', 'get_vented_dropdown_cb_callback');
// get zipcode dropdown
function get_vented_dropdown_cb_callback()
{
  $selected_broker_ids = isset($_POST['broker_ids']) ? $_POST['broker_ids'] : array();
  $selected_city = isset($_POST['_buildout_city']) ? $_POST['_buildout_city'] : array();
  $selected_use = isset($_POST['_gsheet_use']) ? $_POST['_gsheet_use'] : array();
  $selected_neighbourhoods = isset($_POST['_gsheet_neighborhood']) ? $_POST['_gsheet_neighborhood'] : array();
  $selected_zip = isset($_POST['_gsheet_zip']) ? $_POST['_gsheet_zip'] : array();
  $selected_state = isset($_POST['_gsheet_state']) ? $_POST['_gsheet_state'] : array();
  $selected_vented = isset($_POST['_gsheet_vented']) ? $_POST['_gsheet_vented'] : array();

  global $wpdb;
  $table_name = $wpdb->prefix . 'postmeta';
  $post_table = $wpdb->prefix . 'posts';
  $meta_key = '_gsheet_vented';

  //$query = "SELECT DISTINCT meta_value FROM $table_name WHERE meta_key = %s ";

  $query = "SELECT DISTINCT pm.meta_value 
          FROM $table_name pm 
          INNER JOIN $post_table p ON pm.post_id = p.ID 
          WHERE pm.meta_key = %s 
          AND p.post_status = 'publish' 
          AND p.post_type = 'properties' ";

  // Parameters for the prepared statement
  $params = array($meta_key);

  if (!empty($selected_zip)) {
    $query .= " AND post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_zip' 
        AND meta_value IN ('" . implode("','", $selected_zip) . "')
    )";
  }

  // Adding conditions for selected broker IDs if not empty
/*   if (!empty($selected_broker_ids)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_listing_agent' 
          AND meta_value IN ('" . implode("','", $selected_broker_ids) . "')
      )";
  } */

  if (!empty($selected_broker_ids)) {
    $broker_ids_str = implode("','", $selected_broker_ids);
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE (meta_key = '_gsheet_listing_agent' AND meta_value IN ('$broker_ids_str'))
             OR (meta_key = '_buildout_listing_agent' AND meta_value IN ('$broker_ids_str'))
      )";
}

  // Adding conditions for selected city if not empty
  if (!empty($selected_city)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_buildout_city' 
          AND meta_value IN ('" . implode("','", $selected_city) . "')
      )";
  }

  $selected_type = isset($_POST['selected_type']) ? $_POST['selected_type'] : array();
  if (!empty($selected_type)) {
    $query .= " AND post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_listing_type' 
        AND meta_value IN ('" . implode("','", $selected_type) . "')
    )";
  }

  // Adding conditions for selected use if not empty
  if (!empty($selected_use)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_use' 
          AND meta_value IN ('" . implode("','", $selected_use) . "')
      )";
  }

  // Adding conditions for selected neighbourhoods if not empty
  if (!empty($selected_neighbourhoods)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_neighborhood' 
          AND meta_value IN ('" . implode("','", $selected_neighbourhoods) . "')
      )";
  }

  if (!empty($selected_state)) {
    $query .= " AND (post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_state' 
        AND meta_value IN ('" . implode("','", $selected_state) . "')
    ) OR post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_buildout_state' 
        AND meta_value IN ('" . implode("','", $selected_state) . "')
    ))";
  }

  // Preparing the query
  $query = $wpdb->prepare($query, $params);

  // Fetching results from the database
  $original_results = $wpdb->get_results($query);

  // Array to store the values obtained from the first query
  $matched_zip = array();

  // Storing the values obtained from the first query into the array
  if ($original_results) {
    foreach ($original_results as $result) {
      $matched_zip[] = $result->meta_value;
    }
  }

  // Fetching all results from the database
  // $query_all = $wpdb->prepare("SELECT DISTINCT meta_value FROM $table_name WHERE meta_key = %s", $meta_key);
  $query_all = $wpdb->prepare("
    SELECT DISTINCT meta_value 
    FROM $table_name 
    WHERE meta_key = %s 
    AND post_id IN (SELECT ID FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND post_type = 'properties')
", $meta_key);

  $results_all = $wpdb->get_results($query_all);
  $data = array();

  foreach ($results_all as $result) {
    $zip = $result->meta_value;
    $is_matched = in_array($zip, $matched_zip);
    $data[] = array(
      'id' => $zip,
      'text' => $zip,
      'matched' => $is_matched // Store if ZIP code is matched or not
    );
  }

  // Send JSON response
  wp_send_json($data);
  wp_die();
}

add_action('wp_ajax_get_agents_dropdown_cb', 'get_agents_dropdown_cb_callback');
add_action('wp_ajax_nopriv_get_agents_dropdown_cb', 'get_agents_dropdown_cb_callback');

function get_agents_dropdown_cb_callback()
{
  $selected_broker_ids = isset($_POST['broker_ids']) ? $_POST['broker_ids'] : array();
  $selected_city = isset($_POST['_buildout_city']) ? $_POST['_buildout_city'] : array();
  $selected_use = isset($_POST['_gsheet_use']) ? $_POST['_gsheet_use'] : array();
  $selected_neighbourhoods = isset($_POST['_gsheet_neighborhood']) ? $_POST['_gsheet_neighborhood'] : array();
  $selected_zip = isset($_POST['_gsheet_zip']) ? $_POST['_gsheet_zip'] : array();
  $selected_state = isset($_POST['_gsheet_state']) ? $_POST['_gsheet_state'] : array();
  $selected_vented = isset($_POST['_gsheet_vented']) ? $_POST['_gsheet_vented'] : array();
  $selected_type = isset($_POST['selected_type']) ? $_POST['selected_type'] : array();
  #_gsheet_listing_type input[type="checkbox"]:checked
  global $wpdb;
  $table_name = $wpdb->prefix . 'postmeta';
  $post_table = $wpdb->prefix . 'posts';
  $meta_key = '_gsheet_listing_agent';

  $query = "SELECT DISTINCT pm.meta_value 
          FROM $table_name AS pm 
          INNER JOIN $post_table AS p ON pm.post_id = p.ID 
          WHERE pm.meta_key = %s 
          AND p.post_status = 'publish' 
          AND p.post_type = 'properties'";

  // Parameters for the prepared statement
  $params = array($meta_key);
  /*   if (!empty($selected_broker_ids)) {
    $query .= " AND post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_listing_agent' 
        AND meta_value IN ('" . implode("','", $selected_zip) . "')
    )";
  } */
  if (!empty($selected_zip)) {
    $query .= " AND post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_zip' 
        AND meta_value IN ('" . implode("','", $selected_zip) . "')
    )";
  }

  if (!empty($selected_type)) {
    $query .= " AND post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_listing_type' 
        AND meta_value IN ('" . implode("','", $selected_type) . "')
    )";
  }

  // Adding conditions for selected city if not empty
  if (!empty($selected_city)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_buildout_city' 
          AND meta_value IN ('" . implode("','", $selected_city) . "')
      )";
  }

  // Adding conditions for selected use if not empty
  if (!empty($selected_use)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_use' 
          AND meta_value IN ('" . implode("','", $selected_use) . "')
      )";
  }

  // Adding conditions for selected neighbourhoods if not empty
  if (!empty($selected_neighbourhoods)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_neighborhood' 
          AND meta_value IN ('" . implode("','", $selected_neighbourhoods) . "')
      )";
  }

  // Adding conditions for selected state if not empty
  /*  if (!empty($selected_state)) {
      $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_state' 
          AND meta_value IN ('" . implode("','", $selected_state) . "')
      )";
  } */

  if (!empty($selected_state)) {
    $query .= " AND (post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_state' 
        AND meta_value IN ('" . implode("','", $selected_state) . "')
    ) OR post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_buildout_state' 
        AND meta_value IN ('" . implode("','", $selected_state) . "')
    ))";
  }

  // Adding conditions for selected vented if not empty
  if (!empty($selected_vented)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_vented' 
          AND meta_value IN ('" . implode("','", $selected_vented) . "')
      )";
  }

  // Preparing the query
  $query = $wpdb->prepare($query, $params);

  // Fetching results from the database
  $original_results = $wpdb->get_results($query);

  // Array to store the values obtained from the first query
  $matched_zip = array();

  // Storing the values obtained from the first query into the array
  if ($original_results) {
    foreach ($original_results as $result) {
      $matched_zip[] = $result->meta_value;
    }
  }

  // Fetching all results from the database
  //  $query_all = $wpdb->prepare("SELECT DISTINCT meta_value FROM $table_name WHERE meta_key = %s", $meta_key);
  $query_all = $wpdb->prepare("
    SELECT DISTINCT pm.meta_value 
    FROM $table_name AS pm 
    INNER JOIN {$wpdb->prefix}posts AS p ON pm.post_id = p.ID 
    WHERE pm.meta_key = %s 
    AND p.post_status = 'publish' 
    AND p.post_type = 'properties'
", $meta_key);
  $results_all = $wpdb->get_results($query_all);
  $data = array();

  foreach ($results_all as $result) {
    $zip = $result->meta_value;
    $is_matched = in_array($zip, $matched_zip);
    $data[] = array(
      'id' => $zip,
      'text' => $zip,
      'matched' => $is_matched // Store if ZIP code is matched or not
    );
  }

  // Send JSON response
  wp_send_json($data);
  wp_die();
}



//get_neighborhood_dropdown_cb
add_action('wp_ajax_get_neighborhood_dropdown_cb', 'get_neighborhood_dropdown_cb_callback');
add_action('wp_ajax_nopriv_get_neighborhood_dropdown_cb', 'get_neighborhood_dropdown_cb_callback');
// get zipcode dropdown
function get_neighborhood_dropdown_cb_callback()
{
  $selected_broker_ids = isset($_POST['broker_ids']) ? $_POST['broker_ids'] : array();
  $selected_city = isset($_POST['_buildout_city']) ? $_POST['_buildout_city'] : array();
  $selected_use = isset($_POST['_gsheet_use']) ? $_POST['_gsheet_use'] : array();
  $selected_neighbourhoods = isset($_POST['_gsheet_neighborhood']) ? $_POST['_gsheet_neighborhood'] : array();
  $selected_zip = isset($_POST['_gsheet_zip']) ? $_POST['_gsheet_zip'] : array();
  $selected_state = isset($_POST['_gsheet_state']) ? $_POST['_gsheet_state'] : array();
  $selected_vented = isset($_POST['_gsheet_vented']) ? $_POST['_gsheet_vented'] : array();

  global $wpdb;
  $table_name = $wpdb->prefix . 'postmeta';
  $meta_key = '_gsheet_neighborhood';

  $query = $wpdb->prepare("
  SELECT DISTINCT meta_value 
  FROM $table_name AS pm
  INNER JOIN {$wpdb->prefix}posts AS p ON pm.post_id = p.ID 
  WHERE pm.meta_key = %s 
  AND p.post_status = 'publish' 
  AND p.post_type = 'properties'
", $meta_key);


  // Parameters for the prepared statement
  $params = array($meta_key);

  if (!empty($selected_zip)) {
    $query .= " AND post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_zip' 
        AND meta_value IN ('" . implode("','", $selected_zip) . "')
    )";
  }

  // Adding conditions for selected broker IDs if not empty
/*   if (!empty($selected_broker_ids)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_listing_agent' 
          AND meta_value IN ('" . implode("','", $selected_broker_ids) . "')
      )";
  } */

  if (!empty($selected_broker_ids)) {
    $broker_ids_str = implode("','", $selected_broker_ids);
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE (meta_key = '_gsheet_listing_agent' AND meta_value IN ('$broker_ids_str'))
             OR (meta_key = '_buildout_listing_agent' AND meta_value IN ('$broker_ids_str'))
      )";
}
  // Adding conditions for selected city if not empty
  if (!empty($selected_city)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_buildout_city' 
          AND meta_value IN ('" . implode("','", $selected_city) . "')
      )";
  }

  $selected_type = isset($_POST['selected_type']) ? $_POST['selected_type'] : array();
  if (!empty($selected_type)) {
    $query .= " AND post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_listing_type' 
        AND meta_value IN ('" . implode("','", $selected_type) . "')
    )";
  }

  // Adding conditions for selected use if not empty
  if (!empty($selected_use)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_use' 
          AND meta_value IN ('" . implode("','", $selected_use) . "')
      )";
  }

  // Adding conditions for selected neighbourhoods if not empty
  /*   if (!empty($selected_neighbourhoods)) {
      $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_neighborhood' 
          AND meta_value IN ('" . implode("','", $selected_neighbourhoods) . "')
      )";
  } */

  // Adding conditions for selected state if not empty
  // if (!empty($selected_state)) {
  //     $query .= " AND post_id IN (
  //         SELECT post_id 
  //         FROM $table_name 
  //         WHERE meta_key = '_gsheet_state' 
  //         AND meta_value IN ('" . implode("','", $selected_state) . "')
  //     )";
  // }

  if (!empty($selected_state)) {
    $query .= " AND (post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_state' 
        AND meta_value IN ('" . implode("','", $selected_state) . "')
    ) OR post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_buildout_state' 
        AND meta_value IN ('" . implode("','", $selected_state) . "')
    ))";
  }

  // Adding conditions for selected vented if not empty
  if (!empty($selected_vented)) {
    $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_vented' 
          AND meta_value IN ('" . implode("','", $selected_vented) . "')
      )";
  }
  // Preparing the query
  $query = $wpdb->prepare($query, $params);

  // Fetching results from the database
  $original_results = $wpdb->get_results($query);

  // Array to store the values obtained from the first query
  $matched_zip = array();

  // Storing the values obtained from the first query into the array
  if ($original_results) {
    foreach ($original_results as $result) {
      $matched_zip[] = $result->meta_value;
    }
  }

  // Fetching all results from the database
  //$query_all = $wpdb->prepare("SELECT DISTINCT meta_value FROM $table_name WHERE meta_key = %s", $meta_key);
  $query_all = $wpdb->prepare("
    SELECT DISTINCT meta_value 
    FROM $table_name 
    WHERE meta_key = %s 
    AND post_id IN (SELECT ID FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND post_type = 'properties')
", $meta_key);

  $results_all = $wpdb->get_results($query_all);
  $data = array();

  foreach ($results_all as $result) {
    $zip = $result->meta_value;
    $is_matched = in_array($zip, $matched_zip);
    $data[] = array(
      'id' => $zip,
      'text' => $zip,
      'matched' => $is_matched // Store if ZIP code is matched or not
    );
  }

  // Send JSON response
  wp_send_json($data);
  wp_die();
}
