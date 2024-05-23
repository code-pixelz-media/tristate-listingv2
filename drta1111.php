<?php


function __total()
{
  global $wpdb;
  $post_type = 'properties';
  $query = "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_status = 'publish'";
  $count = $wpdb->get_var($wpdb->prepare($query, $post_type));
  return $count;
}



add_shortcode('drt', 'drt_shortcode');

function drt_shortcode($atts)
{
  ob_start(); // Start output buffering
  $markers_data = [];
?>

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
    
    //   jQuery('#tri_agents').on('select2:open', function(e) {
    //   alert("as");
    //     // jQuery.ajax({
    //     //     url: '<?php //echo admin_url('admin-ajax.php'); ?>',
    //     //     type: 'POST',
    //     //     success: function(data) {
    //     //         console.log(data);
    //     //     },
    //     //     error: function(xhr, status, error) {
    //     //         console.error(error);
    //     //     }
    //     // });
    // });
      
      // $('#tri_agents').on('click', function(){
      //     alert('Hellow');
      // });
      
      // $('#tri_agents').click(function(e){
      //     alert("hellow2");
      // });
      
      // $('#tri_agents').on('input', function(){
          
      //     alert('hellow3');
      // });
      // $('#tri_agents').hover(function(){
      //   alert('hellow4'); 
      // });
      // $('.select2-search__field').on('focus', function(){
          
      //     alert('focus');
      // });
      
        
        // var agentsField = $('#tri_agents'),
        //     usesField = $("#_gsheet_use"),
        //     neighborhoodfield = $("#_gsheet_neighborhood"),
        //     gsheetZip = $("#_gsheet_zip"),
        //     cities = $("#_buildout_city"),
        //     states = $('#_gsheet_state'),
        //     vented = $('#_gsheet_vented');
            
        // var allFields = [agentsField ,usesField ,neighborhoodfield ,gsheetZip ,cities,states,vented];

        // function updateDropdowns(excludeId){
        //   $.each(allFields, function(i, e){
        //   var fieldId = $(this).prop('id');
        //       if(fieldId === excludeId) return true;
        //       $('#'+fieldId).on('onmouseenter', function (e) {
        //           console.log($(this).val());
        //       });
             
        //   });
            
        // }
        
        // $('#_gsheet_neighborhood').on('keyup', function (e) {
        //    console.log($(this).val());
        // });    
        

            
        // agentsField.on('input', function(){
            
        //   updateDropdowns($(this).prop('id'));
        //   // alert(agentsField.val());
            
        // });

/*       document.getElementById("filter-clear11").addEventListener("click", function(event) {
    // Prevent the default behavior of the click event
    event.preventDefault();

    // Remove all selected values from the Select2 dropdowns
    $('#_gsheet_use, #tri_agents, #_gsheet_neighborhood, #_gsheet_zip,#_buildout_city, #_gsheet_state, #_gsheet_vented, #_gsheet_listing_type, #price-range2').val(null).trigger('change');

    // Disable the Select2 dropdowns
    $('.select2').prop('disabled', true);

    // Reset the price range slider
    var priceRangeSlider = $("#price-range");
    var minPrice = priceRangeSlider.slider("option", "min");
    var maxPrice = priceRangeSlider.slider("option", "max");
    priceRangeSlider.slider("values", [minPrice, maxPrice]);

    // Perform any additional actions if needed
    var data = {
        action: 'live_search',
    };
    if (JSON.stringify(prevSearchParams) === JSON.stringify(data) && prevSearchResult) {
        $('#propertylisting-content').html(prevSearchResult); // Display previous result
    } else {
        performAjaxRequest(data); // Make AJAX request
    }
}); */

/* $('#filter-clear11').on('click', function() {
    alert('hello test');
    // Set the value of '#tri_agents' and '#_gsheet_use' to null and trigger the 'change' event
    $('#tri_agents, #_gsheet_use').val(null).trigger('change');
    
    // Remove 'select2-results__option--disabled' class and 'aria-disabled' attribute from options
    $('.select2-results__option--disabled').removeClass('select2-results__option--disabled').removeAttr('aria-disabled');
}); */


$('#filter-clear11').on('click', function() {
//    alert('hello test');

    $('#_gsheet_use, #tri_agents, #_gsheet_neighborhood, #_gsheet_zip,#_buildout_city, #_gsheet_state, #_gsheet_vented,#price-range2,#price-range,#price-range3').val(null).trigger('change');
    // Reset Select2 select by ID ('tri_agents')
    //$('#tri_agents,#_gsheet_use').val(null).trigger('change');
    $('#_gsheet_listing_type input[type="checkbox"]').prop('checked', false);
  $("#for_sale,#for_lease").hide();
      // Reset ui-slider-range for price-range2
      $('#price-range2 .ui-slider-range').css({
        'left': '0%',
        'width': '100%'
    });

  $("#tristate-input").val("");
    // Remove disabled attributes from options
    $('#_gsheet_use option, #_gsheet_neighborhood option,#_gsheet_zip option,#_buildout_city option,#_gsheet_state option').each(function() {
        $(this).prop('disabled', false);
        $(this).removeAttr('aria-disabled');
    });
     // Perform any additional actions if needed
     var data = {
        action: 'live_search',
        clear: 'yes'
    };
    if (JSON.stringify(prevSearchParams) === JSON.stringify(data) && prevSearchResult) {
        $('#propertylisting-content').html(prevSearchResult); // Display previous result
    } else {
        performAjaxRequest(data); // Make AJAX request
    }
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
      
        var agentsField = $('#tri_agents'),
            usesField = $("#_gsheet_use"),
            neighborhoodfield = $("#_gsheet_neighborhood"),
            gsheetZip = $("#_gsheet_zip"),
            cities = $("#_buildout_city"),
            states = $('#_gsheet_state'),
            vented = $('#_gsheet_vented');
            
        var allFields = [agentsField ,usesField ,neighborhoodfield ,gsheetZip ,cities,states,vented];

        function dropDownToUpdate(excludeId) {
            var fieldsToChange = [];
            $.each(allFields, function(i, e) {
                var fieldId = $(e).prop('id');
                if (fieldId === excludeId) return true;
                fieldsToChange.push(fieldId);
            });
            
            return fieldsToChange;
        }
       
        // $.each(allFields, function(i,e){
          
        //   $(this).on('select2:open', function(e){
        //     var selectedValues = {
        //       action: "tristate_select2_dropdown_update",
        //       agents: $('#tri_agents').val(),
        //       uses : $('#_gsheet_use').val(),
        //       neighbourhoods:$("#_gsheet_neighborhood").val(),
        //       zips : $('#_gsheet_zip').val(),
        //       cities:$("#_buildout_city").val(),
        //       states:$("#_gsheet_state").val(),
        //       venteds:vented.val()
             
        //     };
            
             
        //     $.ajax({
        //         url: '<?php //echo admin_url('admin-ajax.php'); ?>',
        //         type: 'POST',
        //         data:selectedValues,
        //         success: function(data) {
        //             console.log(data);
        //         },
        //         error: function(xhr, status, error) {
        //             console.error(error);
        //         }
        //     });
              

        //   });
        
        // });

        
        $("#tri_agents,#_gsheet_use,#_gsheet_neighborhood,#_gsheet_zip,#_buildout_city,#_gsheet_state").on('select2:select', function(e) { 
          
            var selectedValues = {
                action: "tristate_select2_dropdown_update",
                agents: $('#tri_agents').val() !== "" ? $('#tri_agents').val() : '' ,
                uses : $('#_gsheet_use').val()!== "" ? $('#_gsheet_use').val() : '',
                neighbourhoods:$("#_gsheet_neighborhood").val()!== "" ? $("#_gsheet_neighborhood").val() : '',
                zips : $('#_gsheet_zip').val()!== "" ? $('#_gsheet_zip').val() : '',
                cities:$("#_buildout_city").val()!== "" ? $("#_buildout_city").val() : '',
                states:$("#_gsheet_state").val()!== "" ? $("#_gsheet_state").val() : '',
                dropdownToUpdate: $(this).prop('id'),
     
              };
              var needUpdate =$("#"+$(this).prop('id'));
              var selectedValue = e.params.data.id;
              $.ajax({
                  url: '<?php echo admin_url('admin-ajax.php'); ?>',
                  type: 'POST',
                  data: selectedValues,
                  success: function(datas) {
                    needUpdate.html(datas);
                    console.log(datas);
                 
                  },
                  error: function(xhr, status, error) {
                      console.error(error);
                  }
              });        
                   
        
        });
        
      // Agents Change
      $('#tri_agents555').on('input',function() {
        var brokerIds = $(this).val(); // Get the selected broker IDs as an array
        var neighborhoodIds = $("#_gsheet_neighborhood").val(); 
       
        // Perform AJAX requests for both actions simultaneously
        $.when(
          // for listings html
          $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
              action: 'live_search',
              broker_ids: brokerIds,
              _buildout_city: $('#_buildout_city').val(),
              _gsheet_use: $('#_gsheet_use').val(),
              // selected_type: $('#_gsheet_listing_type').val(),
              selected_type: getSelectedListingTypes(),
              _gsheet_neighborhood: $('#_gsheet_neighborhood').val(),
              _gsheet_zip: $('#_gsheet_zip').val(),
              _gsheet_state: $('#_gsheet_state').val(),
              _gsheet_vented: $('#_gsheet_vented').val(),
              property_price_range: $('#price-range-selected').val(),
              property_size_range: $('#size-range-selected').val(),
              property_rent_range: $('#rent-range-selected').val(),
            }
          }),
          // ajax to get uses
          $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
              action: 'get_uses_dropdown',
              broker_ids: brokerIds,
              _buildout_city: $('#_buildout_city').val(),
              _gsheet_use: $('#_gsheet_use').val(),
              //selected_type: $('#_gsheet_listing_type').val(),
              selected_type: getSelectedListingTypes(),
              _gsheet_neighborhood: $('#_gsheet_neighborhood').val(),
              _gsheet_zip: $('#_gsheet_zip').val(),
              _gsheet_state: $('#_gsheet_state').val(),
              _gsheet_vented: $('#_gsheet_vented').val(),

            }
          }),
          // ajax to get neighbourhoods
          $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
              action: 'get_neighbourhoods_dropdown',
              broker_ids: brokerIds,
              _buildout_city: $('#_buildout_city').val(),
              _gsheet_use: $('#_gsheet_use').val(),
              selected_type: getSelectedListingTypes(),
              _gsheet_neighborhood: $('#_gsheet_neighborhood').val(),
              _gsheet_zip: $('#_gsheet_zip').val(),
              _gsheet_state: $('#_gsheet_state').val(),
              _gsheet_vented: $('#_gsheet_vented').val(),

            }
          }),
          //ajax to get zipcodes 
                  
          $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
              action: 'get_zip_dropdown',
              broker_ids: brokerIds,
              _buildout_city: $('#_buildout_city').val(),
              _gsheet_use: $('#_gsheet_use').val(),
              selected_type: getSelectedListingTypes(),
              _gsheet_neighborhood: $('#_gsheet_neighborhood').val(),
              _gsheet_zip: $('#_gsheet_zip').val(),
              _gsheet_state: $('#_gsheet_state').val(),
              _gsheet_vented: $('#_gsheet_vented').val(),

            }
          }),
          
          // ajax to get cities
          $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
              action: 'get_agent_cities',
              status:'agent_to_city',
              broker_ids: brokerIds,
              _buildout_city: $('#_buildout_city').val(),
              _gsheet_use: $('#_gsheet_use').val(),
              // selected_type: $('#_gsheet_listing_type').val(),
              selected_type: getSelectedListingTypes(),
              _gsheet_neighborhood: $('#_gsheet_neighborhood').val(),
              _gsheet_zip: $('#_gsheet_zip').val(),
              _gsheet_state: $('#_gsheet_state').val(),
              _gsheet_vented: $('#_gsheet_vented').val()
            }
          }),
          
          // ajax to get state
          $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
              action: 'get_agent_states',
              broker_ids: brokerIds,
              _buildout_city: $('#_buildout_city').val(),
              _gsheet_use: $('#_gsheet_use').val(),
              // selected_type: $('#_gsheet_listing_type').val(),
              selected_type: getSelectedListingTypes(),
              _gsheet_neighborhood: $('#_gsheet_neighborhood').val(),
              _gsheet_zip: $('#_gsheet_zip').val(),
              _gsheet_state: $('#_gsheet_state').val(),
              _gsheet_vented: $('#_gsheet_vented').val()
            } //here
          }),
          
        //1live_search ,2uses ,3neighs,4zip ,5city , 6states
        ).done(function(response1, response2, response3, response4 , response5,response6 ) {
          $('#propertylisting-content').html(response1[0]);

          $('#_gsheet_use').html(response2[0]);
          $("#_gsheet_neighborhood").html(response3[0]);
          $("#_gsheet_zip").html(response4[0]);
          $('#_buildout_city').html(response5[0]);
          $('#_gsheet_state').html(response6[0]);
        }).fail(function(xhr, status, error) {
          console.error(error); // Log any errors
        });
      });

      // City change
      $('#_buildout_city555').on('input',function() {
        var _buildout_city = $(this).val(); // Get the selected city

        // AJAX request for live search action
        var liveSearchRequest = $.ajax({
          url: '<?php echo admin_url('admin-ajax.php'); ?>',
          type: 'POST',
          data: {
            action: 'live_search',
            _buildout_city: _buildout_city,
            broker_ids: $('#tri_agents').val(),
            _gsheet_use: $('#_gsheet_use').val(),
            selected_type: $('#_gsheet_listing_type').val(),
            _gsheet_neighborhood: $('#_gsheet_neighborhood').val(),
            _gsheet_zip: $('#_gsheet_zip').val(),
            _gsheet_state: $('#_gsheet_state').val(),
            _gsheet_vented: $('#_gsheet_vented').val(),
            property_price_range: $('#price-range-selected').val(),
            property_size_range: $('#size-range-selected').val(),
            property_rent_range: $('#rent-range-selected').val(),
          }
        });

        // AJAX request for get uses dropdown action
        var usesDropdownAgents = $.ajax({
          url: '<?php echo admin_url('admin-ajax.php'); ?>',
          type: 'POST',
          data: {
            action: 'get_agents_dropdown',
            _buildout_city: _buildout_city,
            broker_ids: $('#tri_agents').val(),
            _gsheet_use: $('#_gsheet_use').val(),
            // selected_type: $('#_gsheet_listing_type').val(),
            selected_type: getSelectedListingTypes(),
            _gsheet_neighborhood: $('#_gsheet_neighborhood').val(),
            _gsheet_zip: $('#_gsheet_zip').val(),
            _gsheet_state: $('#_gsheet_state').val(),
            _gsheet_vented: $('#_gsheet_vented').val()
          }
        });
        
       

        $.when(liveSearchRequest, usesDropdownAgents).done(function(response1, response5) {
          // Update the dropdown containers with responses

          $('#propertylisting-content').html(response1[0]);

          $('#tri_agents').html(response5[0]);
        }).fail(function(xhr, status, error) {
          console.error(error); // Log any errors
        });
      });

      // state change
      $('#_gsheet_state555').on('input',function() {
        var _gsheet_state = $(this).val(); // Get the selected city

        // AJAX request for live search action
        var liveSearchRequest = $.ajax({
          url: '<?php echo admin_url('admin-ajax.php'); ?>',
          type: 'POST',
          data: {
            action: 'live_search',
            _gsheet_state: _gsheet_state,
            broker_ids: $('#tri_agents').val(),
            _gsheet_use: $('#_gsheet_use').val(),
            //   selected_type: $('#_gsheet_listing_type').val(),
            selected_type: getSelectedListingTypes(),
            _gsheet_neighborhood: $('#_gsheet_neighborhood').val(),
            _gsheet_zip: $('#_gsheet_zip').val(),
            _buildout_city: $('#_buildout_city').val(),
            _gsheet_vented: $('#_gsheet_vented').val(),
            property_price_range: $('#price-range-selected').val(),
            property_size_range: $('#size-range-selected').val(),
            property_rent_range: $('#rent-range-selected').val(),
          }
        });
        // AJAX request for get uses dropdown action
        var usesDropdownAgents = $.ajax({
          url: '<?php echo admin_url('admin-ajax.php'); ?>',
          type: 'POST',
          data: {
            action: 'get_state_dropdown',
            _gsheet_state: _gsheet_state,
            broker_ids: $('#tri_agents').val(),
            _gsheet_use: $('#_gsheet_use').val(),
            //   selected_type: $('#_gsheet_listing_type').val(),
            selected_type: getSelectedListingTypes(),
            _gsheet_neighborhood: $('#_gsheet_neighborhood').val(),
            _gsheet_zip: $('#_gsheet_zip').val(),
            _buildout_city: $('#_buildout_city').val(),
            _gsheet_vented: $('#_gsheet_vented').val()
          }
        });
          // AJAX request for get uses dropdown action
          var stateCitiesDropdown = $.ajax({
          url: '<?php echo admin_url('admin-ajax.php'); ?>',
          type: 'POST',
          data: {
            action: 'get_agent_cities',
            _gsheet_state: _gsheet_state,
            status:'state_to_city',
            broker_ids: $('#tri_agents').val(),
            _gsheet_zip: $('#_gsheet_zip').val(),
            _buildout_city: $('#_buildout_city').val(),
          }
        });

        var stateZipCodeDropdown = $.ajax({
          url: '<?php echo admin_url('admin-ajax.php'); ?>',
          type: 'POST',
          data: {
            action: 'get_zipcodes',
            _gsheet_state: _gsheet_state,
            status:'state_to_zip',  
            broker_ids: $('#tri_agents').val(),
            _gsheet_zip: $('#_gsheet_zip').val(),
            _buildout_city: $('#_buildout_city').val(),
          }
        });
        $.when(liveSearchRequest, usesDropdownAgents,stateCitiesDropdown,stateZipCodeDropdown).done(function(response1, response5,cityResponse,zipCodeResponse) {
          // Update the dropdown containers with responses
          $('#propertylisting-content').html(response1[0]);
          $('#_buildout_city').html(cityResponse[0]);
          $('#_gsheet_zip').html(zipCodeResponse[0]);
          //#_gsheet_zip
        }).fail(function(xhr, status, error) {
          console.error(error); // Log any errors
        });
      });
    
      // listing type, neighbourhood  , zip_code ,vented 
      $('#_gsheet_listing_type55,#_gsheet_neighborhood55, #_gsheet_zip55, #_gsheet_vented55').on('input',function() {
        
        var data = {
          action: 'live_search',
          broker_ids: $('#tri_agents').val(),
          _gsheet_use: $('#_gsheet_use').val(),
          //  selected_type: getSelectedListingTypes(),
          selected_type: getSelectedListingTypes(),
          _gsheet_neighborhood: $('#_gsheet_neighborhood').val(),
          _gsheet_zip: $('#_gsheet_zip').val(),
          _buildout_city: $('#_buildout_city').val(),
          _gsheet_state: $('#_gsheet_state').val(),
          _gsheet_vented: $('#_gsheet_vented').val(),
          _gsheet_vented: $('#_gsheet_vented').val(),
          property_price_range: $('#price-range-selected').val(),
          property_size_range: $('#size-range-selected').val(),
          property_rent_range: $('#rent-range-selected').val(),
        };
        if (JSON.stringify(prevSearchParams) === JSON.stringify(data) && prevSearchResult) {
          $('#propertylisting-content').html(prevSearchResult); // Display previous result
        } else {
          performAjaxRequest(data); // Make AJAX request
        }
      });
      
      // use
      $('#_gsheet_use555').on('input',function() {
        var selectedUses = $(this).val(); // Get the selected uses
        //alert(selectedUses);
        // Example of triggering an AJAX call
        $.ajax({
          url: '<?php echo admin_url('admin-ajax.php'); ?>',
          type: 'POST',
          data: {
            action: 'live_search', // The action hook for handling the backend processing
            selected_uses: selectedUses // Passing the selected uses to the backend
          },

          success: function(response) {

            // Handle the response here (e.g., update the HTML content)
          },
          error: function(error) {
            console.error("Error fetching properties:", error);
          }
        });
      });

      /*  ---------------------Save map layer------------- */

      jQuery("#submit_map_layer").on("click", function() {
        var get_map_title = $('#map_post_title').val();
        var get_map_layer_title = $('#map_post_title').val();

        // Array to store the values
        var get_filter_poist_id = [];

        // Select all input elements with the name "get_properties_id" and loop through them
        $('input[name="get_properties_id"]').each(function() {
          // Get the value of each input element and push it into the get_filter_poist_id
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
              action: 'tristate_save_results_as_layer', // The action hook for handling the backend processing
              get_map_title: get_map_title,
              layer_name: get_map_layer_title,

              listing_ids: final_listing_ids, // Passing the selected uses to the backend
            },

            success: function(response) {

              // Handle the response here (e.g., update the HTML content)
              //console.log(response.data.message);
              $('#map_layer_show_message').text(response.data.message);
              $('#map-layer-content').css('display', 'none');
            },
            error: function(error) {
              console.error("Error fetching properties:", error);
            }
          });



          // Output the values array to console
          //alert(get_filter_poist_id);
        }
      });


    });
  </script>


  <!-- -------------------------- -->
  <?php
  /* $cached_content = get_transient('property_listing_content');

if (false === $cached_content) {
    ob_start();  */
  ?>

  <div class="filter-wrapper" id="filter-wrapper">
    <div class="MuiBox-root">
      <div class="left-content">
        <div class="Filterform">
          <div class="MuiBox-root">
            <div class="MuiFormControl-root MuiTextField-root css-i44wyl">
              <input aria-invalid="false" id="tristate-input" placeholder="Filter by text ..." type="text" class="MuiInputBase-input MuiOutlinedInput-input css-1x5jdmq">
            </div>


            <?php

            function drt_get_dropdown_for_meta($meta_key)
            {
              global $wpdb;

              // Replace 'wp_' with your WordPress table prefix if it's different
              $table_name = $wpdb->prefix . 'postmeta';

              // Custom SQL query to fetch unique trimmed values based on meta key
              // $query = $wpdb->prepare("SELECT DISTINCT TRIM(meta_value) AS meta_value FROM $table_name WHERE meta_key = %s", $meta_key);

              // Custom SQL query to fetch unique trimmed values based on meta key
              if ($meta_key === '_gsheet_state') {
                $query = $wpdb->prepare("SELECT DISTINCT TRIM(meta_value) AS meta_value FROM $table_name WHERE meta_key = %s OR meta_key = %s", $meta_key, '_buildout_state');
              } else {
                $query = $wpdb->prepare("SELECT DISTINCT TRIM(meta_value) AS meta_value FROM $table_name WHERE meta_key = %s", $meta_key);
              }

              // Fetching results from the database
              $results = $wpdb->get_results($query);
              if ($meta_key != '_gsheet_use') {
                // Generating the select element
                echo '<select class="js-example-basic-multiple" name="' . $meta_key . '[]" multiple="multiple" id="' . $meta_key . '">';
              }
              // Processing and displaying the results
              if ($results) {
                foreach ($results as $result) {
                  $uses = $result->meta_value;
                  echo '<option value="' . $uses . '" data-uses="' . $uses . '">' . $uses . '</option>';
                }
              } else {
                echo '<option>No uses found</option>';
              }
              if ($meta_key != '_gsheet_use') {
                echo '</select>';
              }
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
            <div>
              <label>Agents</label>
              <?php
              // Query to fetch brokers

              // Query to fetch brokers
              $args = array(
                'post_type' => 'brokers',
                'posts_per_page' => -1, // Get all brokers
              );

              $brokers = new WP_Query($args);

              if ($brokers->have_posts()) {
                echo '<select id="tri_agents" class="js-example-basic-multiple" name="agents[]" multiple="multiple">';
                while ($brokers->have_posts()) {
                  $brokers->the_post();
                  $broker_id = get_the_ID();
                  $broker_name = get_the_title();
                  echo '<option value="' . $broker_name . '" data-uid="' . $broker_id . '" data-agent_name="' . $broker_name . '"  >' . $broker_name . '</option>';
                }
                echo '</select>';
                wp_reset_postdata();
              } else {
                // No brokers found
                echo '<p>No brokers found.</p>';
              }
              ?>


            </div>

            <div class="drt-uses">
              <label>Uses</label>
              <select id="_gsheet_use" class="js-example-basic-multiple" name="_gsheet_use[]" multiple="multiple">
                <?php drt_get_dropdown_for_meta('_gsheet_use'); ?>
              </select>
            </div>



            <div>
              <label>Neighbourhoods</label>
              <?php drt_get_dropdown_for_meta('_gsheet_neighborhood'); ?>
            </div>

            <div>
              <label>Zip Codes</label>
              <?php drt_get_dropdown_for_meta('_gsheet_zip'); ?>
            </div>

            <div>
              <label>Cities</label>
              <?php drt_get_dropdown_for_meta('_buildout_city'); ?>
            </div>

            <div>
              <label>State</label>
              <?php drt_get_dropdown_for_meta('_gsheet_state'); ?>
            </div>

            <div>
              <label>Vented</label>
              <?php drt_get_dropdown_for_meta('_gsheet_vented'); ?>
            </div>

            <div>
              <label>Types</label>
              <div>

                <?php
                //drt_get_dropdown_for_meta('_gsheet_listing_type');
                drt_get_checkboxes_for_types('_gsheet_listing_type');

                ?>
              </div>

            </div>

            <div id="sale_lease">
              <div>
                <div class="slider-box" id="for_sale">
                  <label for="priceRange">Price :</label>
                  <input type="text" id="priceRange" readonly>
                  <div id="price-range" class="slider"></div>
                  <input type="hidden" name="price-range" id="price-range-selected" onchange="rangeChanged()">
                </div>
              </div>
              <div>
                <div class="slider-box" id="for_lease">
                  <label for="priceRange">Rent:</label>
                  <input type="text" id="priceRange3" readonly>
                  <div id="price-range3" class="slider"></div>
                  <input type="hidden" name="rent-range" id="rent-range-selected" onchange="rangeChanged()">
                </div>
              </div>
            </div>

            <div>
              <div class="slider-box">
                <label for="priceRange">Size:</label>
                <input type="text" id="priceRange2" readonly>
                <div id="price-range2" class="slider"></div>
                <input type="hidden" name="size-range" id="size-range-selected" onchange="rangeChanged()">
              </div>
            </div>

            <div class="price-range-btm">
              <div class="MuiBox-root css-69324s">
                <div>
                  <button tabindex="0" type="button" id="save_map_layer" class="MuiButtonBase-root MuiButton-root MuiButton-contained MuiButton-containedPrimary MuiButton-sizeMedium MuiButton-containedSizeMedium MuiButton-colorPrimary MuiButton-root MuiButton-contained MuiButton-containedPrimary MuiButton-sizeMedium MuiButton-containedSizeMedium MuiButton-colorPrimary css-1hw9j7s"> Save to a new map layer <span class="MuiTouchRipple-root css-w0pj6f"></span>
                  </button>
                  <!-- Popup content -->

                  <div class="tcr-popup-overlay"></div>

                  <div class="tcr-popup-wrapper" id="tcr-popup-wrapper">

                    <div class="tcr-popup-content" id="tcr-req-acc-output">

                      <h4>SAVE TO A NEW MAP LAYER</h4>
                      <div id="map-layer-content">
                        <ul>
                          <li><label>Map Title</label>
                            <input type="text" name="map_post_title" id="map_post_title">
                          </li>
                          <li>
                            <label>Map Layer Title</label>
                            <input type="text" name="map_layer_title" id="map_layer_title">
                          </li>
                        </ul>

                        <input type="hidden" name="map_layer_post_ids" id="map_layer_post_ids">
                        <input type="submit" id="submit_map_layer" name="submit_layer" id="" value="save to a new map layer">
                      </div>
                      <div id="map_layer_show_message"></div>
                    </div>

                    <button id="tcr-popup-close-button">X</button>
                  </div>
                  <!-- Popup content end -->
                </div>
              </div>
              <div class="MuiBox-root css-69324s">
                <div>
                
                  <button id="filter-clear11" tabindex="0" type="button" class="MuiButtonBase-root MuiButton-root MuiButton-contained MuiButton-containedPrimary MuiButton-sizeMedium MuiButton-containedSizeMedium MuiButton-colorPrimary MuiButton-root MuiButton-contained MuiButton-containedPrimary MuiButton-sizeMedium MuiButton-containedSizeMedium MuiButton-colorPrimary bg-red css-1hw9j7s color-white"> Clear Filter <span class="MuiTouchRipple-root css-w0pj6f"></span>
                  </button>
                </div>
              </div>
        
              <div id="tristate-result-count">
                <p><?php echo 'Showing ' . __total() . ' of ' . __total() . ' Listing' ?></p>
              </div>
            </div>

          </div>
        </div>
      </div>
      <div class="right-content">
        <div id="menu-btn"><i class="fa fa-angle-left"></i></div>
        <div class="right-map">
          <!-- <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d407542.86304287874!2d-74.32724652492182!3d40.69942908913206!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!z4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2snp!4v1711702301417!5m2!1sen!2snp" allowfullscreen="allowFullScreen" width="100%" height="450px" style="position: relative; display: block;"></iframe> -->
          <div id="tristate-map" style="height:600px; width:100%;position:relative;display:block;"></div>
        </div>

        <div class="post-output"></div>




        <div class="property-list-wrapper">
          <div class="MuiBox-root">
            <div class="MuiStack-root property-filter css-12xuzbq" id="propertylisting-content">

              <?php
              // Perform the query to fetch search results
              $args = array(
                'post_type' => 'properties', // Specify the post type
                'posts_per_page' => -1, // Retrieve all posts of the specified post type

                // Add any additional arguments as needed
              );

              $search_query = new WP_Query($args);


              // Output the search results
              if ($search_query->have_posts()) {
                while ($search_query->have_posts()) {
                  $search_query->the_post();

                  /*  -------------------Start Meta data---------------- */


                  $ID               = get_the_ID();
                  $buildout_id       = (int) get_post_meta($ID, '_buildout_id', true);
                  $title             = get_post_meta($ID, '_buildout_sale_listing_web_title', true);
                  $subtitle         = implode(', ', array(get_post_meta($ID, '_buildout_city', true), get_post_meta($ID, '_buildout_county', true), get_post_meta($ID, '_buildout_state', true)));
                  $badges           = array(
                    'use'         => get_post_meta($ID, '_gsheet_use', true),
                    'type'         => get_post_meta($ID, '_gsheet_listing_type', true),
                    'price_sf'     => get_post_meta($ID, '_gsheet_price_sf', true),
                    'commission'   => get_post_meta($ID, '_gsheet_commission', true)
                  );
                  $_use             = get_post_meta($ID, '_gsheet_use', true);
                  $_type             = get_post_meta($ID, '_gsheet_listing_type', true);
                  $_price_sf         = get_post_meta($ID, '_gsheet_price_sf', true);
                  $_price_sf         = preg_replace('/\.[0-9]+/', '', $_price_sf);
                  $_price_sf         = (int) preg_replace('/[^0-9]/', '', $_price_sf);
                  $_commission       = get_post_meta($ID, '_gsheet_commission', true);
                  $summary           = get_post_meta($ID, '_buildout_location_description', true);
                  $min_size         = get_post_meta($ID, '_gsheet_min_size_fm', true);
                  $max_size         = get_post_meta($ID, '_gsheet__max_size_fm', true);
                  $zoning           = get_post_meta($ID, '_buildout_zoning', true);
                  $key_tag           = get_post_meta($ID, '_gsheet_key_tag', true);
                  //$agents           = (array) tristatectr_get_brokers_with_excluded(get_post_meta($ID, '_buildout_broker_ids', true));
                  $_agent           = get_post_meta($ID, '_gsheet_listing_agent', true);
                  $lease_out         = get_post_meta($ID, '_gsheet_lease_out', true);

                  $lease_conditions = get_post_meta($ID, '_buildout_lease_description', true);
                  $lease_conditions = get_post_meta($ID, '_gsheet_lease_conditions', true);

                  $bo_price         = empty(get_post_meta($ID, '_buildout_sale_price_dollars', true)) ? 0 : get_post_meta($ID, '_buildout_sale_price_dollars', true);
                  $price             = get_post_meta($ID, '_gsheet_monthly_rent', true);
                  // Remove fractional units from the price
                  $_price           = preg_replace('/\.[0-9]+/', '', $price);
                  // Convert the price to integer value
                  $_price = (int) preg_replace('/[^0-9]/', '', $_price);
                  $more_info         = get_post_meta($ID, '_gsheet_link_to_more_info', true);
                  $more_info         = get_post_meta($ID, '_buildout_sale_listing_url', true) ?? get_post_meta($ID, '_buildout_lease_listing_url', true);
                  $tour3d           = get_post_meta($ID, '_gsheet_3d_tour', true);
                  $tour3d           = get_post_meta($ID, '_buildout_matterport_url', true);
                  $youtube_url       = get_post_meta($ID, '_buildout_you_tube_url', true);
                  $zip               = get_post_meta($ID, '_gsheet_zip', true) ?? get_post_meta($ID, '_buildout_zip', true);
                  $neighborhood     = get_post_meta($ID, '_gsheet_neighborhood', true);
                  $vented           = get_post_meta($ID, '_gsheet_vented', true);
                  $city             = get_post_meta($ID, '_buildout_city', true);
                  $borough           = get_post_meta($ID, '_gsheet_borough', true);
                  // $state             = get_post_meta($ID, '_gsheet_state', true);
                  $gsheet_state = get_post_meta($ID, '_gsheet_state', true);
                  $buildout_state = get_post_meta($ID, '_buildout_state', true);
                  $state = !empty($gsheet_state) ? $gsheet_state : (!empty($buildout_state) ? $buildout_state : '');

                  $image             = false;
                  if ($photos = get_post_meta($ID, '_buildout_photos', true)) {
                    $photo = reset($photos);
                    $image = $photo->formats->thumb ?? '';
                  }


                  $markers_data[] = [
                    'lat' => get_post_meta($ID, '_buildout_latitude', true),
                    'long' => get_post_meta($ID, '_buildout_longitude', true),
                    'popup_data' => [
                      'title' => $title,
                      'sub_title' => $subtitle,
                      'type' => $_type,
                      'image' => $image,
                      'summary' => $summary,
                      'link' => get_the_permalink(get_the_ID())
                    ]

                  ];
                  $meta_vrs = [ 
                    'City'=>$city,
                    'State'=>$state,
                    'Min Size'=>$min_size,
                    'Max Size'=>$max_size,
                    'Zoning'=>$zoning,
                    'Key Tag' => $key_tag,
                    'Listing Agent'=>$_agent,
                    'Vented'=>$vented,
                    'Borough'=>$borough,
                    'Neighborhood'=>$neighborhood,
                    'Zip Code'=>$zip
                ];
                  /*  -------------------END Meta data---------------- */


              ?>

                  <div class="propertylisting-content">
                    <div class="plc-top">
                      <h2><?php
                          echo esc_html(get_the_title()); ?></h2>
                      <h4><?php echo $subtitle; ?></h4>
                      <div class="css-ajk2hm">
                        <ul class="ul-buttons">
                          <?php
                          if (!empty($badges)) {
                            foreach ($badges as $key => $value) {
                              if (!empty($value)) {
                                switch ($key) {
                                  case 'use':
                                    $class = 'bg-blue';
                                    break;
                                  case 'type':
                                    $class = 'bg-green';
                                    break;
                                  case 'price_sf':
                                    $class = 'bg-yellow';
                                    break;
                                  case 'commission':
                                    $class = 'bg-red';
                                    break;
                                  default:
                                    $class = '';
                                }
                                echo '<li class="' . $class . '"><span>' . $value . '</span></li>';
                              }
                            }
                          }
                          ?>


                        </ul>
                        <ul class="ul-content">
                          <?php
                          echo $lease_conditions;
                          ?>
                        </ul>
                        <ul class="ul-content ul-features">
                        <?php foreach($meta_vrs as $k=>$v){ echo !empty($v) ? ' <li><p>'.$k.': <span>'.$v.'</span></p></li>' : ''; } ?>

                        </ul>
                      </div>
                    </div>
                    <div class="plc-bottom">
                    <?php if (!empty($bo_price) && $bo_price !== '0'): ?>
              <p class="price"><?php echo '$' . $bo_price; ?></p>
          <?php endif; ?>
                      <a href="<?php the_permalink(); ?>" target="_blank" class="MuiButton-colorPrimary"> More Info </a>
                    </div>

                  </div>
              <?php

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
    function rangeChanged() {
      jQuery(document).ready(function($) {

        function getSelectedListingTypes2() {
          var selectedTypes = [];
          $('#_gsheet_listing_type input[type="checkbox"]:checked').each(function() {
            selectedTypes.push($(this).val());
          });
          return selectedTypes;
        }

        $.ajax({
          url: '<?php echo admin_url('admin-ajax.php'); ?>',
          type: 'POST',
          data: {
            action: 'live_search',
            _buildout_city: $('#_buildout_city').val(),
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

      });
    }
  </script>
  <!-- text data 1 -->
  <textarea style="display: none;" id="marker_data_all"><?php echo json_encode($markers_data) ?></textarea>
  <?php
  /*        $cached_content = ob_get_clean(); 
              set_transient('property_listing_content', $cached_content);
          }
          
          echo $cached_content; 

  return ob_get_clean(); */
  ?>
<script>
  $(document).ready(function() {
    var input = $('#tristate-input');
    var timer;
    var cachedResults = {};

    function makeRequest() {
      var searchText = input.val().trim();

      if (searchText.length < 3) {
        return;
      }

      if (searchText in cachedResults) {
        $('#propertylisting-content').html(cachedResults[searchText]);
        return;
      }

      $.ajax({
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        type: 'POST',
        data: {
          action: 'live_search',
          search_text: searchText
        },
        dataType: 'html',
        success: function(response) {
          $('#propertylisting-content').html(response);
          cachedResults[searchText] = response;
        }
      });
    }

    input.keyup(function() {
      clearTimeout(timer);
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

  // Get the selected broker IDs
  $brokerIds = isset($_POST['broker_ids']) ? $_POST['broker_ids'] : array();
  $neighborhoodIds = isset($_POST['neighborhoodIds']) ? $_POST['neighborhoodIds'] : array();
  //neighborhood_ids
  $mark_data = [];
  // Initialize the query arguments
  $args = array(
    'post_type' => 'properties', // Specify the post type
    'posts_per_page' => -1, // Retrieve all posts of the specified post type
    'post_status' => 'publish'
  );

  // Initialize meta query if not already initialized
  if (!isset($args['meta_query'])) {
    $args['meta_query'] = array('relation' => 'AND'); // Assuming you want to meet all conditions
  }



  // Process selected types
  if (!empty($_POST['selected_type'])) {
    $args['meta_query'][] = array(
      'key' => '_gsheet_listing_type',
      'value' => $_POST['selected_type'],
      'compare' => 'IN',
    );
  }

  if (!empty($_POST['neighborhoodIds'])) {
    $args['meta_query'][] = array(
      'key' => '_gsheet_neighborhood',
      'value' => $_POST['neighborhoodIds'],
      'compare' => 'IN',
    );
  }

  if (!empty($_POST['_buildout_city'])) {
    $args['meta_query'][] = array(
      'key' => '_buildout_city',
      'value' => $_POST['_buildout_city'],
      'compare' => 'IN',
    );
  }

  if (!empty($_POST['_gsheet_use'])) {
    $args['meta_query'][] = array(
      'key' => '_gsheet_use',
      'value' => $_POST['_gsheet_use'],
      'compare' => 'IN',
    );
  }

  if (!empty($_POST['selected_type'])) {
    $args['meta_query'][] = array(
      'key' => '_gsheet_listing_type',
      'value' => $_POST['selected_type'],
      'compare' => 'IN',
    );
  }

  if (!empty($_POST['_gsheet_zip'])) {
    $args['meta_query'][] = array(
      'key' => '_gsheet_zip',
      'value' => $_POST['_gsheet_zip'],
      'compare' => 'IN',
    );
  }
  
/*   if (!empty($_POST['_gsheet_state'])) {
    $args['meta_query'][] = array(
      'key' => '_gsheet_state',
      'value' => 'PA',
      'compare' => '=',
    );
  } */

/*   if (!empty($_POST['_gsheet_state'])) {
    $args['meta_query'][] = array(
      'relation' => 'OR',
      array(
        'key' => '_gsheet_state',
        'value' => $_POST['_gsheet_state'],
        'compare' => '=',
      ),
      array(
        'key' => '_buildout_state',
        'value' => $_POST['_gsheet_state'],
        'compare' => '=',
      ),
    );
  } */

  
  if (!empty($_POST['_gsheet_state'])) {
    $args['meta_query'][] = array(
      'key' => '_buildout_state',
      'value' => $_POST['_gsheet_state'],
      'compare' => 'IN',
    );
  }




  if (!empty($_POST['_gsheet_vented'])) {
    $args['meta_query'][] = array(
      'key' => '_gsheet_vented',
      'value' => $_POST['_gsheet_vented'],
      'compare' => 'IN',
    );
  }

  // Process other meta queries
  $meta_queries = array(
    '_gsheet_neighborhood' => 'OR',
    '_gsheet_zip' => 'OR',
    '_buildout_city' => 'OR',
    '_buildout_state' => 'OR',
    '_gsheet_vented' => 'OR',
    '_gsheet_use' => 'OR'

  );

  foreach ($meta_queries as $meta_key => $relation) {
    if (!empty($_POST[$meta_key])) {
      $args['meta_query'][] = array(
        'key' => $meta_key,
        'value' => $_POST[$meta_key],
        'compare' => 'IN',
        'relation' => $relation,
      );
    }
  }

  // If search text is provided, include it in the query
  if (!empty($search_text)) {
    $args['orderby'] = 'relevance'; // Order results by relevance
    $args['s'] = $search_text; // Search text
    $args['sentence'] = false; // Match individual words
  }

  // Include broker IDs in the query
  if (!empty($brokerIds)) {
    $args['meta_query'][] = array(
      'key' => '_gsheet_listing_agent',
      'value' => $brokerIds,
      'compare' => 'IN',
    );
  }

  //================Range Filters===============
  if (!empty($_POST['property_size_range'])) {

    $size_range = sanitize_text_field($_POST['property_size_range']);
    $sizes = explode('-', $size_range);
    $min_value_size = $sizes[0];
    $max_value_size = $sizes[1];



    $args['meta_query'][] = array(

      'relation'     => 'AND', // Use 'AND' relation for multiple meta queries
      array(
        'key'     => '_gsheet_min_size_fm',
        'value'   => $min_value_size, // Replace YOUR_MIN_VALUE with the minimum value
        'type'    => 'NUMERIC',
        'compare' => '>=',
      ),
      array(
        'key'     => '_gsheet__max_size_fm',
        'value'   => $max_value_size, // Replace YOUR_MAX_VALUE with the maximum value
        'type'    => 'NUMERIC',
        'compare' => '<=',
      ),

    );
  }

  if (!empty($_POST['property_price_range'])) {

    $price_range = sanitize_text_field($_POST['property_price_range']);

    $prices = explode('-', $price_range);

    $args['meta_query'][] = array(
      'key' => '_buildout_sale_price_dollars',
      'value' => array($prices[0], $prices[1]),
      'compare' => 'BETWEEN',
      'type' => 'NUMERIC'
    );
  }

  if (!empty($_POST['property_rent_range'])) {

    $rent_range = sanitize_text_field($_POST['property_rent_range']);

    $rents = explode('-', $rent_range);

    $args['meta_query'][] = array(
      'key' => '_gsheet_monthly_rent',
      'value' => array($rents[0], $rents[1]),
      'type' => 'NUMERIC'
    );
  }
  //================Range Filters Ends ===============

  // Run the query
  $drt_query = new WP_Query($args);
  //print_r($drt_query);
  $total_search_results = $drt_query->found_posts;
  $totals = __total();
  // Showing 716 of 716 Listing
  $results_string = "Showing {$total_search_results} of {$totals} listing";

  // Output the search results
  if ($drt_query->have_posts()) {
    while ($drt_query->have_posts()) {
      $drt_query->the_post();

      /*  -------------------Start Meta data---------------- */


      $ID               = get_the_ID();
      $subtitle         = implode(', ', array(get_post_meta($ID, '_buildout_city', true), get_post_meta($ID, '_buildout_county', true), get_post_meta($ID, '_buildout_state', true)));
      $_use             = get_post_meta($ID, '_gsheet_use', true);
      $_type             = get_post_meta($ID, '_gsheet_listing_type', true);
      $badges           = array(
        'use'         => get_post_meta($ID, '_gsheet_use', true),
        'type'         => get_post_meta($ID, '_gsheet_listing_type', true),
        'price_sf'     => get_post_meta($ID, '_gsheet_price_sf', true),
        'commission'   => get_post_meta($ID, '_gsheet_commission', true)
      );
      $_price_sf         = get_post_meta($ID, '_gsheet_price_sf', true);
      $_price_sf         = preg_replace('/\.[0-9]+/', '', $_price_sf);
      $_price_sf         = (int) preg_replace('/[^0-9]/', '', $_price_sf);
      $min_size         = get_post_meta($ID, '_gsheet_min_size_fm', true);
      $max_size         = get_post_meta($ID, '_gsheet__max_size_fm', true);

      $zoning           = get_post_meta($ID, '_buildout_zoning', true);
      $key_tag           = get_post_meta($ID, '_gsheet_key_tag', true);
      $_agent           = get_post_meta($ID, '_gsheet_listing_agent', true);

      $lease_conditions = get_post_meta($ID, '_buildout_lease_description', true);
      $lease_conditions = get_post_meta($ID, '_gsheet_lease_conditions', true);

      $bo_price         = empty(get_post_meta($ID, '_buildout_sale_price_dollars', true)) ? 0 : get_post_meta($ID, '_buildout_sale_price_dollars', true);
      $price             = get_post_meta($ID, '_gsheet_monthly_rent', true);
      // Remove fractional units from the price
      $_price           = preg_replace('/\.[0-9]+/', '', $price);
      // Convert the price to integer value
      $_price = (int) preg_replace('/[^0-9]/', '', $_price);
      $zip               = get_post_meta($ID, '_gsheet_zip', true) ?? get_post_meta($ID, '_buildout_zip', true);
      $neighborhood     = get_post_meta($ID, '_gsheet_neighborhood', true);
      $vented           = get_post_meta($ID, '_gsheet_vented', true);
      $city             = get_post_meta($ID, '_buildout_city', true);
      $borough           = get_post_meta($ID, '_gsheet_borough', true);
      $state             = get_post_meta($ID, '_gsheet_state', true);
      $title             = get_post_meta($ID, '_buildout_sale_listing_web_title', true);
      $subtitle         = implode(', ', array(get_post_meta($ID, '_buildout_city', true), get_post_meta($ID, '_buildout_county', true), get_post_meta($ID, '_buildout_state', true)));
      $summary           = get_post_meta($ID, '_buildout_location_description', true);
      $image             = false;
      if ($photos = get_post_meta($ID, '_buildout_photos', true)) {
        $photo = reset($photos);
        $image = $photo->formats->thumb ?? '';
      }
      $mark_data[] = [
        'lat' => get_post_meta($ID, '_buildout_latitude', true),
        'long' => get_post_meta($ID, '_buildout_longitude', true),
        'popup_data' => [
          'title' => $title,
          'sub_title' => $subtitle,
          'type' => $_type,
          'image' => $image,
          'summary' => $summary,
          'link' => get_the_permalink(get_the_ID())
        ]

      ];
      $meta_vrs = [ 
        'City'=>$city,
        'State'=>$state,
        'Min Size'=>$min_size,
        'Max Size'=>$max_size,
        'Zoning'=>$zoning,
        'Key Tag' => $key_tag,
        'Listing Agent'=>$_agent,
        'Vented'=>$vented,
        'Borough'=>$borough,
        'Neighborhood'=>$neighborhood,
        'Zip Code'=>$zip
    ];

      /*  -------------------END Meta data---------------- */


?>

      <div class="propertylisting-content">
        <input type="hidden" name="get_properties_id" id="get_properties_id" value="<?php echo $ID; ?>">
        <h2><?php
            echo esc_html(get_the_title()); ?></h2>
        <h4><?php echo $subtitle; ?></h4>
        <div class="css-ajk2hm">
          <ul class="ul-buttons">
            <?php
            if (!empty($badges)) {
              foreach ($badges as $key => $value) {
                if (!empty($value)) {
                  switch ($key) {
                    case 'use':
                      $class = 'bg-blue';
                      break;
                    case 'type':
                      $class = 'bg-green';
                      break;
                    case 'price_sf':
                      $class = 'bg-yellow';
                      break;
                    case 'commission':
                      $class = 'bg-red';
                      break;
                    default:
                      $class = '';
                  }
                  echo '<li class="' . $class . '"><span>' . $value . '</span></li>';
                }
              }
            }
          
            ?>

          </ul>
          <ul class="ul-content">
            <?php
            echo $lease_conditions;
            ?>
          </ul>
          <ul class="ul-content ul-features">
          <?php foreach($meta_vrs as $k=>$v){ echo !empty($v) ? ' <li><p>'.$k.': <span>'.$v.'</span></p></li>' : ''; } ?>

          </ul>
          <?php if (!empty($bo_price) && $bo_price !== '0'): ?>
              <p class="price"><?php echo '$' . $bo_price; ?></p>
          <?php endif; ?>
          <a href="<?php the_permalink(); ?>" target="_blank" class="MuiButton-colorPrimary"> More Info </a>
        </div>
      </div>
    <?php
    }
    wp_reset_postdata();
    ?>

  <?php
  } else {
    echo '<p>No results found.</p>';
  }
  ?>
  <!-- text data 2 -->
  <textarea style="display: none;" id="ajax-marker-data" rows="4" cols="50"> <?php echo json_encode($mark_data); ?> </textarea>
  <script>
    var newStr = "<?php echo $results_string; ?>";
    jQuery("#ajax-marker-data").trigger('change');
    jQuery("#tristate-result-count>p").text(newStr);
   
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

  // Custom SQL query to fetch unique values based on meta key and selected broker IDs
  $query = $wpdb->prepare("
    SELECT DISTINCT meta_value 
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

  // Storing the values obtained from the second query into the array
  if ($results) {
    foreach ($results as $result) {
      $matched_uses[] = $result->meta_value;
    }
  }

  // Custom SQL query to fetch all unique values
  $query_all = $wpdb->prepare("
    SELECT DISTINCT meta_value 
    FROM $table_name 
    WHERE meta_key = %s", $meta_key);

  // Fetching all results from the database
  $results_all = $wpdb->get_results($query_all);

  // Processing and displaying the results
  if ($results_all) {
    foreach ($results_all as $result) {
      $uses = $result->meta_value;
      // Check if $uses is found in $matched_uses array
      $is_matched = in_array($uses, $matched_uses);
      // If $selected_broker_ids is empty, do not disable the option
      // Otherwise, disable the option if $uses is not found in $matched_uses array
      $disabled = empty($selected_broker_ids) ? '' : ($is_matched ? '' : 'disabled');
      echo '<option value="' . $uses . '" data-uses="' . $uses . '" ' . $disabled . '>' . $uses . '</option>';
    }
  } else {
    echo '<option>No uses found</option>';
  }

  die();
}
// Assuming this is inside a WordPress theme or plugin

// add_action('wp_ajax_get_neighbourhoods_dropdown', 'get_neighbourhoods_dropdown_callback');
// add_action('wp_ajax_nopriv_get_neighbourhoods_dropdown', 'get_neighbourhoods_dropdown_callback');

function get_dropdown_select_options_drdown(){

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
    if (!empty($selected_broker_ids)) {
        $query .= " AND post_id IN (
            SELECT post_id 
            FROM $table_name 
            WHERE meta_key = '_gsheet_listing_agent' 
            AND meta_value IN ('" . implode("','", $selected_broker_ids) . "')
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
    if (!empty($selected_state)) {
        $query .= " AND post_id IN (
            SELECT post_id 
            FROM $table_name 
            WHERE meta_key = '_gsheet_state' 
            AND meta_value IN ('" . implode("','", $selected_state) . "')
        )";
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

function get_zip_dropdown_callback(){

    $selected_broker_ids = isset($_POST['broker_ids']) ? $_POST['broker_ids'] : array();
    $selected_city =  isset($_POST['_buildout_city']) ? $_POST['_buildout_city'] : array();
    $selected_use  =  isset($_POST['_gsheet_use']) ? $_POST['_gsheet_use'] : array();
    $selected_neighbourhoods = isset($_POST['_gsheet_neighborhood']) ? $_POST['_gsheet_neighborhood'] : array();
    $selected_zip = isset($_POST['_gsheet_zip']) ? $_POST['_gsheet_zip'] : array();
    $selected_state = isset($_POST['_gsheet_state']) ? $_POST['_gsheet_state'] : array();
    $selected_vented = isset($_POST['_gsheet_vented']) ? $_POST['_gsheet_vented'] : array();

    global $wpdb;
    $table_name = $wpdb->prefix . 'postmeta';
    $meta_key = '_gsheet_zip';

    $query = "SELECT DISTINCT meta_value FROM $table_name WHERE meta_key = %s ";

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
    if (!empty($selected_broker_ids)) {
        $query .= " AND post_id IN (
            SELECT post_id 
            FROM $table_name 
            WHERE meta_key = '_gsheet_listing_agent' 
            AND meta_value IN ('" . implode("','", $selected_broker_ids) . "')
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
    if (!empty($selected_state)) {
        $query .= " AND post_id IN (
            SELECT post_id 
            FROM $table_name 
            WHERE meta_key = '_gsheet_state' 
            AND meta_value IN ('" . implode("','", $selected_state) . "')
        )";
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
    $query_all = $wpdb->prepare("SELECT DISTINCT meta_value FROM $table_name WHERE meta_key = %s", $meta_key);
    $results_all = $wpdb->get_results($query_all);

    // Processing and displaying the results
    if ($results_all) {
        foreach ($results_all as $result) {  //tes
            $zip = $result->meta_value;
            // Check if $zip is found in $matched_zip array
            $is_matched = in_array($zip, $matched_zip);
            // If $selected_broker_ids is empty, do not disable the option
            // Otherwise, disable the option if $zip is not found in $matched_zip array
            $disabled = empty($selected_broker_ids) ? '' : ($is_matched ? '' : 'disabled');
            echo '<option value="' . $zip . '" data-zip="' . $zip . '" ' . $disabled . '>' . $zip . '</option>';
        }
    } else {
        echo '<option>No ZIP Codes Found</option>';
    }

    wp_die();

}



// Modified get_agent_states_callback function
add_action('wp_ajax_get_agent_states', 'get_agent_states_callback');
add_action('wp_ajax_nopriv_get_agent_states', 'get_agent_states_callback');

function get_agent_states_callback()
{
  // Get the selected broker IDs from the AJAX request
  $selected_broker_ids = isset($_POST['broker_ids']) ? $_POST['broker_ids'] : array();

  global $wpdb;

  // Replace 'wp_' with your WordPress table prefix if it's different
  $table_name = $wpdb->prefix . 'postmeta';
  $meta_key_gsheet = '_gsheet_state';
  $meta_key_buildout = '_buildout_state';

  // Custom SQL query to fetch unique city values based on both meta keys and selected broker IDs
  $query = $wpdb->prepare("
      SELECT DISTINCT meta_value 
      FROM $table_name 
      WHERE meta_key IN (%s, %s)
      AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_listing_agent' 
          AND meta_value IN ('" . implode("','", $selected_broker_ids) . "')
      )
  ", $meta_key_gsheet, $meta_key_buildout);

  // Fetching results from the database
  $results = $wpdb->get_results($query);

  // Array to store the values obtained from the second query
  $matched_cities = array();

  // Storing the values obtained from the second query into the array
  if ($results) {
      foreach ($results as $result) {
          $matched_cities[] = $result->meta_value;
      }
  }

  // Custom SQL query to fetch all unique city values
  $query_all = $wpdb->prepare("SELECT DISTINCT meta_value 
      FROM $table_name 
      WHERE meta_key = %s", $meta_key_buildout);

  // Fetching all results from the database
  $results_all = $wpdb->get_results($query_all);

  // Processing and displaying the results
  if ($results_all) {
      foreach ($results_all as $result) {
          $city = $result->meta_value;
          
          // Check if $selected_broker_ids is empty or an empty array
          // If it is, do not add the 'disabled' attribute
          // Otherwise, add the 'disabled' attribute
          $disabled = (empty($selected_broker_ids) || !is_array($selected_broker_ids)) ? '' : (in_array($city, $matched_cities) ? '' : 'disabled');
          
          echo '<option value="' . $city . '" data-city="' . $city . '" ' . $disabled . '>' . $city . '</option>';
      }
  } else {
      echo '<option>No City found</option>';
  }

  die();
}


// Register AJAX actions for logged-in and not logged-in users
add_action('wp_ajax_get_zipcodes', 'get_zipcodes_callback');
add_action('wp_ajax_nopriv_get_zipcodes', 'get_zipcodes_callback');

function get_zipcodes_callback() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'postmeta';

    if (isset($_POST['status'])) {
        switch ($_POST['status']) {
            case 'state_to_zip':
                // Get selected states from AJAX request and ensure it's an array
                $selected_states = isset($_POST['_gsheet_state']) ? $_POST['_gsheet_state'] : array();
                
                // Prepare the SQL statement using safe methods
                if (!empty($selected_states)) {
                    $query = $wpdb->prepare("SELECT DISTINCT pm1.meta_value AS zipcode
                        FROM $table_name AS pm1
                        INNER JOIN $table_name AS pm2 ON pm1.post_id = pm2.post_id
                        WHERE pm2.meta_key = '_gsheet_state' AND pm2.meta_value IN (%s)",
                        implode("', '", array_map('esc_sql', $selected_states)));
                
                    $results = $wpdb->get_results($query);
                    $matched_zipcodes = wp_list_pluck($results, 'zipcode');
                }

                break;

            case 'agent_to_zip':
                $selected_broker_ids = isset($_POST['broker_ids']) ? $_POST['broker_ids'] : array();

                if (!empty($selected_broker_ids)) {
                    $query = $wpdb->prepare("SELECT DISTINCT meta_value AS zipcode
                        FROM $table_name
                        WHERE meta_key = '_gsheet_zip'
                        AND post_id IN (
                            SELECT post_id
                            FROM $table_name
                            WHERE meta_key = '_gsheet_listing_agent'
                            AND meta_value IN (%s)",
                            implode("', '", array_map('esc_sql', $selected_broker_ids)));
                    
                    $results = $wpdb->get_results($query);
                    $matched_zipcodes = wp_list_pluck($results, 'zipcode');
                }
                
                break;
        }

        // Fetch all zip codes for dropdown if needed
        $all_zipcodes = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT meta_value FROM $table_name WHERE meta_key = '_gsheet_zip'"));

        // Output the HTML for dropdown
        if ($all_zipcodes) {
            foreach ($all_zipcodes as $zipcode) {
                $disabled = !in_array($zipcode, $matched_zipcodes) ? 'disabled' : '';
                echo '<option value="' . esc_attr($zipcode) . '" ' . $disabled . '>' . esc_html($zipcode) . '</option>';
            }
        } else {
            echo '<option>No Zip Code found</option>';
        }
    }

    die(); // Proper termination
}





add_action('wp_ajax_get_agent_cities', 'get_agent_cities_callback');
add_action('wp_ajax_nopriv_get_agent_cities', 'get_agent_cities_callback');

function get_agent_cities_callback() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'postmeta';

    if (isset($_POST['status'])) {
        switch ($_POST['status']) {
            case 'state_to_city':
                $selected_gsheet_state = isset($_POST['_gsheet_state']) ? $_POST['_gsheet_state'] : array();
                $selected_buildout_state = $selected_gsheet_state; // This looks like it was meant to be another POST variable.

                $states = array_merge($selected_gsheet_state, $selected_buildout_state);
                $states = array_unique($states);

                $query = $wpdb->prepare("SELECT DISTINCT pm1.meta_value AS city
                    FROM $table_name AS pm1
                    INNER JOIN $table_name AS pm2 ON pm1.post_id = pm2.post_id
                    WHERE pm2.meta_key IN ('_gsheet_state', '_buildout_state') AND pm2.meta_value IN (%s)",
                    implode("', '", array_map('esc_sql', $states)));

                $results = $wpdb->get_results($query);
                $matched_cities = wp_list_pluck($results, 'city');

                break;

            case 'agent_to_city':
                $selected_broker_ids = isset($_POST['broker_ids']) ? $_POST['broker_ids'] : array();

                if (!empty($selected_broker_ids)) {
                    $query = $wpdb->prepare("SELECT DISTINCT meta_value AS city
                        FROM $table_name
                        WHERE meta_key = '_buildout_city'
                        AND post_id IN (
                            SELECT post_id
                            FROM $table_name
                            WHERE meta_key = '_gsheet_listing_agent'
                            AND meta_value IN (%s)
                        )", implode("', '", array_map('esc_sql', $selected_broker_ids)));

                    $results = $wpdb->get_results($query);
                    $matched_cities = wp_list_pluck($results, 'city');
                }
                break;
        }

        // Fetch all cities for dropdown
        $all_cities = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT meta_value FROM $table_name WHERE meta_key = '_buildout_city'"));
        
        // Display the results
        if ($all_cities) {
            foreach ($all_cities as $city) {
                $disabled = !in_array($city, $matched_cities) ? 'disabled' : '';
                echo '<option value="' . esc_attr($city) . '" ' . $disabled . '>' . esc_html($city) . '</option>';
            }
        } else {
            echo '<option>No City found</option>';
        }
    }

    die(); // Proper termination
}


///get_cities_by_state_dropdown
add_action('wp_ajax_get_cities_by_state_dropdown', 'get_cities_by_state_dropdown_callback');
add_action('wp_ajax_nopriv_get_cities_by_state_dropdown', 'get_cities_by_state_dropdown_callback');

function get_cities_by_state_dropdown_callback()
{
    // Get the selected gsheet state from the AJAX request
    $selected_gsheet_state = isset($_POST['_gsheet_state']) ? sanitize_text_field($_POST['_gsheet_state']) : '';

   // Get the selected broker IDs from the AJAX request
   //$selected_broker_ids = isset($_POST['broker_ids']) ? $_POST['broker_ids'] : array();

   global $wpdb;

   // Replace 'wp_' with your WordPress table prefix if it's different
   $table_name = $wpdb->prefix . 'postmeta';
   $meta_key_city = '_buildout_city';

   // Custom SQL query to fetch unique city values where the associated post IDs are filtered by gsheet states
   $query = $wpdb->prepare("
       SELECT DISTINCT pm1.meta_value AS city
       FROM $table_name AS pm1
       JOIN $table_name AS pm2 ON pm1.post_id = pm2.post_id
       WHERE pm1.meta_key = %s
       AND pm2.meta_key = '_gsheet_state'
       AND pm2.meta_value IN ('" . implode("','", $selected_gsheet_state) . "')
   ", $meta_key_city);

   // Fetching results from the database
   $results = $wpdb->get_results($query);

   // Array to store the cities matched with selected states
   $matched_cities = array();

   // Storing the cities obtained from the query into the array
   if ($results) {
       foreach ($results as $result) {
           $matched_cities[] = $result->city;
       }
   }

   // Custom SQL query to fetch all unique city values
   $query_all = $wpdb->prepare("SELECT DISTINCT meta_value FROM $table_name WHERE meta_key = %s", $meta_key_city);

   // Fetching all results from the database
   $results_all = $wpdb->get_results($query_all);

   // Processing and displaying the results
   if ($results_all) {
       foreach ($results_all as $result) {
           $city = $result->meta_value;
           $disabled = (!in_array($city, $matched_cities)) ? 'disabled' : '';
           echo '<option value="' . $city . '" ' . $disabled . '>' . $city . '</option>';
       }
   } else {
       echo '<option>No City found</option>';
   }

   die();
}




add_action('wp_ajax_get_agents_dropdown', 'get_agents_dropdown_handler');
add_action('wp_ajax_nopriv_get_agents_dropdown', 'get_agents_dropdown_handler'); // If needed for users not logged in

function get_agents_dropdown_handler()
{
  // Collect input values
  $use = isset($_POST['_gsheet_use']) ? $_POST['_gsheet_use'] : '';
  $type = isset($_POST['selected_type']) ? $_POST['selected_type'] : '';
  $neighborhood = isset($_POST['_gsheet_neighborhood']) ? $_POST['_gsheet_neighborhood'] : '';
  $zip = isset($_POST['_gsheet_zip']) ? $_POST['_gsheet_zip'] : '';
  $city = isset($_POST['_buildout_city']) ? $_POST['_buildout_city'] : '';
  $state = isset($_POST['_gsheet_state']) ? $_POST['_gsheet_state'] : '';
  $vented = isset($_POST['_gsheet_vented']) ? $_POST['_gsheet_vented'] : '';
  $clear = isset($_POST['clear']) ? $_POST['clear'] : '';
 
if($clear!='true' && !empty($clear)){
  // Initialize the meta query array
  $meta_query = array('relation' => 'AND');

  // Only add meta conditions if values are provided
  if (!empty($use)) $meta_query[] = array('key' => '_gsheet_use', 'value' => $use);
  if (!empty($type)) $meta_query[] = array('key' => '_gsheet_listing_type', 'value' => $type);
  if (!empty($neighborhood)) $meta_query[] = array('key' => '_gsheet_neighborhood', 'value' => $neighborhood);
  if (!empty($zip)) $meta_query[] = array('key' => '_gsheet_zip', 'value' => $zip);
  if (!empty($city)) $meta_query[] = array('key' => '_buildout_city', 'value' => $city);
  if (!empty($state)) $meta_query[] = array('key' => '_gsheet_state', 'value' => $state);
  if (!empty($vented)) $meta_query[] = array('key' => '_gsheet_vented', 'value' => $vented);

  // Set up query arguments for properties
  $args_properties = array(
    'post_type' => 'properties',
    'posts_per_page' => -1,
    'meta_query' => $meta_query
  );

  // Perform the properties query
  $query_properties = new WP_Query($args_properties);
  $processed_brokers = array(); // Array to keep track of processed broker names

  if ($query_properties->have_posts()) {
    while ($query_properties->have_posts()) {
      $query_properties->the_post();
      $broker_id = get_the_ID();
      $broker_name = get_post_meta($broker_id, '_gsheet_listing_agent', true); // Fetch the listing agent meta value

      // Check if the broker name has already been added
      if (!isset($processed_brokers[$broker_name])) {
        $processed_brokers[$broker_name] = true; // Add to the list of processed brokers
      }
    }

    // Set up query arguments to fetch all brokers
    $args_all_brokers = array(
      'post_type' => 'brokers',
      'posts_per_page' => -1, // Get all brokers
    );

    // Perform the query to get all brokers
    $all_brokers_query = new WP_Query($args_all_brokers);

    // Fetch all brokers
    if ($all_brokers_query->have_posts()) {
      while ($all_brokers_query->have_posts()) {
        $all_brokers_query->the_post();
        $broker_id = get_the_ID();
        $broker_name = get_the_title();
        
        // Check if any of the conditions are not empty
        $is_disabled = !empty($use) || !empty($type) || !empty($neighborhood) || !empty($zip) || !empty($city) || !empty($state) || !empty($vented);
        
        // Set disabled attribute based on the condition
        $disabled = $is_disabled && !isset($processed_brokers[$broker_name]) ? 'disabled' : '';

        echo '<option value="' . $broker_name . '" data-uid="' . $broker_id . '" ' . $disabled . '>' . $broker_name . '</option>';
      }
    }
  } else {
    echo '<option value="">No agents found.</option>';
  }
}else {

  // Set up query arguments for properties
  $args_properties = array(
    'post_type' => 'properties',
    'posts_per_page' => -1,
  );

  // Perform the properties query
  $query_properties = new WP_Query($args_properties);
  $processed_brokers = array(); // Array to keep track of processed broker names

  if ($query_properties->have_posts()) {
    while ($query_properties->have_posts()) {
      $query_properties->the_post();
      $broker_id = get_the_ID();
      $broker_name = get_post_meta($broker_id, '_gsheet_listing_agent', true); // Fetch the listing agent meta value

      // Check if the broker name has already been added
      if (!isset($processed_brokers[$broker_name])) {
        $processed_brokers[$broker_name] = true; // Add to the list of processed brokers
      }
    }

    // Set up query arguments to fetch all brokers
    $args_all_brokers = array(
      'post_type' => 'brokers',
      'posts_per_page' => -1, // Get all brokers
    );

    // Perform the query to get all brokers
    $all_brokers_query = new WP_Query($args_all_brokers);

    // Fetch all brokers
    if ($all_brokers_query->have_posts()) {
      while ($all_brokers_query->have_posts()) {
        $all_brokers_query->the_post();
        $broker_id = get_the_ID();
        $broker_name = get_the_title();
        
        // Check if any of the conditions are not empty
        $is_disabled = !empty($use) || !empty($type) || !empty($neighborhood) || !empty($zip) || !empty($city) || !empty($state) || !empty($vented);
        
        // Set disabled attribute based on the condition
        $disabled = $is_disabled && !isset($processed_brokers[$broker_name]) ? 'disabled' : '';

        echo '<option value="' . $broker_name . '" data-uid="' . $broker_id . '">' . $broker_name . '</option>';
      }
    }
  } else {
    echo '<option value="">No agents found.</option>';
  }


}

  wp_die(); // This is required to terminate immediately and return a proper response
}



add_action('wp_ajax_get_state_dropdown', 'get_state_dropdown_handler');
add_action('wp_ajax_nopriv_get_state_dropdown', 'get_state_dropdown_handler'); // If needed for users not logged in

function get_state_dropdown_handler() {
    // Collect input values
    $use = isset($_POST['_gsheet_use']) ? $_POST['_gsheet_use'] : '';
    $type = isset($_POST['selected_type']) ? $_POST['selected_type'] : '';
    $neighborhood = isset($_POST['_gsheet_neighborhood']) ? $_POST['_gsheet_neighborhood'] : '';
    $zip = isset($_POST['_gsheet_zip']) ? $_POST['_gsheet_zip'] : '';
    $city = isset($_POST['_buildout_city']) ? $_POST['_buildout_city'] : '';
    $state = isset($_POST['_gsheet_state']) ? $_POST['_gsheet_state'] : '';
    $vented = isset($_POST['_gsheet_vented']) ? $_POST['_gsheet_vented'] : '';
    $clear = isset($_POST['clear']) ? $_POST['clear'] : '';
    // Initialize the meta query array    
    if($clear!='true' && !empty($clear)){
    $meta_query = array('relation' => 'AND');

    // Only add meta conditions if values are provided
    if (!empty($use)) $meta_query[] = array('key' => '_gsheet_use', 'value' => $use);
    if (!empty($type)) $meta_query[] = array('key' => '_gsheet_listing_type', 'value' => $type);
    if (!empty($neighborhood)) $meta_query[] = array('key' => '_gsheet_neighborhood', 'value' => $neighborhood);
    if (!empty($zip)) $meta_query[] = array('key' => '_gsheet_zip', 'value' => $zip);
    if (!empty($city)) $meta_query[] = array('key' => '_buildout_city', 'value' => $city);
    if (!empty($state)) $meta_query[] = array('key' => '_gsheet_state', 'value' => $state);
    if (!empty($vented)) $meta_query[] = array('key' => '_gsheet_vented', 'value' => $vented);

    // Set up query arguments for properties
    $args_properties = array(
        'post_type' => 'properties',
        'posts_per_page' => -1,
        'meta_query' => $meta_query
    );

    // Perform the properties query
    $query_properties = new WP_Query($args_properties);
    $processed_brokers = array(); // Array to keep track of processed broker names

    if ($query_properties->have_posts()) {
        while ($query_properties->have_posts()) {
            $query_properties->the_post();
            $broker_id = get_the_ID();
            $broker_name = get_post_meta($broker_id, '_gsheet_listing_agent', true); // Fetch the listing agent meta value

            // Check if the broker name has already been added
            if (!isset($processed_brokers[$broker_name])) {
                $processed_brokers[$broker_name] = true; // Add to the list of processed brokers
            }
        }
    }

    // Set up query arguments to fetch all brokers
    $args_all_brokers = array(
        'post_type' => 'brokers',
        'posts_per_page' => -1, // Get all brokers
    );

    // Perform the query to get all brokers
    $all_brokers_query = new WP_Query($args_all_brokers);

    // Fetch all brokers
    if ($all_brokers_query->have_posts()) {
        while ($all_brokers_query->have_posts()) {
            $all_brokers_query->the_post();
            $broker_id = get_the_ID();
            $broker_name = get_the_title();
            $disabled = (empty($use) && empty($type) && empty($neighborhood) && empty($zip) && empty($city) && empty($state) && empty($vented)) ? '' : 'disabled'; // Disable brokers not processed

            echo '<option value="' . $broker_name . '" data-uid="' . $broker_id . '" ' . $disabled . '>' . $broker_name . ' 11</option>';
        }
    } else {
        echo '<option value="">No agents found.</option>';
    }

    // Reset post data
    wp_reset_postdata();
  }else  {

      // Set up query arguments for properties
      $args_properties = array(
        'post_type' => 'properties',
        'posts_per_page' => -1,
    );

    // Perform the properties query
    $query_properties = new WP_Query($args_properties);
    $processed_brokers = array(); // Array to keep track of processed broker names

    if ($query_properties->have_posts()) {
        while ($query_properties->have_posts()) {
            $query_properties->the_post();
            $broker_id = get_the_ID();
            $broker_name = get_post_meta($broker_id, '_gsheet_listing_agent', true); // Fetch the listing agent meta value

            // Check if the broker name has already been added
            if (!isset($processed_brokers[$broker_name])) {
                $processed_brokers[$broker_name] = true; // Add to the list of processed brokers
            }
        }
    }

    // Set up query arguments to fetch all brokers
    $args_all_brokers = array(
        'post_type' => 'brokers',
        'posts_per_page' => -1, // Get all brokers
    );

    // Perform the query to get all brokers
    $all_brokers_query = new WP_Query($args_all_brokers);

    // Fetch all brokers
    if ($all_brokers_query->have_posts()) {
        while ($all_brokers_query->have_posts()) {
            $all_brokers_query->the_post();
            $broker_id = get_the_ID();
            $broker_name = get_the_title();
           
            echo '<option value="' . $broker_name . '" data-uid="' . $broker_id . '">' . $broker_name . ' 00</option>';
        }
    } else {
        echo '<option value="">No State found.</option>';
    }
  }


    // End the script execution
    wp_die();
}





/* ----------------save map laeyr data------------ */

function tristatecr_save_map_layer()
{
  // Your AJAX handling logic goes here
  $get_available_properties_id = $_POST['post_ids'];
  $get_map_title = $_POST['get_map_title'];
  $get_map_layer_title = $_POST['get_map_layer_title'];

  $final_properties_id = implode(',', $get_available_properties_id);

  echo $final_properties_id;


  $search_post = array(
    'post_title'    => $get_map_title, // Change the title as needed
    'post_type'     => 'properties_search',
    'author'         => get_current_user_id(),
    'post_status'   => 'publish', // You can change this to 'draft' if you want to save it as a draft
  );

  // Set up the query arguments
  $args = array(
    'post_type'      => 'properties_search', // Change this to your post type if it's different
    'author'         => get_current_user_id(),
    'posts_per_page' => -1, // Retrieve all posts by the user
    'post_status'    => array('publish', 'pending', 'draft', 'future', 'private'), // Include all post statuses
  );

  $user_posts_query = new WP_Query($args);
  $user_post_ids = $user_posts_query->posts;
  $user_exists_post_id = $user_post_ids[0]->ID;

   

  //var_dump($search_post);
  // Insert the post into the database
  // if (!$user_exists_post_id) {
  //   $post_id = wp_insert_post($search_post);
  //   update_post_meta($post_id, '_tristate_map_layer_title', $get_map_layer_title);
  //   update_post_meta($post_id, '_tristate_map_layer_properties_id', $get_available_properties_id);
  // } else {
  //   $get_user_previous_data = get_post_meta($user_exists_post_id, '_tristate_map_layer_title', true);

  //   update_post_meta($user_exists_post_id, '_tristate_map_layer_title', $get_map_layer_title);
  //   update_post_meta($user_exists_post_id, '_tristate_map_layer_properties_id', $get_available_properties_id);
  // }


  if (!$user_exists_post_id) {
    $post_id = wp_insert_post($search_post);
    update_post_meta($post_id, 'layer_name', $get_map_layer_title);
    update_post_meta($post_id, 'listing_ids', $final_properties_id);
  } else {
    //$get_user_previous_data = get_post_meta($user_exists_post_id, '_tristate_map_layer_title', true);

    update_post_meta($user_exists_post_id, 'layer_name', $get_map_layer_title);
    update_post_meta($user_exists_post_id, 'listing_ids', $final_properties_id);
  }



  die();
  // echo 'aja x runing';
}

// Hook the AJAX function to the appropriate WordPress action
add_action('wp_ajax_tristatecr_save_map_layer', 'tristatecr_save_map_layer');
add_action('wp_ajax_nopriv_tristatecr_save_map_layer', 'tristatecr_save_map_layer');




add_action('wp_ajax_tristate_select2_dropdown_update', 'tristate_select2_dropdown_update');
add_action('wp_ajax_nopriv_tristate_select2_dropdown_update', 'tristate_select2_dropdown_update');
function tristate_select2_dropdown_update(){
  
  $selected_data = array(
    'broker_ids' => isset($_POST['agents']) && !empty($_POST['agents']) ? $_POST['agents'] : array(),
    'use' => isset($_POST['uses']) && !empty($_POST['uses']) ? $_POST['uses'] : array(),
    'neighbourhoods' => isset($_POST['neighbourhoods']) && !empty($_POST['neighbourhoods']) ? $_POST['neighbourhoods'] : array(),
    'zip' => isset($_POST['zips']) && !empty($_POST['zips']) ? $_POST['zips'] : array(),
    'city' => isset($_POST['cities']) && !empty($_POST['cities']) ? $_POST['cities'] : array(),
    'state' => isset($_POST['states']) && !empty($_POST['states']) ? $_POST['states'] : array(),
    'vented' => isset($_POST['venteds']) && !empty($_POST['venteds']) ? $_POST['venteds'] : array(),
    'dropdownToUpdate' => isset($_POST['dropdownToUpdate']) && !empty($_POST['dropdownToUpdate']) ? $_POST['dropdownToUpdate'] : ''
);




$options= '';
  switch($selected_data['dropdownToUpdate']){
  
    case "tri_agents":
        
        echo get_select2_dropdown($selected_data,'_gsheet_listing_agent');
        
    break;
    
    case "_gsheet_use":
        
      echo get_select2_dropdown($selected_data,'_gsheet_use');
        
    break;
    
    case "_gsheet_neighborhood":
    
      echo get_select2_dropdown($selected_data,'_gsheet_neighborhood');
      
    break;
    
    case "_gsheet_zip":
    
      echo get_select2_dropdown($selected_data,'_gsheet_zip');
    break;
    
    case "_buildout_city":
      echo get_select2_dropdown($selected_data,'_buildout_city');
    break;
    
    case "_gsheet_state":
      echo get_select2_dropdown($selected_data,'_gsheet_state');
    break;
    
    default : 
      echo 'Nothing';
  
  }
  
 
  
  wp_die();


}


function get_select2_dropdown($selected_data , $meta_key){

 
   global $wpdb;
   $table_name = $wpdb->prefix . 'postmeta';
   
   $query = "SELECT DISTINCT meta_value FROM $table_name WHERE meta_key = %s";
   $params = array($meta_key);

   if(!empty($selected_data['broker_ids'])){
   
      $broker_values = is_array($selected_data['broker_ids']) ? "'" . implode("','", $selected_data['broker_ids']) . "'" : "'" . $selected_data['broker_ids'] . "'";
      $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_listing_agent' 
          AND meta_value IN ($broker_values)
      )";
    
   }

   if(!empty($selected_data['use'])){
   
    $use_values = is_array($selected_data['use']) ? "'" . implode("','", $selected_data['use']) . "'" : "'" . $selected_data['use'] . "'";
    
    $query .= " AND post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_use' 
        AND meta_value IN ($use_values)
    )";
   
   }
   if(!empty($selected_data['neighbourhoods'])){
    $neb_values = is_array($selected_data['neighbourhoods']) ? "'" . implode("','", $selected_data['neighbourhoods']) . "'" : "'" . $selected_data['neighbourhoods'] . "'";
    $query .= " AND post_id IN (
      SELECT post_id 
      FROM $table_name 
      WHERE meta_key = '_gsheet_neighborhood' 
      AND meta_value IN ($neb_values)
    )";
   }
   if(!empty($selected_data['zip'])){
   
      $zip_values = is_array($selected_data['zip']) ? "'" . implode("','", $selected_data['zip']) . "'" : "'" . $selected_data['zip'] . "'";
      
      $query .= " AND post_id IN (
        SELECT post_id 
        FROM $table_name 
        WHERE meta_key = '_gsheet_zip' 
        AND meta_value IN ($zip_values)
      )";
      
   }
   if(!empty($selected_data['city'])){
    $city_values = is_array($selected_data['city']) ? "'" . implode("','", $selected_data['city']) . "'" : "'" . $selected_data['city'] . "'";
      $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_buildout_city' 
          AND meta_value IN ($city_values)
      )";
   }
   if(!empty($selected_data['state'])){
          $state_values = is_array($selected_data['state']) ? "'" . implode("','", $selected_data['state']) . "'" : "'" . $selected_data['state'] . "'";
          $query .= " AND post_id IN (
          SELECT post_id 
          FROM $table_name 
          WHERE meta_key = '_gsheet_state' 
          AND meta_value IN ($state_values)
      )";
   }
   
   // Preparing the query
   $query = $wpdb->prepare($query, $params);

   // Fetching results from the database
   $original_results = $wpdb->get_results($query);  //test
 
   // Array to store the values obtained from the first query
   $matching_vals = array();
 
   // Storing the values obtained from the first query into the array
   if ($original_results) {
       foreach ($original_results as $result) {
           $matching_vals[] = $result->meta_value;
       }
   }
   
   
   // Fetching all results from the database
   $query_all = $wpdb->prepare("SELECT DISTINCT meta_value FROM $table_name WHERE meta_key = %s", $meta_key);
   $results_all = $wpdb->get_results($query_all);
   $html = '';
     // Processing and displaying the results
      if ($results_all) {
        foreach ($results_all as $result) {  //tes
            $zip = $result->meta_value;
            // Check if $zip is found in $matched_zip array
            $is_matched = in_array($zip, $matching_vals);
            // If $selected_broker_ids is empty, do not disable the option
            // Otherwise, disable the option if $zip is not found in $matched_zip array
            $disabled = $is_matched ? '' : 'disabled';
            $html.= '<option value="' . $zip . '" data-zip="' . $zip . '" ' . $disabled . '>' . $zip . '</option>';
        }
      } else {
          $html.= '<option>Not Found</option>';
      }
    return $html;
}


function select2_ajax_handler() {
  // Your AJAX request handling logic goes here
  // Example: fetching data from the database or an external API
  // Then, format the data into JSON and return it

  // Example response
  $data = array(
      array('id' => 1, 'text' => 'Option 1'),
      array('id' => 2, 'text' => 'Option 2'),
      // Add more options as needed
  );

  // Send JSON response
  wp_send_json($data);
}
add_action('wp_ajax_select2_ajax_action', 'select2_ajax_handler'); // For logged-in users
add_action('wp_ajax_nopriv_select2_ajax_action', 'select2_ajax_handler');