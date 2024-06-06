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

  // Step 3: Use WP_Query to find the broker post with the user_id meta key
  $args = array(
      'post_type' => 'brokers',
      'meta_query' => array(
          array(
              'key' => 'user_id',
              'value' => $broker_id,
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


function get_pricesf_minmax($type="min", $formatted=true) {
  global $wpdb;
  
/*   $max_rent= $wpdb->get_var("
  SELECT MAX(CAST(pm.meta_value AS UNSIGNED)) 
  FROM $wpdb->postmeta pm
  INNER JOIN $wpdb->posts p ON pm.post_id = p.ID
  WHERE pm.meta_key = '__gsheet__monthly_rent'
  AND p.post_type = 'properties'
"); */

$max_rent = $wpdb->get_var("SELECT MAX(CAST(CAST(REPLACE(REPLACE(meta_value, '$', ''), ',', '') AS DECIMAL(10, 2)) AS UNSIGNED)) FROM $wpdb->postmeta WHERE meta_key = '_gsheet_price_sf'");


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

// add_shortcode('TSC-inventory-pub', 'drt_shortcode');


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

function drt_shortcode($_atts)
{
  // Start output buffering
  $defaults = array(
    'state' => ''
  );
  
  $atts = shortcode_atts($defaults , $_atts);
  
  $markers_data = [];
  ob_start();
?>
<style>
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
    


//Event listeners for checkboxes and search input
/* $('#type_for_sale, #type_for_lease').on('change', function() {
  $('#search-by-text').trigger('keyup');
  // combinedFilter();
  
}); */


document.getElementById("filter-clear11").addEventListener("click", function() {
    // Clear all selected values from select2 dropdowns
    $('#select2_agents').val(null).trigger('change');
    $('#select2_uses').val(null).trigger('change');
    $('#select2_neighborhoods').val(null).trigger('change');
    $('#select2_zipcodes').val(null).trigger('change');
    $('#select2_cities').val(null).trigger('change');
    $('#select2_states').val(null).trigger('change');
    $('#select2_vented').val(null).trigger('change');
    

      // Get the checkboxes
      var forSaleCheckbox = document.getElementById('type_for_sale');
    var forLeaseCheckbox = document.getElementById('type_for_lease');

    // Reset and check the checkboxes
    forSaleCheckbox.checked = true;
    forLeaseCheckbox.checked = true;

    // Remove the disabled attribute if present
    if (forSaleCheckbox.hasAttribute('disabled')) {
        forSaleCheckbox.removeAttribute('disabled');
    }
    if (forLeaseCheckbox.hasAttribute('disabled')) {
        forLeaseCheckbox.removeAttribute('disabled');
    }
    //$('#_gsheet_listing_type input[type="checkbox"]').prop('checked', true);
        // $("#for_sale,#for_lease").hide();
        // Reset ui-slider-range for price-range2
        $('#price-range .ui-slider-range,#price-range2 .ui-slider-range, #price-range3 .ui-slider-range').css({
          'left': '0%',
          'width': '100%'
        });

        var rangeHiddenFields = $("#price-range-selected,#rent-range-selected,#size-range-selected");
        rangeHiddenFields.attr("data-clear", "1");
        
        
        // price
        $("#price-range" ).slider( "option", "max",  $("#price-range").data('max') );
        $("#price-range" ).slider( "option", "min",  $("#price-range").data('min') );
        $("#price-range").slider("values", [$("#price-range").data('min'), $("#price-range").data('max')]);
        
        // rent 
        $("#price-range3" ).slider( "option", "max",  $("#price-range3").data('max') );
        $("#price-range3" ).slider( "option", "min",  $("#price-range3").data('min') );
        $("#price-range3").slider("values", [$("#price-range3").data('min'), $("#price-range3").data('max')]);
        
        // size
        $("#price-range2" ).slider( "option", "max",  $("#price-range2").data('max') );
        $("#price-range2" ).slider( "option", "min",  $("#price-range2").data('min') );
        $("#price-range2").slider("values", [$("#price-range2").data('min'), $("#price-range2").data('max')]);
        
        // resetting inputs
        $('.range-inputs').each(function(){
            $(this).val($(this).attr('data-default'));
        });
        $("#search-by-text-new").val("");
});


      $('#filter-clear111').on('click', function() {
        //    alert('hello test');
        //$('#tristate-input').val("");
        $('#_gsheet_use, #tri_agents, #_gsheet_neighborhood,#search-by-text, #_gsheet_zip,#_buildout_city, #_gsheet_state, #_gsheet_vented,#price-range2,#price-range,#price-range3').val(null).trigger('change');
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
        
        
        // price
        $("#price-range" ).slider( "option", "max",  $("#price-range").data('max') );
        $("#price-range" ).slider( "option", "min",  $("#price-range").data('min') );
        $("#price-range").slider("values", [$("#price-range").data('min'), $("#price-range").data('max')]);
        
        // rent 
        $("#price-range3" ).slider( "option", "max",  $("#price-range3").data('max') );
        $("#price-range3" ).slider( "option", "min",  $("#price-range3").data('min') );
        $("#price-range3").slider("values", [$("#price-range3").data('min'), $("#price-range3").data('max')]);
        
        // size
        $("#price-range2" ).slider( "option", "max",  $("#price-range2").data('max') );
        $("#price-range2" ).slider( "option", "min",  $("#price-range2").data('min') );
        $("#price-range2").slider("values", [$("#price-range2").data('min'), $("#price-range2").data('max')]);
        
        // resetting inputs
        $('.range-inputs').each(function(){
            $(this).val($(this).attr('data-default'));
        });
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
        // $('#price-range .ui-slider-handle, #price-range2 .ui-slider-handle, #price-range3 .ui-slider-handle').each(function() {
        //   $(this).css('left', '0%'); // Adjust this percentage as needed
        // });
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
      //,#_gsheet_listing_type
      $('#_gsheet_use,#_gsheet_zip,#_gsheet_state,#_buildout_city,#_gsheet_vented,#_gsheet_neighborhood').on('input', function() {
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
        var form = $('#tri-popup-form');
        var closebutton =$("#tcr-popup-close-button");
        $('input[name="get_properties_id"]').each(function() {
          
          var parent = $(this).parent('.propertylisting-content:visible');
          if(parent.length>0){
            var value = $(this).val();
            get_filter_poist_id.push(value);
          }
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
              
              $('#map_layer_show_message').fadeOut(600)
                .promise() 
                .done(function() {
                  form.get(0).reset();
                  $('#map-layer-content').fadeIn(300); 
                  closebutton.trigger('click');
                });
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

              <!-- <div class="tristate_cr_d-flex checkbox-wrapper" id="_gsheet_listing_type_new">
                <div>
                  <label for="for Sale">For Sale</label>
                  <input type="checkbox" name="listing_type_new" value="for Sale" id="type_for_sale_new">
                </div>
                <div>
                  <label for="for Lease">For Lease</label>
                  <input type="checkbox" name="listing_type_new" value="for Lease" id="type_for_lease_new">
                </div>
              </div> -->
            <?php
            }

            ?>


            <!-- Select2 Elements -->

            <div id="select-container">
    <!-- Dynamically created select elements will be placed here -->
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
                  <input type="hidden" name="price-range" data-live="0" data-clear="0" id="price-range-selected">
                </div>

              </div>
              <!-- For Rent -->
       
              <div>
                <div class="slider-box" id="for_lease">
                  <label for="priceRange">Price per SF:</label>
                  <input style="display:none" type="text" id="priceRange3" readonly>
                  <div class="range-min-max">
                    <input type="text" class="range-inputs" id="rent-range-min"data-default="<?php echo get_pricesf_minmax(); ?>" name="price_range_min" value="<?php echo get_pricesf_minmax(); ?>">
                    <input type="text" class="range-inputs" id="rent-range-max" data-default="<?php echo get_pricesf_minmax('max');?>" name="price_range_max" value="<?php echo get_pricesf_minmax('max'); ?>">
                  </div>
                  <div id="price-range3" class="slider" data-min="<?php echo get_pricesf_minmax('min',false) ?>" data-max="<?php echo get_pricesf_minmax('max',false); ?>"></div>
                  <input type="hidden" name="rent-range" data-clear="0" id="rent-range-selected">
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
                <input type="hidden" name="size-range" id="size-range-selected" data-live="0" data-clear="0">
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
                        <form  id="tri-popup-form" method="POST">
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

      <div id="get_all_listing_data">
      <div id="get_all_agents"></div>
      <div id="get_all_uses"></div>
      <div id="get_all_neighborhood"></div>
      <div id="get_all_zipcode"></div>
        <div id="get_all_cities"></div>
        <div id="get_all_state"></div>
        <div id="get_all_vented"></div>
       

      </div>
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
        
        if (!empty($atts['state'])) {
        
          $args['meta_query'][] = array(
            'relation' => 'OR',
            array(
                'key'     => '_buildout_state',
                'value'   => esc_attr($atts['state']),
                'compare' => '=',
            ),
            array(
                'key'     => '_gsheet_state', 
                'value'   => esc_attr($atts['state']),    
                'compare' => '=',              
                       
            ),
          );
        }
          $search_query = new WP_Query($args);
          $default_found_results = $search_query->found_posts;
        ?>
        <div id="menu-btn"><i class="fa fa-angle-left"></i></div>
        <div class="right-map">
          <!-- <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d407542.86304287874!2d-74.32724652492182!3d40.69942908913206!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!z4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2snp!4v1711702301417!5m2!1sen!2snp" allowfullscreen="allowFullScreen" width="100%" height="450px" style="position: relative; display: block;"></iframe> -->
          <div id="tristate-map" style="height:600px; width:100%;position:relative;display:block;"></div>
        </div>
        <div id="search_count_area">
        <!-- <div class="search-by-text MuiFormControl-root MuiTextField-root css-i44wyl">
            <input class="MuiInputBase-input MuiOutlinedInput-input css-1x5jdmq" aria-invalid="false" id="search-by-text" placeholder="search by keyword" type="text">
          </div> -->

          <div class="search-by-text-new MuiFormControl-root MuiTextField-root css-i44wyl">
            <input class="MuiInputBase-input MuiOutlinedInput-input css-1x5jdmq" aria-invalid="false" id="search-by-text-new" placeholder="search by keyword" type="text">
          </div>
          <!-- <div class="MuiFormControl-root MuiTextField-root css-i44wyl">
            <input aria-invalid="false" id="tristate-input" placeholder="search by keyword old" type="text" class="MuiInputBase-input MuiOutlinedInput-input css-1x5jdmq">
          </div> -->
          <div class="column-select-result-count">
      <div id="tristate-result-count" data-count="<?php echo __total(); ?>">
            <?php //echo 'Showing ' . $default_found_results . ' of ' .$default_found_results . ' Listing' ?>
          
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

     <!--    <div class="search-by-text">
            <input aria-invalid="false" id="search-by-text" placeholder="search by text" type="text">
          </div> -->

        <div class="post-output"></div>




        <div class="property-list-wrapper">
          <div class="MuiBox-root">
            <div class="MuiStack-root property-filter css-12xuzbq" id="propertylisting-content">

            <?php
              // Output the search results
              if ($search_query->have_posts()) {
                $loop = TRISTATECRLISTING_PLUGIN_DIR . 'templates/dr-loop.php';
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
<!-- dr new test for generate automatic options -->
<script>


$(document).ready(function() {


  $("#select2_agents, #select2_uses, #select2_neighborhoods, #select2_zipcodes, #select2_cities, #select2_states, #select2_vented").on('select2:opening', function(e) {
        filterListings();
    });


   // price range
	$("#price-range").slider({
		range: true,
		min: $("#price-range").data('min'),//get min val
		max: $("#price-range").data('max'),//get max val  
		values: [$("#price-range").data('min'), $("#price-range").data('max')],//postion slider val
		step: 1,
		slide: function (event, ui) {
		  $("#priceRange").val("$" + ui.values[0] + " - $" + ui.values[1]);
		  $("#price-range-min").val('$'+ui.values[0].toLocaleString());
		  $("#price-range-max").val('$'+ui.values[1].toLocaleString());
	   
		},
		change: function (event, ui) {
		  $("#price-range-selected").val(ui.values[0] + "-" + ui.values[1]);
		 
      
		},
	
	});
	
	  $("#price-range2").slider({
      range: true,
      min: $("#price-range2").data('min'),//get min val
      max: $("#price-range2").data('max'),//get max val  
      values: [$("#price-range2").data('min'), $("#price-range2").data('max')],//postion slider val
      step:1,
      slide: function (event, ui) {
      
        $("#priceRange2").val(
          "" + ui.values[0].toLocaleString() + " SF to " + ui.values[1].toLocaleString() + " SF "
        );
        $("#size-range-min").val(ui.values[0].toLocaleString()+ ' SF');
        $("#size-range-max").val(ui.values[1].toLocaleString() + " SF");
      },
      change: function (event, ui) {
        
        $("#size-range-selected").val(ui.values[0] + "-" + ui.values[1]);
        
        
      },
  });
  
  
  $("#price-range3").slider({
    range: true,
    min: $("#price-range3").data('min'),//get min val
    max: $("#price-range3").data('max'),//get max val  
    values: [$("#price-range3").data('min'), $("#price-range3").data('max')],
    step:1,
    slide: function (event, ui) {
      $("#priceRange3").val("$" + ui.values[0].toLocaleString() + " - $" + ui.values[1].toLocaleString());
      $("#rent-range-min").val("$" +ui.values[0].toLocaleString());
      $("#rent-range-max").val("$" +ui.values[1].toLocaleString());
    },
    change: function (event, ui) {
      $("#rent-range-selected").val(ui.values[0] + "-" + ui.values[1]);  
     
    },
  });

    // Extract unique values from the HTML for select2 options
    var agents = new Set();
    var uses = new Set();
    var neighborhoods = new Set();
    var zipcodes = new Set();
    var cities = new Set();
    var states = new Set();
    var vented = new Set();

    $(".propertylisting-content").each(function() {
        agents.add($(this).find("#tri_listing_agent").text().trim());
        uses.add($(this).find(".tri_use").text().trim());
        neighborhoods.add($(this).find("#tri_neighborhood").text().trim());
        zipcodes.add($(this).find("#tri_zip_code").text().trim());
        cities.add($(this).find("#tri_city").text().trim());
        states.add($(this).find("#tri_state").text().trim());
        vented.add($(this).find("#tri_vented").text().trim());
    });

    // Function to create select2 options
    function createSelect2Options(data) {
        var options = Array.from(data).sort().map(function(value) {
            return { id: value, text: value };
        });
        return options;
    }

    // Generate select2 options
    var selectOptions = {
        agents: createSelect2Options(agents),
        uses: createSelect2Options(uses),
        neighborhoods: createSelect2Options(neighborhoods),
        zipcodes: createSelect2Options(zipcodes),
        cities: createSelect2Options(cities),
        states: createSelect2Options(states),
        vented: createSelect2Options(vented)
    };

    $.each(selectOptions, function(key, options) {
        // Add label element
        $('<label>', {
            for: 'select2_' + key,
            text: key.charAt(0).toUpperCase() + key.slice(1) + ': '
        }).appendTo('#select-container');
        
        // Add select2 element
        $('<select>', {
            id: 'select2_' + key,
            name: 'select2_' + key + '[]',
            multiple: true
        }).appendTo('#select-container').select2({
            data: options,
            placeholder: ''
        }).on('change', function(e) {
            if (e.type === 'select2:select') {
                $(this).select2("close");
            }
            filterListings(key);
        }).on('select2:unselecting', function(e) {
            $(this).data('state', 'unselecting');
        }).on('select2:opening', function(e) {
            if ($(this).data('state') === 'unselecting') {
                $(this).removeData('state');
                e.preventDefault();
            }

        });
    });

    // Function to filter listings based on selected options and keyword
    function filterListings(changedSelect) {

//#select2_zipcodes,#select2_cities,#select2_states,#select2_vented,#search-by-text-new
        var selectedAgents = $('#select2_agents').val() || [];
        var selectedUses = $('#select2_uses').val() || [];
        var selectedNeighborhoods = $('#select2_neighborhoods').val() || [];
        var selectedZipcodes = $('#select2_zipcodes').val() || [];
        var selectedCities = $('#select2_cities').val() || [];
        var selectedStates = $('#select2_states').val() || [];
        var selectedVented = $('#select2_vented').val() || [];
        var keyword = $('#search-by-text-new').val().toLowerCase();

        var priceRange = $("#price-range" ).slider( "values" ).map(Number);
        var priceRangeSf=$("#price-range3").slider("values").map(Number);
        var sizeRangeSf = $("#price-range2").slider("values").map(Number);
        var displayedListings = 0;
        var priceArray =[0] , pricesfArray=[0] , minsizeArray=[0] , maxsizeArray= [0] ;
        console.log("priceSf: "+priceRangeSf+" sizeMax:"+sizeRangeSf+" price"+priceRange);




        var showForSale = $('#type_for_sale').is(':checked');
        var showForLease = $('#type_for_lease').is(':checked');

        var displayedListings = 0;

        $(".propertylisting-content").each(function() {
            var $listing = $(this);
            var showListing = true,
            price = parseFloat($(this).data('price')),
            priceSf = parseFloat($(this).data('pricesf')),
            sizeMax = parseFloat($(this).data('maxsize')),
            isBetweenMaxMinPrice = (price >= priceRange[0]) && (price <= priceRange[1]),
            isBetweenMaxMinPriceSf = (priceSf >= priceRangeSf[0]) && (priceSf <= priceRangeSf[1]),
            isBetweenMaxMinSize = (sizeMax >= sizeRangeSf[0]) && (sizeMax <= sizeRangeSf[1]);

            if (selectedAgents.length > 0 && !selectedAgents.includes($listing.find("#tri_listing_agent").text().trim())) {
                showListing = false;
            }

            if (selectedUses.length > 0 && !selectedUses.includes($listing.find(".tri_use").text().trim())) {
                showListing = false;
            }

            if (selectedNeighborhoods.length > 0 && !selectedNeighborhoods.includes($listing.find("#tri_neighborhood").text().trim())) {
                showListing = false;
            }

            if (selectedZipcodes.length > 0 && !selectedZipcodes.includes($listing.find("#tri_zip_code").text().trim())) {
                showListing = false;
            }

            if (selectedCities.length > 0 && !selectedCities.includes($listing.find("#tri_city").text().trim())) {
                showListing = false;
            }

            if (selectedStates.length > 0 && !selectedStates.includes($listing.find("#tri_state").text().trim())) {
                showListing = false;
            }

            if (selectedVented.length > 0 && !selectedVented.includes($listing.find("#tri_vented").text().trim())) {
                showListing = false;
            }

            if (keyword && !$listing.text().toLowerCase().includes(keyword)) {
                showListing = false;
            }

            if (!isBetweenMaxMinPrice) {
                showListing = false;
            }
            if(!isBetweenMaxMinPriceSf){
                showListing = false;
            }
            
            if(!isBetweenMaxMinSize){
                showListing =false;
            }

            var isForLease = $listing.find(".tri_for_lease").length > 0;
            var isForSale = $listing.find(".tri_for_sale").length > 0;

            if ((showForSale && isForSale) || (showForLease && isForLease) ){
                // Listing matches one of the selected types
            } else if (showForSale || showForLease) {
                // At least one of the checkboxes is checked but the listing doesn't match any
                showListing = false;
            }

            if (showListing) {
                $listing.show();

                priceArray.push(price);
                pricesfArray.push(priceSf);
                maxsizeArray.push(sizeMax);

                displayedListings++;
            } else {
                $listing.hide();
            }
        });

        // Update displayed listings count
        var totalListings = $(".propertylisting-content").length;
        $('#tristate-result-count').text('Showing ' + displayedListings + ' of ' + totalListings + ' Listings');

        $("#save_map_layer").text("SAVE " + displayedListings + " RESULTS TO A NEW MAP LAYER");
        // change markers on map
      
        
        var maxPrice = findMax(priceArray,'price-range')
        , maxsf = findMax(pricesfArray,'price-range3') 
        , maxSize=findMax(maxsizeArray,'price-range2');
        console.log("hamro test: "+maxsf);
        var dataSlided = $('#search-by-text-new').data('slided');
        console.log("#########"+priceArray);
        //price
       
        if(dataSlided !=='price-range'){
          $("#price-range" ).slider( "option", "values", [ 0, maxPrice] );
          $("#price-range-max").val('$' + maxPrice.toLocaleString());
        }
        //sf
        if(dataSlided !=='price-range3'){
          $('#price-range3').slider( "option", "values", [ 0, maxsf] );
          $("#rent-range-max").val('$'+maxsf.toLocaleString());
        }
        if(dataSlided !=='price-range2'){
          $('#price-range2').slider( "option", "values", [ 0, maxSize] );
          $("#size-range-max").val(maxSize.toLocaleString()+' SF');
        }
        get_markerData(false);
        console.log(changedSelect);
        if (changedSelect !== 'type_for_lease_unchecked' && changedSelect !== 'type_for_sale_unchecked') {
        updateSelect2Options(changedSelect);
        }
        // Display selected options in console
        // console.log('Selected Agents:', selectedAgents);
        // console.log('Selected Uses:', selectedUses);
        // console.log('Selected Neighborhoods:', selectedNeighborhoods);
        // console.log('Selected Zipcodes:', selectedZipcodes);
        // console.log('Selected Cities:', selectedCities);
        // console.log('Selected States:', selectedStates);
        // console.log('Selected Vented:', selectedVented);

      

    }
// #select2_agents , #select2_uses, #select2_neighborhoods, #select2_zipcodes, #select2_cities, #select2_states, #select2_vented

    function updateSelect2Options(changedSelect) {
        var selectedAgents = $('#select2_agents').val() || [];
        var selectedUses = $('#select2_uses').val() || [];
        var selectedNeighborhoods = $('#select2_neighborhoods').val() || [];
        var selectedZipcodes = $('#select2_zipcodes').val() || [];
        var selectedCities = $('#select2_cities').val() || [];
        var selectedStates = $('#select2_states').val() || [];
        var selectedVented = $('#select2_vented').val() || [];

        var filterValues = {
            agents: new Set(),
            uses: new Set(),
            neighborhoods: new Set(),
            zipcodes: new Set(),
            cities: new Set(),
            states: new Set(),
            vented: new Set()
        };

        $(".propertylisting-content:visible").each(function() {
            filterValues.agents.add($(this).find("#tri_listing_agent").text().trim());
            filterValues.uses.add($(this).find(".tri_use").text().trim());
            filterValues.neighborhoods.add($(this).find("#tri_neighborhood").text().trim());
            filterValues.zipcodes.add($(this).find("#tri_zip_code").text().trim());
            filterValues.cities.add($(this).find("#tri_city").text().trim());
            filterValues.states.add($(this).find("#tri_state").text().trim());
            filterValues.vented.add($(this).find("#tri_vented").text().trim());
        });

        $.each(filterValues, function(key, values) {
            if (key !== changedSelect) {
                var select = $('#select2_' + key);
                var options = select.find('option');
                options.each(function() {
                    if (values.has($(this).val()) || $(this).val() === '') {
                        $(this).prop('disabled', false);
                    } else {
                        $(this).prop('disabled', true);
                    }
                });
                select.trigger('change.select2');
            }
        });
    }
    function findMax(arr,sliderID) {

        let max = arr[0];
        if(arr.length > 0){
          for (let i = 1; i < arr.length; i++) {
            if (arr[i] > max) {
              max = arr[i];
            }
          }
        }else{
        
        }
        if(max === 0){
           max= $('#'+sliderID).data('max');
        }
        console.log(max);
        return parseInt(max);
}

    // Automatically check both checkboxes on page load
    $('#type_for_sale').prop('checked', true);
    $('#type_for_lease').prop('checked', true);

    // Initially filter listings based on selected options
    filterListings();

    // Attach keyup event to search box to filter listings on input
    $('#search-by-text-new').on('keyup', function() {
   
    var maxPrice = 0;
       
    // Iterate through each div element
    $('div[data-pricesf]').each(function() {
        var price = parseFloat($(this).attr('data-price')); // Get the value of data-pricesf attribute
        if (price > maxPrice) {
            maxPrice = price; // Update maxPrice if a higher value is found
        }
    });

    // Update UI with the maximum price
    $("#price-range").slider("option", "values", [0, maxPrice]);
    $("#price-range-max").val('$' + maxPrice.toLocaleString());

    console.log("hello " + maxPrice);
    filterListings(); 
});


    $( "#price-range,#price-range3,#price-range2" ).on( "slidestop", function( event, ui ) {
        filterListings();
    });
    
    
    $( "#price-range,#price-range3,#price-range2" ).on( "slide", function( event, ui ) {
        $("#search-by-text-new").attr('data-slided', $(this).prop('id'));
    });


    $('#type_for_sale, #type_for_lease').on('change', function() {
    var currentId = $(this).attr('id');
    var currentState = $(this).is(':checked');
    var stateString = currentState ? "checked" : "unchecked";
    var identifier = currentId + "_" + stateString;
    console.log(currentId + ": " + currentState);
    filterListings(identifier); // Pass identifier to filterListings()

    if (currentId === 'type_for_sale') {
        if (!currentState) {
            $('#type_for_lease').prop('disabled', true);
        } else {
            $('#type_for_lease').prop('disabled', false);
        }
    } else if (currentId === 'type_for_lease') {
        if (!currentState) {
            $('#type_for_sale').prop('disabled', true);
        } else {
            $('#type_for_sale').prop('disabled', false);
        }
    }
});

});


</script>
<!-- end auto option -->
<?php 


  wp_enqueue_script('traistate-google-map');
  wp_enqueue_script('traistate-google-map-api');

?>

<?php 
if (!empty($atts['state'])) {
?>
<script>
jQuery(document).ready(function($){

    var val = '<?php echo $atts['state']  ?>';
    $('#select2_states').val(val).trigger('change');
    $('#select2_states').prev('label').hide();
    $('#select2_states').next(".select2-container").hide();
});
</script>

<?php

}

?>

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

return ob_get_clean();
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

    if ($trimmed_range[0] == "0" &&  $trimmed_range[1] == get_pricesf_minmax('max', false)) {
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

// new shortcodes

add_shortcode('TSC-inventory-pub', 'tsc_inventory_pub');

function tsc_inventory_pub($_atts)
{
  // Start output buffering
  $defaults = array(
    'state' => ''
  );
  
  $atts = shortcode_atts($defaults , $_atts);
  
  $markers_data = [];
  ob_start();
?>
<style>
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
    


//Event listeners for checkboxes and search input
/* $('#type_for_sale, #type_for_lease').on('change', function() {
  $('#search-by-text').trigger('keyup');
  // combinedFilter();
  
}); */




      $('#filter-clear11').on('click', function() {
        //    alert('hello test');
        //$('#tristate-input').val("");
        $('#_gsheet_use, #tri_agents, #_gsheet_neighborhood,#search-by-text, #_gsheet_zip,#_buildout_city, #_gsheet_state, #_gsheet_vented,#price-range2,#price-range,#price-range3').val(null).trigger('change');
        // Reset Select2 select by ID ('tri_agents')
        //$('#tri_agents,#_gsheet_use').val(null).trigger('change');
        $('#_gsheet_listing_type input[type="checkbox"]').prop('checked', true);
        // $("#for_sale,#for_lease").hide();
        // Reset ui-slider-range for price-range2
        $('#price-range .ui-slider-range,#price-range2 .ui-slider-range, #price-range3 .ui-slider-range').css({
          'left': '0%',
          'width': '100%'
        });



        // Remove disabled attributes from options
        $('#_gsheet_use option, #_gsheet_neighborhood option,#_gsheet_zip option,#_buildout_city option,#_gsheet_state option').each(function() {
          $(this).prop('disabled', false);
          $(this).removeAttr('aria-disabled');
        });
        var rangeHiddenFields = $("#price-range-selected,#rent-range-selected,#size-range-selected");
        rangeHiddenFields.attr("data-clear", "1");
        
        
        // price
        $("#price-range" ).slider( "option", "max",  $("#price-range").data('max') );
        $("#price-range" ).slider( "option", "min",  $("#price-range").data('min') );
        $("#price-range").slider("values", [$("#price-range").data('min'), $("#price-range").data('max')]);
        
        // rent 
        $("#price-range3" ).slider( "option", "max",  $("#price-range3").data('max') );
        $("#price-range3" ).slider( "option", "min",  $("#price-range3").data('min') );
        $("#price-range3").slider("values", [$("#price-range3").data('min'), $("#price-range3").data('max')]);
        
        // size
        $("#price-range2" ).slider( "option", "max",  $("#price-range2").data('max') );
        $("#price-range2" ).slider( "option", "min",  $("#price-range2").data('min') );
        $("#price-range2").slider("values", [$("#price-range2").data('min'), $("#price-range2").data('max')]);
        
        // resetting inputs
        $('.range-inputs').each(function(){
            $(this).val($(this).attr('data-default'));
        });
       
        
        // Set the handle positions for the sliders
        // $('#price-range .ui-slider-handle, #price-range2 .ui-slider-handle, #price-range3 .ui-slider-handle').each(function() {
        //   $(this).css('left', '0%'); // Adjust this percentage as needed
        // });
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
   

      var isDropdownsInitialized = false;
      // Attach input event handler to relevant elements
      //,#_gsheet_listing_type
      $('#_gsheet_use,#_gsheet_zip,#_gsheet_state,#_buildout_city,#_gsheet_vented,#_gsheet_neighborhood').on('input', function() {
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
        var form = $('#tri-popup-form');
        var closebutton =$("#tcr-popup-close-button");
        $('input[name="get_properties_id"]').each(function() {
          
          var parent = $(this).parent('.propertylisting-content:visible');
          if(parent.length>0){
            var value = $(this).val();
            get_filter_poist_id.push(value);
          }
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
              
              $('#map_layer_show_message').fadeOut(600)
                .promise() 
                .done(function() {
                  form.get(0).reset();
                  $('#map-layer-content').fadeIn(300); 
                  closebutton.trigger('click');
                });
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

            function drt_get_dropdown_for_meta_2($meta_key)
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

            function drt_get_checkboxes_for_types_2($meta_key)
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

              <!-- <div class="tristate_cr_d-flex checkbox-wrapper" id="_gsheet_listing_type_new">
                <div>
                  <label for="for Sale">For Sale</label>
                  <input type="checkbox" name="listing_type_new" value="for Sale" id="type_for_sale_new">
                </div>
                <div>
                  <label for="for Lease">For Lease</label>
                  <input type="checkbox" name="listing_type_new" value="for Lease" id="type_for_lease_new">
                </div>
              </div> -->
            <?php
            }

            ?>


            <!-- Select2 Elements -->

            <div id="select-container">
    <!-- Dynamically created select elements will be placed here -->
</div>


            <div>

        
              
              <div id="dropdown_lisiting_type">
              
              <?php echo drt_get_checkboxes_for_types_2('_gsheet_listing_type'); ?>
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
                  <input type="hidden" name="price-range" data-live="0" data-clear="0" id="price-range-selected">
                </div>

              </div>
              <!-- For Rent -->
       
              <div>
                <div class="slider-box" id="for_lease">
                  <label for="priceRange">Price per SF:</label>
                  <input style="display:none" type="text" id="priceRange3" readonly>
                  <div class="range-min-max">
                    <input type="text" class="range-inputs" id="rent-range-min"data-default="<?php echo get_pricesf_minmax(); ?>" name="price_range_min" value="<?php echo get_pricesf_minmax(); ?>">
                    <input type="text" class="range-inputs" id="rent-range-max" data-default="<?php echo get_pricesf_minmax('max');?>" name="price_range_max" value="<?php echo get_pricesf_minmax('max'); ?>">
                  </div>
                  <div id="price-range3" class="slider" data-min="<?php echo get_pricesf_minmax('min',false) ?>" data-max="<?php echo get_pricesf_minmax('max',false); ?>"></div>
                  <input type="hidden" name="rent-range" data-clear="0" id="rent-range-selected">
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
                <input type="hidden" name="size-range" id="size-range-selected" data-live="0" data-clear="0">
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
                        <form  id="tri-popup-form" method="POST">
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

      <div id="get_all_listing_data">
      <div id="get_all_agents"></div>
      <div id="get_all_uses"></div>
      <div id="get_all_neighborhood"></div>
      <div id="get_all_zipcode"></div>
        <div id="get_all_cities"></div>
        <div id="get_all_state"></div>
        <div id="get_all_vented"></div>
       

      </div>
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
        
        if (!empty($atts['state'])) {
        
          $args['meta_query'][] = array(
            'relation' => 'OR',
            array(
                'key'     => '_buildout_state',
                'value'   => esc_attr($atts['state']),
                'compare' => '=',
            ),
            array(
                'key'     => '_gsheet_state', 
                'value'   => esc_attr($atts['state']),   
                'compare' => '=',              
                       
            ),
          );
        }
          $search_query = new WP_Query($args);
          $default_found_results = $search_query->found_posts;
        ?>
        <div id="menu-btn"><i class="fa fa-angle-left"></i></div>
        <div class="right-map">
          <!-- <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d407542.86304287874!2d-74.32724652492182!3d40.69942908913206!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!z4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2snp!4v1711702301417!5m2!1sen!2snp" allowfullscreen="allowFullScreen" width="100%" height="450px" style="position: relative; display: block;"></iframe> -->
          <div id="tristate-map" style="height:600px; width:100%;position:relative;display:block;"></div>
        </div>
        <div id="search_count_area">
        <!-- <div class="search-by-text MuiFormControl-root MuiTextField-root css-i44wyl">
            <input class="MuiInputBase-input MuiOutlinedInput-input css-1x5jdmq" aria-invalid="false" id="search-by-text" placeholder="search by keyword" type="text">
          </div> -->

          <div class="search-by-text-new MuiFormControl-root MuiTextField-root css-i44wyl">
            <input class="MuiInputBase-input MuiOutlinedInput-input css-1x5jdmq" aria-invalid="false" id="search-by-text-new" placeholder="search by keyword" type="text">
          </div>
          <!-- <div class="MuiFormControl-root MuiTextField-root css-i44wyl">
            <input aria-invalid="false" id="tristate-input" placeholder="search by keyword old" type="text" class="MuiInputBase-input MuiOutlinedInput-input css-1x5jdmq">
          </div> -->
          <div class="column-select-result-count">
      <div id="tristate-result-count" data-count="<?php echo __total(); ?>">
            <?php //echo 'Showing ' . $default_found_results . ' of ' .$default_found_results . ' Listing' ?>
          
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

     <!--    <div class="search-by-text">
            <input aria-invalid="false" id="search-by-text" placeholder="search by text" type="text">
          </div> -->

        <div class="post-output"></div>




        <div class="property-list-wrapper">
          <div class="MuiBox-root">
            <div class="MuiStack-root property-filter css-12xuzbq" id="propertylisting-content">

            <?php
              // Output the search results
              if ($search_query->have_posts()) {
                $loop = TRISTATECRLISTING_PLUGIN_DIR . 'templates/dr-loop.php';
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
<!-- dr new test for generate automatic options -->
<script>


$(document).ready(function() {


   // price range
	$("#price-range").slider({
		range: true,
		min: $("#price-range").data('min'),//get min val
		max: $("#price-range").data('max'),//get max val  
		values: [$("#price-range").data('min'), $("#price-range").data('max')],//postion slider val
		step: 1,
		slide: function (event, ui) {
		  $("#priceRange").val("$" + ui.values[0] + " - $" + ui.values[1]);
		  $("#price-range-min").val('$'+ui.values[0].toLocaleString());
		  $("#price-range-max").val('$'+ui.values[1].toLocaleString());
	   
		},
		change: function (event, ui) {
		  $("#price-range-selected").val(ui.values[0] + "-" + ui.values[1]);
		},
	
	});
	
	  $("#price-range2").slider({
      range: true,
      min: $("#price-range2").data('min'),//get min val
      max: $("#price-range2").data('max'),//get max val  
      values: [$("#price-range2").data('min'), $("#price-range2").data('max')],//postion slider val
      step:1,
      slide: function (event, ui) {
      
        $("#priceRange2").val(
          "" + ui.values[0].toLocaleString() + " SF to " + ui.values[1].toLocaleString() + " SF "
        );
        $("#size-range-min").val(ui.values[0].toLocaleString()+ ' SF');
        $("#size-range-max").val(ui.values[1].toLocaleString() + " SF");
      },
      change: function (event, ui) {
        
        $("#size-range-selected").val(ui.values[0] + "-" + ui.values[1]);
        
        
      },
  });
  
  
  $("#price-range3").slider({
    range: true,
    min: $("#price-range3").data('min'),//get min val
    max: $("#price-range3").data('max'),//get max val  
    values: [$("#price-range3").data('min'), $("#price-range3").data('max')],
    step:1,
    slide: function (event, ui) {
      $("#priceRange3").val("$" + ui.values[0].toLocaleString() + " - $" + ui.values[1].toLocaleString());
      $("#rent-range-min").val("$" +ui.values[0].toLocaleString());
      $("#rent-range-max").val("$" +ui.values[1].toLocaleString());
    },
    change: function (event, ui) {
      $("#rent-range-selected").val(ui.values[0] + "-" + ui.values[1]);  
     
    },
  });

    // Extract unique values from the HTML for select2 options
    var agents = new Set();
    var uses = new Set();
    var neighborhoods = new Set();
    var zipcodes = new Set();
    var cities = new Set();
    var states = new Set();
    var vented = new Set();

    $(".propertylisting-content").each(function() {
        agents.add($(this).find("#tri_listing_agent").text().trim());
        uses.add($(this).find(".tri_use").text().trim());
        neighborhoods.add($(this).find("#tri_neighborhood").text().trim());
        zipcodes.add($(this).find("#tri_zip_code").text().trim());
        cities.add($(this).find("#tri_city").text().trim());
        states.add($(this).find("#tri_state").text().trim());
        vented.add($(this).find("#tri_vented").text().trim());
    });

    // Function to create select2 options
    function createSelect2Options(data) {
        var options = Array.from(data).sort().map(function(value) {
            return { id: value, text: value };
        });
        return options;
    }

    // Generate select2 options
    var selectOptions = {
        agents: createSelect2Options(agents),
        uses: createSelect2Options(uses),
        neighborhoods: createSelect2Options(neighborhoods),
        zipcodes: createSelect2Options(zipcodes),
        cities: createSelect2Options(cities),
        states: createSelect2Options(states),
        vented: createSelect2Options(vented)
    };



    $.each(selectOptions, function(key, options) {
        // Add label element
        $('<label>', {
            for: 'select2_' + key,
            text: key.charAt(0).toUpperCase() + key.slice(1) + ': '
        }).appendTo('#select-container');
        
        // Add select2 element
        $('<select>', {
            id: 'select2_' + key,
            name: 'select2_' + key + '[]',
            multiple: true
        }).appendTo('#select-container').select2({
            data: options,
            placeholder: ''
        }).on('change', function(e) {
            if (e.type === 'select2:select') {
                $(this).select2("close");
            }
            filterListings(key);
        }).on('select2:unselecting', function(e) {
            $(this).data('state', 'unselecting');
        }).on('select2:opening', function(e) {
            if ($(this).data('state') === 'unselecting') {
                $(this).removeData('state');
                e.preventDefault();
            }
        });
    });

    // Function to filter listings based on selected options and keyword
    function filterListings() {
        var selectedAgents = $('#select2_agents').val() || [];
        var selectedUses = $('#select2_uses').val() || [];
        var selectedNeighborhoods = $('#select2_neighborhoods').val() || [];
        var selectedZipcodes = $('#select2_zipcodes').val() || [];
        var selectedCities = $('#select2_cities').val() || [];
        var selectedStates = $('#select2_states').val() || [];
        var selectedVented = $('#select2_vented').val() || [];
        var keyword = $('#search-by-text-new').val().toLowerCase();

        var priceRange = $("#price-range" ).slider( "values" ).map(Number);
        var priceRangeSf=$("#price-range3").slider("values").map(Number);
        var sizeRangeSf = $("#price-range2").slider("values").map(Number);
        var displayedListings = 0;
        var priceArray =[0] , pricesfArray=[0] , minsizeArray=[0] , maxsizeArray= [0] ;
 

        var showForSale = $('#type_for_sale').is(':checked');
        var showForLease = $('#type_for_lease').is(':checked');

        var displayedListings = 0;

        $(".propertylisting-content").each(function() {
            var $listing = $(this);
            var showListing = true,
            price = parseFloat($(this).data('price')),
            priceSf = parseFloat($(this).data('pricesf')),
            sizeMax = parseFloat($(this).data('maxsize')),
            isBetweenMaxMinPrice = (price >= priceRange[0]) && (price <= priceRange[1]),
            isBetweenMaxMinPriceSf = (priceSf >= priceRangeSf[0]) && (priceSf <= priceRangeSf[1]),
            isBetweenMaxMinSize = (sizeMax >= sizeRangeSf[0]) && (sizeMax <= sizeRangeSf[1]);

            if (selectedAgents.length > 0 && !selectedAgents.includes($listing.find("#tri_listing_agent").text().trim())) {
                showListing = false;
            }

            if (selectedUses.length > 0 && !selectedUses.includes($listing.find(".tri_use").text().trim())) {
                showListing = false;
            }

            if (selectedNeighborhoods.length > 0 && !selectedNeighborhoods.includes($listing.find("#tri_neighborhood").text().trim())) {
                showListing = false;
            }

            if (selectedZipcodes.length > 0 && !selectedZipcodes.includes($listing.find("#tri_zip_code").text().trim())) {
                showListing = false;
            }

            if (selectedCities.length > 0 && !selectedCities.includes($listing.find("#tri_city").text().trim())) {
                showListing = false;
            }

            if (selectedStates.length > 0 && !selectedStates.includes($listing.find("#tri_state").text().trim())) {
                showListing = false;
            }

            if (selectedVented.length > 0 && !selectedVented.includes($listing.find("#tri_vented").text().trim())) {
                showListing = false;
            }

            if (keyword && !$listing.text().toLowerCase().includes(keyword)) {
                showListing = false;
            }

            if (!isBetweenMaxMinPrice) {
                showListing = false;
            }
            if(!isBetweenMaxMinPriceSf){
                showListing = false;
            }
            
            if(!isBetweenMaxMinSize){
                showListing =false;
            }

            var isForLease = $listing.find(".tri_for_lease").length > 0;
            var isForSale = $listing.find(".tri_for_sale").length > 0;

            if ((showForSale && isForSale) || (showForLease && isForLease) ){
                // Listing matches one of the selected types
            } else if (showForSale || showForLease) {
                // At least one of the checkboxes is checked but the listing doesn't match any
                showListing = false;
            }

            if (showListing) {
                $listing.show();

                priceArray.push(price);
                pricesfArray.push(priceSf);
                maxsizeArray.push(sizeMax);

                displayedListings++;
            } else {
                $listing.hide();
            }
        });

        // Update displayed listings count
        var totalListings = $(".propertylisting-content").length;
        $('#tristate-result-count').text('Showing ' + displayedListings + ' of ' + totalListings + ' Listings');

        $("#save_map_layer").text("SAVE " + displayedListings + " RESULTS TO A NEW MAP LAYER");
        // change markers on map
      
        
        var maxPrice = findMax(priceArray,'price-range')
        , maxsf = findMax(pricesfArray,'price-range3') 
        , maxSize=findMax(maxsizeArray,'price-range2');
        
        var dataSlided = $('#search-by-text-new').data('slided');
        
        //price
    /*     if(dataSlided !=='price-range'){
          $("#price-range" ).slider( "option", "values", [ 0, maxPrice] );
          $("#price-range-max").val('$' + maxPrice.toLocaleString());
        }
        //sf
        if(dataSlided !=='price-range3'){
          $('#price-range3').slider( "option", "values", [ 0, maxsf] );
          $("#rent-range-max").val('$'+maxsf.toLocaleString());
        }
        if(dataSlided !=='price-range2'){
          $('#price-range2').slider( "option", "values", [ 0, maxSize] );
          $("#size-range-max").val(maxSize.toLocaleString()+' SF');
        } */
        get_markerData(false);
        //updateSelect2Options(changedSelect);

        // Display selected options in console
        // console.log('Selected Agents:', selectedAgents);
        // console.log('Selected Uses:', selectedUses);
        // console.log('Selected Neighborhoods:', selectedNeighborhoods);
        // console.log('Selected Zipcodes:', selectedZipcodes);
        // console.log('Selected Cities:', selectedCities);
        // console.log('Selected States:', selectedStates);
        // console.log('Selected Vented:', selectedVented);
    }
    function findMax(arr,sliderID) {

        let max = arr[0];
        if(arr.length > 0){
          for (let i = 1; i < arr.length; i++) {
            if (arr[i] > max) {
              max = arr[i];
            }
          }
        }else{
        
        }
        if(max === 0){
           max= $('#'+sliderID).data('max');
        }
        console.log(max);
        return parseInt(max);
}

    // Automatically check both checkboxes on page load
    $('#type_for_sale').prop('checked', true);
    $('#type_for_lease').prop('checked', true);

    // Initially filter listings based on selected options
    filterListings();

    // Attach keyup event to search box to filter listings on input
    $('#search-by-text-new').on('keyup', function() {
        filterListings();
    });

    $( "#price-range,#price-range3,#price-range2" ).on( "slidestop", function( event, ui ) {
        filterListings();
    });
    
    
    $( "#price-range,#price-range3,#price-range2" ).on( "slide", function( event, ui ) {
        $("#search-by-text-new").attr('data-slided', $(this).prop('id'));
    });

    // Attach change event to the checkboxes to filter listings
 /*    $('#type_for_sale, #type_for_lease').on('change', function() {
        filterListings();
    }); */
    $('#type_for_sale, #type_for_lease').on('change', function() {
    var currentId = $(this).attr('id');
    var currentState = $(this).is(':checked');
    var stateString = currentState ? "checked" : "unchecked";
    var identifier = currentId + "_" + stateString;
    console.log(currentId + ": " + currentState);
    filterListings(identifier); // Pass identifier to filterListings()

    if (currentId === 'type_for_sale') {
        if (!currentState) {
            $('#type_for_lease').prop('disabled', true);
        } else {
            $('#type_for_lease').prop('disabled', false);
        }
    } else if (currentId === 'type_for_lease') {
        if (!currentState) {
            $('#type_for_sale').prop('disabled', true);
        } else {
            $('#type_for_sale').prop('disabled', false);
        }
    }
});
    
});


</script>
<!-- end auto option -->
<?php 


  wp_enqueue_script('traistate-google-map');
  wp_enqueue_script('traistate-google-map-api');

if (!empty($atts['state'])) {
?>
<script>
  jQuery(document).ready(function($){
  
      var val = '<?php echo $atts['state']  ?>';
      $('#select2_states').val(val).trigger('change');
      $('#select2_states').prev('label').hide();
      $('#select2_states').next(".select2-container").hide();
  });
</script>

<?php

}

?>

  <!-- text data 1 -->
  <textarea style="display: none;" id="marker_data_all"><?php echo json_encode($markers_data) ?></textarea>
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

return ob_get_clean();
}



