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


function get_property_broker_title($property_id)
{

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
function meta_of_api_sheet($propid, $metaKey)
{

  $buildout_meta = get_post_meta($propid, '_buildout_' . $metaKey, true);
  $g_sheet_meta = get_post_meta($propid, '_gsheet_' . $metaKey, true);
  
  return !empty($buildout_meta) ? $buildout_meta : (!empty($g_sheet_meta) ? $g_sheet_meta : '');
}


function tristate_get_marker_data($ID)
{

  $sale_marker = TRISTATECRLISTING_PLUGIN_URL . '/assets/img/sale.png';
  $lease_marker = TRISTATECRLISTING_PLUGIN_URL . '/assets/img/lease.png';
  $title = meta_of_api_sheet($ID, 'sale_listing_web_title');
  $buildout_lease = meta_of_api_sheet($ID, 'lease');
  $buildout_sale = meta_of_api_sheet($ID, 'sale');
  $streets = meta_of_api_sheet($ID, 'cross_street');
  $state = meta_of_api_sheet($ID, 'state');
  $zip = meta_of_api_sheet($ID, 'zip');
  $city = meta_of_api_sheet($ID, 'city');
  $subtitle = implode(', ', array_filter(array($streets, $city, $state, $zip), 'strlen'));
  $address = meta_of_api_sheet($ID, 'address');
  $county = meta_of_api_sheet($ID, 'county');
  $country_code = meta_of_api_sheet($ID, 'country_code');
  $address_c = implode(', ', array_filter(array($county, $country_code,), 'strlen'));
  $image = false;
  if ($photos = get_post_meta($ID, '_buildout_photos', true)) {
    $photo = reset($photos);
    $image = $photo->formats->thumb ?? '';
  }

  $marker_img = ($buildout_lease == '1' && $buildout_sale == '1') ? $lease_marker : (($buildout_lease == '1') ? $lease_marker : (($buildout_sale == '1') ? $sale_marker : false));



  $type = ($buildout_lease == '1' && $buildout_sale == '1') ? 'FOR LEASE' : (($buildout_lease == '1') ? 'FOR LEASE' : (($buildout_sale == '1') ? 'FOR SALE' : false));

  if ($buildout_lease == '1' && $buildout_sale == '1') {
    $selected_array = isset($_POST['selected_type']) ? $_POST['selected_type'] : array();
    $selected_string = implode(', ', $selected_array);
    if (!empty($selected_array)) {
      if ($selected_string == 'for Lease') {
        $marker_img = $lease_marker;
        $type = 'FOR LEASE';
      }
      if ($selected_string == 'for Sale') {
        $marker_img = $sale_marker;
        $type = 'FOR SALE';
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


function get_pricesf_minmax($type = "min", $formatted = true)
{
  global $wpdb;

  $max_rent = $wpdb->get_var("
      SELECT MAX(CAST(REPLACE(REPLACE(pm.meta_value, '$', ''), ',', '') AS UNSIGNED)) 
      FROM {$wpdb->postmeta} pm
      INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
      WHERE pm.meta_key = '_gsheet_price_sf' 
      AND p.post_type = 'properties'
  ");


  $formatted_max_val = number_format($max_rent);
  $formatted_min_val = '$0';

  if ($formatted) {

    $retval = $type == 'min' ? $formatted_min_val : '$' . $formatted_max_val;
  } else {

    $retval = $type == 'min' ? (int) 0 : (int) $max_rent;
  }

  return $retval;
}


function get_mnth_rent_min_max($type = "min", $formatted = true){
  global $wpdb;

  $max_rent = $wpdb->get_var("
      SELECT MAX(CAST(REPLACE(REPLACE(pm.meta_value, '$', ''), ',', '') AS UNSIGNED)) 
      FROM {$wpdb->postmeta} pm
      INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
      WHERE pm.meta_key = '_gsheet_monthly_rent' 
      AND p.post_type = 'properties'
  ");


  $formatted_max_val = number_format($max_rent);
  $formatted_min_val = '$0';

  if ($formatted) {

    $retval = $type == 'min' ? $formatted_min_val : '$' . $formatted_max_val;
  } else {

    $retval = $type == 'min' ? (int) 0 : (int) $max_rent;
  }

  return $retval;
}

function get_size_minmax($type = "min", $formatted = true)
{
  global $wpdb;

  $max_size = $wpdb->get_var("
  SELECT MAX(CAST(pm.meta_value AS UNSIGNED)) 
  FROM $wpdb->postmeta pm
  INNER JOIN $wpdb->posts p ON pm.post_id = p.ID
  WHERE pm.meta_key = '_gsheet__max_size_fm'
  AND p.post_type = 'properties'
");


  $formatted_max_val = number_format($max_size);
  $formatted_min_val = '0 SF';

  if ($formatted) {

    $retval = $type == 'min' ? $formatted_min_val :  $formatted_max_val . ' SF';
  } else {

    $retval = $type == 'min' ? (int) 0 : (int) $max_size;
  }

  return $retval;
}


// for getting price 
function get_price_minmax($type = "min", $formatted = true)
{
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

  if ($formatted) {

    $retval = $type == 'min' ? $formatted_min_price : '$' . $formatted_max_price;
  } else {

    $retval = $type == 'min' ? (int) 0 : (int) $max_price;
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

//add_shortcode('drt', 'drt_shortcode');

function drt_shortcode($_atts)
{
  // Start output buffering
  $defaults = array(
    'state' => ''
  );

  $atts = shortcode_atts($defaults, $_atts);

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

 



           // Find all elements with the class 'lisiitng__title'
           var listingTitles = document.querySelectorAll('.ts-state-page .lisiitng__state_title');

// Loop through each element and truncate the text
listingTitles.forEach(function(title) {
  var originalText = title.textContent;
  var truncatedText = originalText.length > 30 ? originalText.substring(0, 30) + ' ...' : originalText;
  title.textContent = truncatedText;
});

      // Initially hide both divs
      document.getElementById('for_sale').style.display = 'none';
      document.getElementById('for_lease').style.display = 'none';
      document.getElementById('sale_lease').style.display = 'none';
      document.getElementById('for_lease_monthly_rent').style.display = 'none';
    //  document.getElementById('for_lease_monthly_rent').style.display = 'none';
      //sale_lease

      // Function to update visibility based on checkbox states
      function updateVisibility() {
        var forSaleCheckbox = document.getElementById('type_for_sale');
        var forLeaseCheckbox = document.getElementById('type_for_lease');

        if (forSaleCheckbox.checked) {
          document.getElementById('sale_lease').style.display = 'block';
          document.getElementById('for_sale').style.display = 'block';
          document.getElementById('for_lease_monthly_rent').style.display = 'block';
        } else {
          document.getElementById('for_sale').style.display = 'none';
         
          
        }

        if (forLeaseCheckbox.checked) {
          document.getElementById('sale_lease').style.display = 'block';
          document.getElementById('for_lease').style.display = 'block';
          document.getElementById('for_lease_monthly_rent').style.display = 'block';
        } else {
          document.getElementById('for_lease').style.display = 'none';
          document.getElementById('for_lease_monthly_rent').style.display = 'none';
        }
/* 
        if (!forSaleCheckbox.checked && !forLeaseCheckbox.checked) {
          document.getElementById('sale_lease').style.display = 'none';
        } */
      }

      // Attach the event listeners to checkboxes
      document.getElementById('type_for_sale').addEventListener('change', updateVisibility);
      document.getElementById('type_for_lease').addEventListener('change', updateVisibility);

      // Call once on page load
      updateVisibility();
    });

    jQuery(document).ready(function($) {

      $('#_gsheet_listing_type input[type="checkbox"]').trigger('click');



    /*   document.getElementById("filter-clear11").addEventListener("click", function() {
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
  

        var ranges = ['#price-range', '#price-range3', '#price-range2'];
          ranges.forEach(function(range) {
              var $range = $(range);
              $range.slider("option", "max", $range.data('max'));
              $range.slider("option", "min", $range.data('min'));
              $range.slider("values", [$range.data('min'), $range.data('max')]);
          });

          // resetting inputs
          $('.range-inputs').each(function() {
            $(this).val($(this).attr('data-default'));
          });
        $("#search-by-text-new").val("");
      }); */

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

      /*  ---------------------Save map layer------------- */

      jQuery("#submit_map_layer").on("click", function(e) {
        e.preventDefault();

        var search_id = $('#previous_map_post_id').val();
        var user_id = $('#map_layer_user_id').val();
        var timestamp = $('#map_layer_timestamp').val();
        var get_map_title = $('#map_post_title').val();
        var get_map_layer_title = $('#map_layer_title').val();
        var viewSearch = $('#layers-link-buttonp');
        var custommap = $('#layers-link-button');
        var get_filter_poist_id = [];
        var form = $('#tri-popup-form');
        var closebutton = $("#tcr-popup-close-button");
        $('input[name="get_properties_id"]').each(function() {

          var parent = $(this).parent('.propertylisting-content:visible');
          if (parent.length > 0) {
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
              action: 'new_tristate_save_results_as_layer',
              search_id: search_id,
              user_id: user_id,
              timestamp: timestamp,
              get_map_title: get_map_title,
              layer_name: get_map_layer_title,
              listing_ids: final_listing_ids,
              page_id: '<?php echo get_the_id(); ?>',
              
            },

            success: function(response) {
              $('#map_layer_show_message').text(response.data.message);
              $('#map-layer-content').css('display', 'none');

              sessionStorage.setItem('latest_search_link', response.data.recent_link);
              if(viewSearch.css('display') !== 'block'){
                viewSearch.css('display', 'block');
              }
              
              custommap.attr('href', response.data.recent_link);

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




  <div class="filter-wrapper <?php echo !empty($atts['state']) ? 'ts-state-page': ''; ?>" id="filter-wrapper"  <?php if (!empty($atts['state'])) : ?>
         data-current_state="<?php echo strtoupper($atts['state']); ?>"
     <?php endif; ?>>
    <div class="MuiBox-root">
      <div class="left-content">
        <div class="Filterform">
          <div class="MuiBox-root">
            <div id="select-container">
            <?php 
          if(!empty($atts['state'])) {
          ?>
          <div class="search-by-text-new state-page-keyword">
       <label for="search-by-text-new">Search</label>
            <input class="MuiInputBase-input" aria-invalid="false" id="search-by-text-new" placeholder="Search by address,city,state, or zip" type="text">
          </div>
          <?php } ?>
              <!-- Dynamically created select elements will be placed here -->
            </div>


            <div>



              <div id="dropdown_lisiting_type">
              <div class="tristate_cr_d-flex checkbox-wrapper" id="_gsheet_listing_type">
                <div>
                  <label for="for Sale">For Sale</label>
                  <input type="checkbox" name="listing_type" value="for Sale" id="type_for_sale">
                </div>
                <div>
                  <label for="for Lease">For Lease</label>
                  <input type="checkbox" name="listing_type" value="ma for Lease" id="type_for_lease">
                </div>
              </div>
                <?php //echo drt_get_checkboxes_for_types('_gsheet_listing_type'); ?>
              </div>



            </div>

            <div id="sale_lease">
              <div>
                <div class="slider-box" id="for_sale">
                  <label for="priceRange">Price :</label>
                  <input style="display:none" type="text" id="priceRange" readonly>
                  <div class="range-min-max">
                    <input type="text" class="range-inputs" id="price-range-min" data-default="<?php echo get_price_minmax(); ?>" name="price_range_min" value="<?php echo get_price_minmax(); ?>">
                    <input type="text" class="range-inputs" id="price-range-max" data-default="<?php echo get_price_minmax('max'); ?>" name="price_range_max" value="<?php echo get_price_minmax('max'); ?>">
                  </div>
                  <div id="price-range" class="slider" data-min="<?php echo get_price_minmax('min', false) ?>" data-max="<?php echo get_price_minmax('max', false); ?>"></div>
                  <input type="hidden" name="price-range" data-live="0" data-clear="0" id="price-range-selected">
                </div>

              </div>
              <!-- For Rent -->

              <div>
                <div class="slider-box" id="for_lease">
                  <label for="priceRange">Price per SF:</label>
                  <input style="display:none" type="text" id="priceRange3" readonly>
                  <div class="range-min-max">
                    <input type="text" class="range-inputs" id="rent-range-min" data-default="<?php echo get_pricesf_minmax(); ?>" name="range_min_rent" value="<?php echo get_pricesf_minmax(); ?>">
                    <input type="text" class="range-inputs" id="rent-range-max" data-default="<?php echo get_pricesf_minmax('max'); ?>" name="range_max_rent" value="<?php echo get_pricesf_minmax('max'); ?>">
                  </div>
                  <div id="price-range3" class="slider" data-min="<?php echo get_pricesf_minmax('min', false) ?>" data-max="<?php echo get_pricesf_minmax('max', false); ?>"></div>
                  <input type="hidden" name="rent-range" data-clear="0" id="rent-range-selected">
                </div>
              </div>
              
              <!-- For monthly Rent -->
              <div>
                <div class="slider-box" id="for_lease_monthly_rent">
                  <label for="priceRange">Monthly Rent:</label>
                  <input style="display:none" type="text" id="priceRange4" readonly>
                  <div class="range-min-max">
                    <input type="text" class="range-inputs" id="month-rent-range-min" data-default="<?php echo get_mnth_rent_min_max(); ?>" name="month_range_min_rent" value="<?php echo get_mnth_rent_min_max(); ?>">
                    <input type="text" class="range-inputs" id="month-rent-range-max" data-default="<?php echo get_mnth_rent_min_max('max'); ?>" name="month_range_max_rent" value="<?php echo get_mnth_rent_min_max('max'); ?>">
                  </div>
                  <div id="price-range4" class="slider" data-min="<?php echo get_mnth_rent_min_max('min', false) ?>" data-max="<?php echo get_mnth_rent_min_max('max', false); ?>"></div>
                  <!-- <input type="hidden" name="rent-range" data-clear="0" id="rent-range-selected"> -->
                </div>
              </div>
              <!-- Monthly rent ends -->

              
            </div>

            <div>
              <div class="slider-box">
                <label for="priceRange">Size:</label>
                <input style="display:none" type="text" id="priceRange2" readonly>

                <div class="range-min-max">
                  <input type="text" class="range-inputs" id="size-range-min" data-default="<?php echo get_size_minmax(); ?>" name="size_range_min" value="<?php echo get_size_minmax(); ?>">
                  <input type="text" class="range-inputs" id="size-range-max" data-default="<?php echo get_size_minmax('max'); ?>" name="size_range_max" value="<?php echo get_size_minmax('max'); ?>">
                </div>
                <div id="price-range2" class="slider" data-min="<?php echo get_size_minmax('min', false) ?>" data-max="<?php echo get_size_minmax('max', false); ?>"></div>
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
                        <form id="tri-popup-form" method="POST">
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
                  <?php if(!empty($_atts['state'])) : ?>
                  <div id="more-filter-content"></div>
                  <button id="state-more-filter" type="button" class="MuiButtonBase-root MuiButton-root MuiButton-contained MuiButton-containedPrimary MuiButton-sizeMedium MuiButton-containedSizeMedium MuiButton-colorPrimary MuiButton-root MuiButton-contained MuiButton-containedPrimary MuiButton-sizeMedium MuiButton-containedSizeMedium MuiButton-colorPrimary bg-yellow css-1hw9j7s color-white"> More Filters <span class="MuiTouchRipple-root css-w0pj6f"></span>
                  </button>
                  <?php endif; ?>
                  <button id="filter-clear11" tabindex="0" type="button" class="MuiButtonBase-root MuiButton-root MuiButton-contained MuiButton-containedPrimary MuiButton-sizeMedium MuiButton-containedSizeMedium MuiButton-colorPrimary MuiButton-root MuiButton-contained MuiButton-containedPrimary MuiButton-sizeMedium MuiButton-containedSizeMedium MuiButton-colorPrimary bg-yellow css-1hw9j7s color-white"> Clear Filter <span class="MuiTouchRipple-root css-w0pj6f"></span>
                  </button>

                  <?php

                  // if (isset($_GET['search_id'])) {
                  //   $get_search_id =  $_GET['search_id'];
                  //   $search_permalink = get_the_permalink($get_search_id);
                  //   $search_permalink = add_query_arg(['redirectId' => get_the_id()], $search_permalink);
                    
                  //   echo '<a class="button" href="' . $search_permalink . '" target="_blank" rel="noopener noreferrer">View Custom Map</a>';

                  // }
                  ?>
                <p style="display: none;" id="layers-link-buttonp">
                  <a class="button" id="layers-link-button" href="#" target="_blank">View Custom Map</a>
                </p>
               
                <script>
                  if (sessionStorage.getItem('latest_search_link')) {
               
                    var layersLinkButton = document.getElementById('layers-link-button');
                    var layersLinkbuttonp =  document.getElementById('layers-link-buttonp');

                    layersLinkButton.setAttribute('href', sessionStorage.getItem('latest_search_link'));

                  } else {

                    document.getElementById('layers-link-buttonp').style.display = 'none';
                  }
                  
                  var url = new URL(window.location.href);

                  // Get the URL parameters
                  var params = new URLSearchParams(url.search);
                  
                  // Get the value of the "search_id" parameter
                  var searchId = params.get('search_id');
                  
                  if(searchId){
                    document.getElementById('layers-link-buttonp').style.display='block';
                  }
                  
                  
                </script>
               
                </div>
              </div>



              <div class="MuiBox-root css-69324s">



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

        // if (!empty($atts['state'])) {

        //   $args['meta_query'][] = array(
        //     'relation' => 'OR',
        //     array(
        //       'key'     => '_buildout_state',
        //       'value'   => esc_attr($atts['state']),
        //       'compare' => '=',
        //     ),
        //     array(
        //       'key'     => '_gsheet_state',
        //       'value'   => esc_attr($atts['state']),
        //       'compare' => '=',

        //     ),
        //   );
        // }
        $search_query = new WP_Query($args);
        $default_found_results = $search_query->found_posts;
        ?>
        <div id="menu-btn"><i class="fa fa-angle-left"></i></div>
        <div class="right-map">
          <!-- <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d407542.86304287874!2d-74.32724652492182!3d40.69942908913206!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!z4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2snp!4v1711702301417!5m2!1sen!2snp" allowfullscreen="allowFullScreen" width="100%" height="450px" style="position: relative; display: block;"></iframe> -->
          <div id="tristate-map" style="height:600px; width:100%;position:relative;display:block;"></div>
        </div>
        <div id="search_count_area" >
          <!-- <div class="search-by-text MuiFormControl-root MuiTextField-root css-i44wyl">
            <input class="MuiInputBase-input MuiOutlinedInput-input css-1x5jdmq" aria-invalid="false" id="search-by-text" placeholder="search by keyword" type="text">
          </div> -->
          <?php 
          if(empty($atts['state'])) {
          ?>
          <div class="search-by-text-new MuiFormControl-root MuiTextField-root css-i44wyl">
       
            <input class="MuiInputBase-input MuiOutlinedInput-input css-1x5jdmq" aria-invalid="false" id="search-by-text-new" placeholder="search by keyword" type="text">
          </div>
          <?php } ?>

          <!-- <div class="MuiFormControl-root MuiTextField-root css-i44wyl">
            <input aria-invalid="false" id="tristate-input" placeholder="search by keyword old" type="text" class="MuiInputBase-input MuiOutlinedInput-input css-1x5jdmq">
          </div> -->
          <div class="column-select-result-count">
            <div id="tristate-result-count" data-count="<?php echo __total(); ?>">
              <?php echo 'Showing ' . $default_found_results . ' of ' .$default_found_results . ' Listings' 
              ?>

            </div>
            <?php if(!empty($_atts['state'])) : ?>
              <select id="state-sorting">
                <option value="date-updated">Date Updated</option>
                <option value="alphabetical-a-z">Alphabetical (A-Z)</option>
                <option value="alphabetical-z-a">Alphabetical (Z-A)</option>
                <option value="price-highest-lowest">Price (Highest to Lowest)</option>
                <option value="price-lowest-highest">Price (Lowest to Highest)</option>
                <option value="size-highest-lowest">Size (Highest to Lowest)</option>
                <option value="size-lowest-highest">Size (Lowest to Highest)</option>
                <option value="date-created-old-new">Date Created (Old to New)</option>
                <option value="date-created-new-old">Date Created (New to Old)</option>
              </select>
            <?php endif; ?>
            <div class="tristate-column-select">
              <select name="" id="selectcolumn">
                <option value="1">One Column</option>
                <option value="2">Two Columns</option>
                <option value="3" selected>Three Columns</option>
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
                $check_state = !empty($atts['state']) ? true : false;
                while ($search_query->have_posts()) {
                  $search_query->the_post();
                  $ID = get_the_id();
                  if (file_exists($loop)) {
                    // echo !empty($atts['state']) ? '<a href="'.get_the_permalink($ID).'">' :'';
                    load_template($loop, false, ['ID' => $ID, 'ajax' => true,'state'=>$check_state]);
                    // echo !empty($atts['state']) ? '</a>' :'';
                  }
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
    jQuery(document).ready(function($) {
    
      $('#state-sorting').change(function() {
        var sortingType = $(this).val();
        var $propertyListing = $('#propertylisting-content');
        var $properties = $propertyListing.find('.propertylisting-content:visible');

        var sortedProperties = $properties.sort(function(a, b) {
            var aValue, bValue;

            switch (sortingType) {
                case 'date-created-old-new':
                  aValue = $(a).data('datecreated');
                  bValue = $(b).data('datecreated');
                  return aValue - bValue;
                case 'date-created-new-old':
                    aValue = $(a).data('datecreated');
                    bValue = $(b).data('datecreated');
                    return bValue - aValue;
                case 'date-updated':
                    aValue = $(a).data('dateupdated');
                    bValue = $(b).data('dateupdated');
                    return bValue - aValue;
                case 'alphabetical-a-z':
                    aValue = $(a).data('title').toLowerCase();
                    bValue = $(b).data('title').toLowerCase();
                    return aValue.localeCompare(bValue);
                case 'alphabetical-z-a':
                    aValue = $(a).data('title').toLowerCase();
                    bValue = $(b).data('title').toLowerCase();
                    return bValue.localeCompare(aValue);
                case 'price-highest-lowest':
                    aValue = $(a).data('price');
                    bValue = $(b).data('price');
                    return bValue - aValue;
                case 'price-lowest-highest':
                    aValue = $(a).data('price');
                    bValue = $(b).data('price');
                    return aValue - bValue;
                case 'size-highest-lowest':
                    aValue = $(a).data('maxsize');
                    bValue = $(b).data('maxsize');
                    return bValue - aValue;
                case 'size-lowest-highest':
                    aValue = $(a).data('maxsize');
                    bValue = $(b).data('maxsize');
                    return aValue - bValue;
                default:
                    return 0;
            }
        });

        $propertyListing.html(sortedProperties);
    });
    
        // Bind event listeners to the select2 elements and search input
        $("#select2_agents, #select2_uses, #select2_neighborhoods, #select2_zipcodes, #select2_cities, #select2_states, #select2_vented, #search-by-text-new").on('select2:unselect input', function(e) {
          checkAndResetFilters();
          
        });

        // Function to check if any value is selected
        function checkAndResetFilters() {
          let isAnySelected = false;

          $("#select2_agents, #select2_uses, #select2_neighborhoods, #select2_zipcodes, #select2_cities, #select2_states, #select2_vented").each(function() {
            if ($(this).val() && $(this).val().length > 0) {
              isAnySelected = true;
              return false; // Exit the loop
            }
          });

          // Check the search input value
          if (!isAnySelected && $("#search-by-text-new").val().trim() === "") {
            resetRangeFilters();
          }
        }

        // Function to reset range filters
        function resetRangeFilters() {
         
          var ranges = ['#price-range', '#price-range3', '#price-range2','#price-range4'];
          ranges.forEach(function(range) {
              var $range = $(range);
              $range.slider("option", "max", $range.data('max'));
              $range.slider("option", "min", $range.data('min'));
              $range.slider("values", [$range.data('min'), $range.data('max')]);
          });

          // resetting inputs
          $('.range-inputs').each(function() {
            $(this).val($(this).attr('data-default'));
          });
       
        }

        $("#select2_agents, #select2_uses, #select2_neighborhoods, #select2_zipcodes, #select2_cities, #select2_states, #select2_vented").on('select2:opening', function(e) {
          filterListings();
        });
        var maxVal = $("#price-range").data('max');
        var stepVal = maxVal / 20;

      // price range
      $("#price-range").slider({
        range: true,
        min: $("#price-range").data('min'), //get min val
        max: $("#price-range").data('max'), //get max val
        values: [$("#price-range").data('min'), $("#price-range").data('max')], //postion slider val
        step: stepVal,
        slide: function(event, ui) {
          $("#priceRange").val("$" + ui.values[0] + " - $" + ui.values[1]);
          $("#price-range-min").val('$' + ui.values[0].toLocaleString());
          $("#price-range-max").val('$' + ui.values[1].toLocaleString());

        },
        change: function(event, ui) {
          $("#price-range-selected").val(ui.values[0] + "-" + ui.values[1]);


        },

      });

      var maxRange2 = $("#price-range2").data('max');
        var stepRange2Val = maxRange2 / 20;
      $("#price-range2").slider({
        range: true,
        min: $("#price-range2").data('min'), //get min val
        max: $("#price-range2").data('max'), //get max val  
        values: [$("#price-range2").data('min'), $("#price-range2").data('max')], //postion slider val
        step: stepRange2Val,
        slide: function(event, ui) {

          $("#priceRange2").val(
            "" + ui.values[0].toLocaleString() + " SF to " + ui.values[1].toLocaleString() + " SF "
          );
          $("#size-range-min").val(ui.values[0].toLocaleString() + ' SF');
          $("#size-range-max").val(ui.values[1].toLocaleString() + " SF");
        },
        change: function(event, ui) {

          $("#size-range-selected").val(ui.values[0] + "-" + ui.values[1]);


        },
      });

      var maxRange3 = $("#price-range3").data('max');
        var stepRange3Val = maxRange3 / 20;
      $("#price-range3").slider({
        range: true,
        min: $("#price-range3").data('min'), //get min val
        max: $("#price-range3").data('max'), //get max val  
        values: [$("#price-range3").data('min'), $("#price-range3").data('max')],
        step: stepRange3Val,
        slide: function(event, ui) {
          $("#priceRange3").val("$" + ui.values[0].toLocaleString() + " - $" + ui.values[1].toLocaleString());
          $("#rent-range-min").val("$" + ui.values[0].toLocaleString());
          $("#rent-range-max").val("$" + ui.values[1].toLocaleString());
        },
        change: function(event, ui) {
          $("#rent-range-selected").val(ui.values[0] + "-" + ui.values[1]);

        },
      });
      //price-range4
      var maxRange4 = $("#price-range4").data('max');
        var stepRange4Val = maxRange4 / 20;
      $("#price-range4").slider({
        range: true,
        min: $("#price-range4").data('min'), //get min val
        max: $("#price-range4").data('max'), //get max val  
        values: [$("#price-range4").data('min'), $("#price-range4").data('max')],
        step: stepRange4Val,
        slide: function(event, ui) {
          $("#priceRange4").val("$" + ui.values[0].toLocaleString() + " - $" + ui.values[1].toLocaleString());
          $("#month-rent-range-min").val("$" + ui.values[0].toLocaleString());
          $("#month-rent-range-max").val("$" + ui.values[1].toLocaleString());
          
          //month-rent-range-min ,month-rent-range-max
        },
        change: function(event, ui) {
          // $("#rent-range-selected").val(ui.values[0] + "-" + ui.values[1]);
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
 /*      function createSelect2Options(data) {
        var options = Array.from(data).sort().map(function(value) {
          return {
            id: value,
            text: value
          };
        });
        return options;
      } */

/* display agents from comma seprated to single option start */
function createSelect2Options(data) {
    var options = [];

    // Iterate through each element in the data array
    Array.from(data).forEach(function(value) {
        // Split the value by comma and trim each part
        var parts = value.split(' ,').map(function(part) { return part.trim(); });

        // Iterate through each part to create options
        parts.forEach(function(part) {
            // Check if the option already exists
            var exists = options.some(function(opt) {
                return opt.id === part;
            });

            // Add the option if it doesn't exist
            if (!exists) {
                options.push({
                    id: part,
                    text: part
                });
            }
        });
    });

    // Sort options by id (assuming id is the value to sort by)
    options.sort(function(a, b) {
        return a.id.localeCompare(b.id);
    });

    return options;
}
/* display agents from comma seprated to single option end */


      var selectOptions;
var tsStatePageDiv = document.querySelector('.ts-state-page');

if (tsStatePageDiv) {
    selectOptions = {
      cities: createSelect2Options(cities),
        uses: createSelect2Options(uses),
        states: createSelect2Options(states),
        // neighborhoods: createSelect2Options(neighborhoods),
        // zipcodes: createSelect2Options(zipcodes),
        
       
    };
} else {
    selectOptions = {
        agents: createSelect2Options(agents),
        uses: createSelect2Options(uses),
        neighborhoods: createSelect2Options(neighborhoods),
        zipcodes: createSelect2Options(zipcodes),
        cities: createSelect2Options(cities),
        states: createSelect2Options(states),
        vented: createSelect2Options(vented)
    };
}

      $.each(selectOptions, function(key, options) {
    // Create a new container div
    var containerDiv = $('<div>', {
        id: 'container_' + key
    }).appendTo('#select-container');
    
    // Add label element
    $('<label>', {
        for: 'select2_' + key,
        text: key.charAt(0).toUpperCase() + key.slice(1) + ': '
    }).appendTo(containerDiv);

    // Add select2 element
    $('<select>', {
        id: 'select2_' + key,
        name: 'select2_' + key + '[]',
        multiple: true
    }).appendTo(containerDiv).select2({
        data: options,
        placeholder: ''
    }).on('change', function(e) {
        if (e.type === 'select2:select') {
            $(this).select2("close");
        }
        filterListings(key);
    }).on('change:select2', function(e) {
        updateSelect2Options(options);
        $(this).data('state', 'unselecting');
    }).on('select2:unselect', function(e) {
        updateSelect2Options(options);
        $(this).data('state', 'unselecting');
    }).on('select2:opening', function(e) {
        if ($(this).data('state') === 'unselecting') {
          updateSelect2Options(options);
            $(this).removeData('state');
            e.preventDefault();
        }
    });
    updateSelect2Options(options);
});

     // Function to filter listings based on selected options and keyword
     function filterListings(changedSelect=null,proid=null) {
     
     //#select2_zipcodes,#select2_cities,#select2_states,#select2_vented,#search-by-text-new
     var selectedAgents = $('#select2_agents').val() || [];
     var selectedUses = $('#select2_uses').val() || [];
     var selectedNeighborhoods = $('#select2_neighborhoods').val() || [];
     var selectedZipcodes = $('#select2_zipcodes').val() || [];
     var selectedCities = $('#select2_cities').val() || [];
     var selectedStates = $('#select2_states').val() || [];
     var selectedVented = $('#select2_vented').val() || [];
     var keyword = $('#search-by-text-new').val().toLowerCase();

     var priceRange = $("#price-range").slider("values").map(Number);
     var priceRangeSf = $("#price-range3").slider("values").map(Number);
     var monthlyRangeSf = $("#price-range4").slider("values").map(Number);
     var sizeRangeSf = $("#price-range2").slider("values").map(Number);
     var displayedListings = 0;
     var priceArray = [0],
       pricesfArray = [0],
       minsizeArray = [0],
       maxsizeArray = [0];
     var showForSale = $('#type_for_sale').is(':checked');
     var showForLease = $('#type_for_lease').is(':checked');
     var displayedListings = 0;

     $(".propertylisting-content").each(function() {
       var $listing = $(this);  
       var showListing = true,
         price = parseFloat($(this).data('price')),
         priceSf = parseFloat($(this).data('pricesf')),
         sizeMax = parseFloat($(this).data('maxsize')),
         rent = parseFloat($(this).data('monthly-rent')),
         isBetweenMaxMinPrice = (price >= priceRange[0]) && (price <= priceRange[1]),
         isBetweenMaxMinPriceSf = (priceSf === 0) || ((priceSf >= priceRangeSf[0]) && (priceSf <= priceRangeSf[1])),
         isBetweenMaxMinSize = (sizeMax >= sizeRangeSf[0]) && (sizeMax <= sizeRangeSf[1]),
         isBetweenMaxMinMonthly = (rent >= monthlyRangeSf[0]) && (rent <= monthlyRangeSf[1]);
         

/*        if (selectedAgents.length > 0 && !selectedAgents.includes($listing.find("#tri_listing_agent").text().trim())) {
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
       } */
       function getSelectedOptions(selector) {
    return $(selector)
        .find('.select2-selection__choice')
        .map(function() {
            return $(this).attr('title').trim();
        }).get();
}



// Fetch all selected options for each criterion
const selectedAgents = getSelectedOptions('#select2-select2_agents-container');
const selectedUses = getSelectedOptions('#select2-select2_uses-container');
const selectedNeighborhoods = getSelectedOptions('#select2-select2_neighborhoods-container');
const selectedZipcodes = getSelectedOptions('#select2-select2_zipcodes-container');
const selectedCities = getSelectedOptions('#select2-select2_cities-container');
const selectedStates = getSelectedOptions('#select2-select2_states-container');
const selectedVented = getSelectedOptions('#select2-select2_vented-container');

// Get the respective fields from the listing
const listingAgent = $listing.find("#tri_listing_agent").text().trim();
const listingUse = $listing.find(".tri_use").text().trim();
const listingNeighborhood = $listing.find("#tri_neighborhood").text().trim();
const listingZipcode = $listing.find("#tri_zip_code").text().trim();
const listingCity = $listing.find("#tri_city").text().trim();
const listingState = $listing.find("#tri_state").text().trim();
const listingVented = $listing.find("#tri_vented").text().trim();
const listingText = $listing.text().toLowerCase();

// Check if each respective field is in the corresponding selected array
/* if (selectedAgents.length > 0 && !selectedAgents.includes(listingAgent)) {
    showListing = false;
} */

/* multiple agents  select working starrt */
function isAnyPartIncluded(selectedAgents, listingAgent) {
    // Split listingAgent by commas and trim each part
    const listingParts = listingAgent.split(' ,').map(part => part.trim());

    // Check if any part of listingAgent is included in selectedAgents
    return listingParts.some(part => selectedAgents.includes(part));
}

// Helper function to get selected options from Select2
function getSelectedOptions(containerId) {
    const selectedOptions = [];
    $(containerId).find('.select2-selection__choice').each(function() {
        selectedOptions.push($(this).attr('title'));
    });
    return selectedOptions;
}


if (selectedAgents.length > 0 && !isAnyPartIncluded(selectedAgents, listingAgent)) {
    showListing = false;
}

/* multiple agents select working end */

if (selectedUses.length > 0 && !selectedUses.includes(listingUse)) {
    showListing = false;
}

if (selectedNeighborhoods.length > 0 && !selectedNeighborhoods.includes(listingNeighborhood)) {
    showListing = false;
}

if (selectedZipcodes.length > 0 && !selectedZipcodes.includes(listingZipcode)) {
    showListing = false;
}

if (selectedCities.length > 0 && !selectedCities.includes(listingCity)) {
    showListing = false;
}

if (selectedStates.length > 0 && !selectedStates.includes(listingState)) {
    showListing = false;
}

if (selectedVented.length > 0 && !selectedVented.includes(listingVented)) {
    showListing = false;
}


/*        if (keyword && !$listing.text().toLowerCase().includes(keyword)) {
         showListing = false;
       } */

       
       if (keyword) {
    const keywords = keyword.toLowerCase().split(' ');
    for (const word of keywords) {
        if (!listingText.includes(word)) {
            showListing = false;
            break;
        }
    }
}

       if (!isBetweenMaxMinPrice) {
         showListing = false;
       }
       if(!isBetweenMaxMinMonthly) {
        showListing = false;
       }
       if (!isBetweenMaxMinPriceSf) {
         showListing = false;
       }

       if (!isBetweenMaxMinSize) {
         showListing = false;
       }

       if(changedSelect=='clearall') {
         showListing = true;
       }
      

       var isForLease = $listing.find(".tri_for_lease").length > 0;
       var isForSale = $listing.find(".tri_for_sale").length > 0;

       if ((showForSale && isForSale) || (showForLease && isForLease)) {
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

      /* column type start */

   var total_search_results = displayedListings; // Assuming $total_search_results is a PHP variable containing the total search results

   var $propertyListingContent = $('#propertylisting-content');
 
 
   var propertyFilter = $('.property-filter');
   if (displayedListings === 1) {
       propertyFilter.removeClass('column-one column-two'); // Remove previous classes
         propertyFilter.addClass('column-one');
     } else if (displayedListings === 2) {
       propertyFilter.removeClass('column-one column-two'); // Remove previous classes
         propertyFilter.addClass('column-two');
     }
     else if (displayedListings > 2) {
       propertyFilter.removeClass('column-one column-two column-three'); // Remove previous classes
         var selectedOption = $('#selectcolumn').val();
         // console.log("selectedOption: "+selectedOption);
         var selectedOptionClass = 'column-three';
         
         if (selectedOption == 1) {
             selectedOptionClass = 'column-one';
         } else if (selectedOption == 2) {
             selectedOptionClass = 'column-two';
         } else if (selectedOption == 3) {
             selectedOptionClass = 'column-three';
         }
 
         propertyFilter.addClass(selectedOptionClass);
     }

     var maxPrice = findMax(priceArray, 'price-range'),
       maxsf = findMax(pricesfArray, 'price-range3'),
       maxSize = findMax(maxsizeArray, 'price-range2');
     var dataSlided = $('#search-by-text-new').data('slided');
    
     //price

     // if (proid !== 'price-range') {
     
     //   $("#price-range").slider("option", "values", [0, maxPrice]);
     //   $("#price-range-max").val('$' + maxPrice.toLocaleString());
     // }
     // //sf
     // if (proid !== 'price-range3') {
      
     //   $('#price-range3').slider("option", "values", [0, maxsf]);
     //   $("#rent-range-max").val('$' + maxsf.toLocaleString());
     // }
     // if (proid !== 'price-range2') {
   
     //   $('#price-range2').slider("option", "values", [0, maxSize]);
     //   $("#size-range-max").val(maxSize.toLocaleString() + ' SF');
     // }
     get_markerData(false);
     if (changedSelect !== 'type_for_lease_unchecked' && changedSelect !== 'type_for_sale_unchecked') {
       updateSelect2Options(changedSelect);
     }
 
     var scrollPosition = $(window).scrollTop();
     if(scrollPosition>700){
       var elem =$(document).find('.right-map');
       $('html, body').animate({
           scrollTop: $(elem).offset().top
       }, 1);
     }

   }

/*       function updateSelect2Options(changedSelect) {
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
      } */


     /* working on comma seprated agents enable/disable start */
 

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

    // Iterate over visible propertylisting-content elements to populate filterValues
    $(".propertylisting-content:visible").each(function() {
        // Handle comma-separated agents in #tri_listing_agent
        var agentsText = $(this).find("#tri_listing_agent").text().trim();
        var agentsArray = agentsText.split(',').map(function(agent) { return agent.trim(); });
        agentsArray.forEach(function(agent) {
            filterValues.agents.add(agent);
        });

        // Add other filter values to respective sets
        filterValues.uses.add($(this).find(".tri_use").text().trim());
        filterValues.neighborhoods.add($(this).find("#tri_neighborhood").text().trim());
        filterValues.zipcodes.add($(this).find("#tri_zip_code").text().trim());
        filterValues.cities.add($(this).find("#tri_city").text().trim());
        filterValues.states.add($(this).find("#tri_state").text().trim());
        filterValues.vented.add($(this).find("#tri_vented").text().trim());
    });

    // Iterate through each filter value set
    $.each(filterValues, function(key, values) {
        if (key !== changedSelect) {
            var select = $('#select2_' + key);
            var options = select.find('option');

            options.each(function() {
                var optionValue = $(this).val();
                var shouldDisable = true;

                // Check if optionValue is in the values set
                values.forEach(function(value) {
                    // Handle comma-separated values in filterValues.agents
                    var parts = value.split(',').map(function(part) { return part.trim(); });
                    if (parts.includes(optionValue)) {
                        shouldDisable = false;
                    }
                });

                // Disable or enable the option based on shouldDisable
                $(this).prop('disabled', shouldDisable);
            });

            // Trigger change event for Select2
            select.trigger('change.select2');
        }
    });
}


      /* working on comma seprated agents enable/disable  end */

      function findMax(arr, sliderID) {

        let max = arr[0];
        if (arr.length > 0) {
          for (let i = 1; i < arr.length; i++) {
            if (arr[i] > max) {
              max = arr[i];
            }
          }
        } else {

        }
        if (max === 0) {
          max = $('#' + sliderID).data('max');
        }
        return parseInt(max);
      }

      // Automatically check both checkboxes on page load
      $('#type_for_sale').prop('checked', true);
      $('#type_for_lease').prop('checked', true);

      // Initially filter listings based on selected options
      // filterListings();

      // Attach keyup event to search box to filter listings on input
      $('#search-by-text-new').on('keyup', function() {

        // var maxPrice = 0;

        // // Iterate through each div element
        // $('div[data-pricesf]').each(function() {
        //   var price = parseFloat($(this).attr('data-price')); // Get the value of data-pricesf attribute
        //   if (price > maxPrice) {
        //     maxPrice = price; // Update maxPrice if a higher value is found
        //   }
        // });

        // Update UI with the maximum price
        // $("#price-range").slider("option", "values", [0, maxPrice]);
        // $("#price-range-max").val('$' + maxPrice.toLocaleString());

        filterListings();
      });

      
      $("#price-range,#price-range3,#price-range2").on("slidestart", function(event, ui) {
      
      $("#search-by-text-new").attr('data-slided', $(this).prop('id'));
    });
      
      $("#price-range,#price-range3,#price-range2,#price-range4").on("slidestop", function(event, ui) {
       
          filterListings(null,$(this).prop('id'));
       
      });

      $('#type_for_sale, #type_for_lease').on('change', function() {
        var currentId = $(this).attr('id');
        var currentState = $(this).is(':checked');
        var stateString = currentState ? "checked" : "unchecked";
        var identifier = currentId + "_" + stateString;
        
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


      /* clear filter start */
      document.getElementById("filter-clear11").addEventListener("click", function() {
    // Function to get the current values of the select2 dropdowns
    function getSelect2Values() {
        return {
            agents: $('#select2_agents').val(),
            uses: $('#select2_uses').val(),
            neighborhoods: $('#select2_neighborhoods').val(),
            zipcodes: $('#select2_zipcodes').val(),
            cities: $('#select2_cities').val(),
            states: $('#select2_states').val(),
            vented: $('#select2_vented').val()
        };
    }

    // Function to get the current states of the checkboxes
    function getCheckboxValues() {
        return {
            forSale: document.getElementById('type_for_sale').checked,
            forLease: document.getElementById('type_for_lease').checked
        };
    }

    // Function to get the current values of the price ranges
    function getPriceRangeValues() {
        return {
            priceRange: $('#price-range').slider("values"),
            priceRange2: $('#price-range2').slider("values"),
            priceRange3: $('#price-range3').slider("values"),
            priceRange4: $('#price-range4').slider("values")
        };
    }

    // Capture values before reset
    var select2ValuesBefore = getSelect2Values();
    var checkboxValuesBefore = getCheckboxValues();
    var priceRangeValuesBefore = getPriceRangeValues();
    var searchByTextNew = document.getElementById('search-by-text-new');

// Check if the input field has a value
var isSearchByTextNewFilled = searchByTextNew && searchByTextNew.value.trim().length > 0;
    // Check if any value is selected
    var anySelected = isSearchByTextNewFilled || Object.values(select2ValuesBefore).some(value => value && value.length > 0) ||
                      !checkboxValuesBefore.forSale ||
                      !checkboxValuesBefore.forLease;
                      resetRangeFilters();
    if (1==1) {
        // Perform reset operations for select2
        $('#select2_agents').val(null).trigger('change');
        $('#select2_uses').val(null).trigger('change');
        $('#select2_neighborhoods').val(null).trigger('change');
        $('#select2_zipcodes').val(null).trigger('change');
        $('#select2_cities').val(null).trigger('change');
        $('#select2_states').val(null).trigger('change');
        $('#select2_vented').val(null).trigger('change');

        // Handle the cases with blank options
        $('#select2_agents').find('option').each(function() {
            if ($(this).text().trim() === '') {
                $(this).prop('selected', false);
            }
        });
        $('#select2_uses').find('option').each(function() {
            if ($(this).text().trim() === '') {
                $(this).prop('selected', false);
            }
        });
        $('#select2_neighborhoods').find('option').each(function() {
            if ($(this).text().trim() === '') {
                $(this).prop('selected', false);
            }
        });
        $('#select2_zipcodes').find('option').each(function() {
            if ($(this).text().trim() === '') {
                $(this).prop('selected', false);
            }
        });
        $('#select2_cities').find('option').each(function() {
            if ($(this).text().trim() === '') {
                $(this).prop('selected', false);
            }
        });
        $('#select2_states').find('option').each(function() {
            if ($(this).text().trim() === '') {
                $(this).prop('selected', false);
            }
        });
        $('#select2_vented').find('option').each(function() {
            if ($(this).text().trim() === '') {
                $(this).prop('selected', false);
            }
        });

        // Reset checkboxes
        var forSaleCheckbox = document.getElementById('type_for_sale');
        var forLeaseCheckbox = document.getElementById('type_for_lease');

        forSaleCheckbox.checked = true;
        forLeaseCheckbox.checked = true;

        if (forSaleCheckbox.hasAttribute('disabled')) {
            forSaleCheckbox.removeAttribute('disabled');
        }
        if (forLeaseCheckbox.hasAttribute('disabled')) {
            forLeaseCheckbox.removeAttribute('disabled');
        }

     

        var rangeHiddenFields = $("#price-range-selected, #rent-range-selected, #size-range-selected");
        rangeHiddenFields.attr("data-clear", "1");

        var ranges = ['#price-range', '#price-range3', '#price-range2', '#price-range4'];
        ranges.forEach(function(range) {
            var $range = $(range);
            $range.slider("option", "max", $range.data('max'));
            $range.slider("option", "min", $range.data('min'));
            $range.slider("values", [$range.data('min'), $range.data('max')]);
        });

        // Reset range inputs
        $('.range-inputs').each(function() {
            $(this).val($(this).attr('data-default'));
        });
        

        // Capture values after reset
        var select2ValuesAfter = getSelect2Values();
        var checkboxValuesAfter = getCheckboxValues();
        var priceRangeValuesAfter = getPriceRangeValues();

        console.log('Before reset:', {
            select2Values: select2ValuesBefore,
            checkboxValues: checkboxValuesBefore,
            priceRangeValues: priceRangeValuesBefore
        });
        console.log('After reset:', {
            select2Values: select2ValuesAfter,
            checkboxValues: checkboxValuesAfter,
            priceRangeValues: priceRangeValuesAfter
        });
        // filterListings();
        $("#search-by-text-new").val("");
        filterListings();
        if (forSaleCheckbox) {
    forSaleCheckbox.checked = false;
    forSaleCheckbox.click();
}

    }
    
    else {
      var forSaleCheckbox = document.getElementById('type_for_sale');
     
    
    forSaleCheckbox.checked = false;
    forSaleCheckbox.click();
    // filterListings();

        console.log('No values selected, no reset performed.');
    }

    if ($('.ts-state-page').length > 0) {
        // Initialize select2 if not already initialized
        if (!$('#select2_states').data('select2')) {
            $('#select2_states').select2();
        }

        var currentState = $('.ts-state-page').data('current_state');

        if (currentState) {
           
            currentState = currentState.toUpperCase();

            // Select the option with the value of currentState
            $('#select2_states').val(currentState).trigger('change');
        }
    }

});
      /* clear filter end */

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
      jQuery(document).ready(function($) {

        var val = '<?php echo $atts['state']  ?>';
        $('#select2_states').val(val).trigger('change');
       // $('#select2_states').prev('label').hide();
       // $('#select2_states').next(".select2-container").hide();
      });
    </script>

  <?php

  }

  ?>

  <!-- text data 1 -->
  <textarea style="display: none;" id="marker_data_all"><?php echo json_encode($markers_data) ?></textarea>
  <?php
  

  return ob_get_clean();
}


function get_usesname_by_propertyID($propertyTypeID){
  
 
  switch ($propertyTypeID) {
    case "1":
      $property_uses_name = 'Office';
      break;
    case "2":
     $property_uses_name = 'Retail'; 
      break;
    case "3":
     $property_uses_name = 'Industrial'; 
      break;
    case "5":
     $property_uses_name = 'Land'; 
      break;
    case "6":
     $property_uses_name = 'Multifamily'; 
      break;
    case "7":
     $property_uses_name = 'Special Purpose'; 
      break;
    case "8":
     $property_uses_name = 'Hospitality'; 
      break;
    default:
    $property_uses_name = false;
}

return $property_uses_name;
  
}


function get_usename_subtype($property_subtype_id){

  switch ($property_subtype_id) {
    case "201":
      $property_uses_subtype = 'Street Retail';
      break;
    case "202":
     $property_uses_subtype = 'Strip Center'; 
      break;
    case "203":
     $property_uses_subtype = 'Free Standing Building'; 
      break;
    case "204":
     $property_uses_subtype = 'Regional Mall'; 
      break;
    case "205":
     $property_uses_subtype = 'Retail Pad'; 
      break;
    case "206":
     $property_uses_subtype = 'Vehicle Related'; 
      break;
    case "207":
     $property_uses_subtype = 'Outlet Center'; 
      break;
    case "208":
      $property_uses_subtype = 'Power Center'; 
    break;
    case "209":
      $property_uses_subtype = 'Neighborhood Center'; 
    break;
    case "210":
      $property_uses_subtype = 'Community Center'; 
    break;
    case "211":
      $property_uses_subtype = 'Specialty Center'; 
    break;
    case "212":
      $property_uses_subtype = 'Theme/Festival Center'; 
    break;
    case "213":
      $property_uses_subtype = 'Restaurant'; 
    break;
    case "214":
      $property_uses_subtype = 'Post Office'; 
    break;
    case "216":
      $property_uses_subtype = 'Lifestyle Center'; 
    break;
    default:
    $property_uses_subtype = false;
}

return $property_uses_subtype;

}



function tristatecr_create_lease_space_table()
{

    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    /* create table to store custom order */
    $space_tbl_name = $wpdb->prefix . 'lease_spaces';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$space_tbl_name}'") != $space_tbl_name) :
    
      $sql = "CREATE TABLE $space_tbl_name (
        id INT(20) NOT NULL AUTO_INCREMENT,
        lease_id varchar(255)  NULL,
        property_id varchar(255)  NULL,
        lease_rate_units varchar(255)  NULL,
        lease_rate varchar(255)  NULL,
        space_size_units varchar(255)  NULL,
        size_sf varchar(255)  NULL,
        leasechecksum varchar(32)  NULL,
        PRIMARY KEY (id)) $charset_collate;";
        
        dbDelta($sql);
    endif;
}


add_action('plugin_loaded', 'tristatecr_create_lease_space_table');


// add_action('init', 'tri_update_lease_spaces');
function tri_update_lease_spaces(){
  $settings = get_option('tristate_cr_settings');
  $get_buildout_api_key = $settings['buildout_api_key'];  
  $response = wp_remote_get( 'https://buildout.com/api/v1/' . $get_buildout_api_key . '/lease_spaces.json?limit=3462', array(
    'headers' => array(
        'Accept' => 'application/json',
        'timeout' => 500 
    )
) );

if (is_wp_error($response)) {
  error_log('Buildout API request failed: ' . $response->get_error_message());
  return; // Exit the function if the request failed.
}
if (200 === wp_remote_retrieve_response_code( $response )) {
  $lease_data = json_decode(wp_remote_retrieve_body($response));
  $lease_spaces = $lease_data->lease_spaces;
  $checksum_lease_space = md5(json_encode($lease_spaces));
   
    if ($checksum_lease_space != get_option('tristatecr_datasync_lease_checksum')) {
     
      global $wpdb; 
      $space_tbl= $wpdb->prefix . 'lease_spaces';
      $extracted_data = array_map(function($space) {
          $values = [
              $space->id,
              $space->property_id,
              $space->lease_rate_units,
              $space->lease_rate,
              $space->space_size_units,
              $space->size_sf
          ];
          return [
              'lease_id' => $values[0],
              'property_id' => $values[1],
              'lease_rate_units' => $values[2],
              'lease_rate' => $values[3],
              'space_size_units' => $values[4],
              'size_sf' => $values[5],
              'leasechecksum' =>md5(implode('', $values))
          ];
      }, $lease_spaces);

      foreach($extracted_data as $ed){
          $leasechecksum = $ed['leasechecksum'];
          $lease_id = $ed['lease_id'];
          $existing_record = $wpdb->get_row($wpdb->prepare(
              "SELECT * FROM $space_tbl WHERE lease_id = %d",
              $ed['lease_id']
          ));
          
          if(!$existing_record){
              $wpdb->insert(
                  $space_tbl,
                  array(
                      'lease_id' => $ed['lease_id'],
                      'property_id' => $ed['property_id'],
                      'lease_rate_units' => $ed['lease_rate_units'],
                      'lease_rate' => $ed['lease_rate'],
                      'space_size_units' => $ed['space_size_units'],
                      'size_sf' => $ed['size_sf'],
                      'leasechecksum' => $ed['leasechecksum'],
                  ),
                  array('%s', '%s', '%s', '%s', '%s', '%s', '%s')
              );
              
          }else{
              
              if ($existing_record->leasechecksum !== $leasechecksum) {
                  $wpdb->update(
                      $space_tbl,
                      array(
                          'property_id' => $ed['property_id'],
                          'lease_rate_units' => $ed['lease_rate_units'],
                          'lease_rate' => $ed['lease_rate'],
                          'space_size_units' => $ed['space_size_units'],
                          'size_sf' => $ed['size_sf'],
                          'leasechecksum' => $ed['leasechecksum'],
                      ),
                      array('lease_id' => $lease_id),
                      array('%s', '%s', '%s', '%s', '%s', '%s'),
                      array('%s')
                  );
              }
          }
      
      }
      update_option('tristatecr_datasync_lease_checksum', $checksum_lease_space);
    }
  }
}