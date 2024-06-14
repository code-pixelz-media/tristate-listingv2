<?php
if (!defined('ABSPATH')) {
  exit;
}
get_header();

/* ---------------------Start of Meta Keys------------------------- */
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
$min_size         = get_post_meta($ID, '_gsheet_min_size', true);
$max_size         = get_post_meta($ID, '_gsheet_max_size', true);
$size             = $min_size ?? $max_size;
$size             = preg_replace('/\.[0-9]+/', '', $size);
$size             = (int) preg_replace('/[^0-9]/', '', $size);
$zoning           = get_post_meta($ID, '_buildout_zoning', true);
$key_tag           = get_post_meta($ID, '_gsheet_key_tag', true);
$agents           = (array) new_tristatectr_get_brokers_with_excluded(get_post_meta($ID, '_buildout_broker_ids', true));
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
$state             = get_post_meta($ID, '_gsheet_state', true);
$buildout_sales_description            = get_post_meta($ID, '_buildout_sale_description', true);
$buildout_sale_highlights            = get_post_meta($ID, '_buildout_sale_bullets', true);
//var_dump($buildout_sale_highlights);
$buildout_lease_bullets_highlights            = get_post_meta($ID, '_buildout_lease_bullets', true);
$_buildout_documents          = get_post_meta($ID, '_buildout_documents', true);

$property_img_gallerys = get_post_meta($ID, '_buildout_photos', true);

$lat               = get_post_meta($ID, '_buildout_latitude', true);
$lng               = get_post_meta($ID, '_buildout_longitude', true);



$buildout_notes   = get_post_meta($ID, '_buildout_notes', true);
$gsheet_notes     = get_post_meta($ID, '_gsheet_notes', true);
$gsheet_buildout_address     = get_post_meta($ID, '_buildout_address', true);
$buildout_hidden_price_label     = get_post_meta($ID, '_buildout_hidden_price_label', true);
$space_size     = get_post_meta($ID, '_buildout_name', true);
$number_of_floor     = get_post_meta($ID, '_buildout_number_of_floors', true);
$number_of_units     = get_post_meta($ID, '_buildout_number_of_units', true);
$property_youtube_url     = get_post_meta($ID, '_buildout_you_tube_url', true);
$fspace_size_text = strstr($space_size, '|', true);
$final_space_size_text = trim($fspace_size_text);


$doc_link = $_buildout_documents[0]->url;
$doc_name = $_buildout_documents[0]->name;





/* ---------------------End of Meta Keys------------------------- */

?>
<div class="tristate_cr_wrapper">
  <div class="trisate_cr_container">
    <div class="tristate_cr_header tristate_cr_d-flex">
      <div class="haeder_left tristate_cr_col_8">
        <h1><?php the_title(); ?></h1>
        <h3><?php echo $subtitle; ?></h3>
      </div>

      <?php if ($_type == 'for Sale') {
        echo ' <div class="haeder_left tristate_cr_col_4 text-right">
        <h2 class="h2"> ' . $price . '</h2>
        <h3>Sale Price</h3>
      </div>';
      } elseif ($_type == 'for lease' || $_type == 'for Lease') {
        echo ' <div class="haeder_left tristate_cr_col_4 text-right">
        <h2 class="h2">' . $price . '</h2>
        <h3>Lease Rate</h3>
      </div>';
      } else {
        echo ' <div class="haeder_left tristate_cr_col_4 text-right">
        <h2 class="h2">Call: 215-300-9688 </h2>
      </div>';
      }
      ?>
    </div>
    <div class="tristate_cr-tabs">
      <ul class="tcr_tabs">
        <li class="active" rel="tcr_tab1"><i class="fa fa-briefcase"></i> Overview</li>

        <li rel="tcr_tab2"><i class="fa fa-briefcase"></i> Spaces</li>

        <?php if ($doc_link && $doc_name) { ?>
          <li rel="tcr_tab3"><i class="fa fa-file"></i> Documents</li>
        <?php } ?>

        <?php if ($property_img_gallerys) { ?>
          <li rel="tcr_tab4"><i class="fa fa-image"></i> Photos</li>
        <?php } ?>

        <?php if ($lat && $lng) { ?>
          <li rel="tcr_tab5"><i class="fa fa-map"></i> Maps</li>
        <?php } ?>
        <li rel="tcr_tab6"><i class="fa fa-chart-pie"></i> Demographics</li>
      </ul>
      <div class="tcr_tab_container">
        <h3 class="d_active tab_drawer_heading" rel="tcr_tab1">Overview</h3>
        <div id="tcr_tab1" class="tcr_tab_content">
          <div class="tcr_tab_wrapper tristate_cr_d-flex">
            <div class="tristate_cr_col_8">

  <?php
if ($property_img_gallerys) {
?>
    <swiper-container style="--swiper-navigation-color: #1a76d2; --swiper-pagination-color:#1a76d2" class="mySwiper" thumbs-swiper=".mySwiper2" loop="true" space-between="10" navigation="true">
       
            <?php
            foreach ($property_img_gallerys as $key => $property_img_gallery) {
                echo '<swiper-slide><img src="' . $property_img_gallery->url . '" alt="" width="792px"></swiper-slide>';
            }
            ?>
       
     
    </swiper-container>

    <swiper-container class="mySwiper2" loop="true" space-between="10" slides-per-view="4" free-mode="true" watch-slides-progress="true">
        
            <?php
            foreach ($property_img_gallerys as $key => $property_img_gallery) {
                echo '<swiper-slide><img src="' . $property_img_gallery->url . '" alt=""></swiper-slide>';
            }
            ?>
       
    </swiper-container>
<?php
}
?>

  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-element-bundle.min.js"></script>
<?php
if ($property_img_gallerys=='test') {
?>
    <div class="gallery">
        <!-- Main slider -->
        <div class="swiper-container gallery-slider">
            <div class="swiper-wrapper">
                <?php
                foreach ($property_img_gallerys as $key => $property_img_gallery) {
                    echo '<div class="swiper-slide"><img src="' . $property_img_gallery->url . '" alt="" width="792px"></div>';
                }
                ?>
            </div>
            <div class="swiper-button-prev swiper-button-prev01"></div>
            <div class="swiper-button-next swiper-button-next01"></div>
        </div>
        
        <!-- Thumbnails slider -->
        <div class="swiper-container gallery-thumbs">
            <div class="swiper-wrapper">
                <?php
                foreach ($property_img_gallerys as $key => $property_img_gallery) {
                    echo '<div class="swiper-slide"><img src="' . $property_img_gallery->url . '" alt=""></div>';
                }
                ?>
            </div>
        </div>
    </div>
<?php
}
?>








              <div class="tcr_property_content">


                <div class="tcr_content_wrapper">
                  <h2>Property Details</h2>
                  <table>
                    <?php if ($_type) { ?>
                      <tr>
                        <td>Property Type</td>
                        <td><?php echo $_type; ?></td>
                      </tr>
                    <?php
                    } ?>

                    <?php if ($title) { ?>
                      <tr>
                        <td>Property Name</td>
                        <td><?php echo $title; ?></td>
                      </tr>
                    <?php
                    } ?>

                    <?php if ($_agent) { ?>
                      <tr>
                        <td>Primary Broker</td>
                        <td><?php echo $_agent; ?></td>
                      </tr>
                    <?php
                    } ?>

                    <?php if ($zoning) { ?>
                      <tr>
                        <td>Zoning</td>
                        <td><?php echo $zoning; ?></td>
                      </tr>
                    <?php  } ?>

                    <?php if ($number_of_floor) { ?>
                      <tr>
                        <td>Floor</td>
                        <td><?php echo $number_of_floor; ?></td>
                      </tr>
                    <?php } ?>
                  </table>
                </div>

                <?php if ($buildout_sales_description) {
                ?>
                  <div class="tcr_content_wrapper">

                    <h2>Property Description</h2>
                    <p><?php echo $buildout_sales_description; ?></p>
                  </div>
                <?php } ?>
                <div class="tcr_content_wrapper">
                  <h2>Location Description</h2>
                  <?php echo $summary; ?>
                </div>
                <div class="tcr_content_wrapper">
                  <h2>Highlights</h2>
                  <ul>
                    <?php

                    if ($buildout_sale_highlights) {
                      foreach ($buildout_sale_highlights as $key => $buildout_sale_highlight) {
                        # code...
                        echo '<li>' . $buildout_sale_highlight . '</li>';
                      }
                    }
                    if ($buildout_lease_bullets_highlights) {
                      foreach ($buildout_lease_bullets_highlights as $key => $buildout_lease_bullets_highlight) {
                        # code...
                        echo '<li>' . $buildout_lease_bullets_highlight . '</li>';
                      }
                    }

                    ?>
                  </ul>
                </div>

                <?php if (!empty($lat) && !empty($lng)) { ?>
                  <div class="tcr_content_wrapper">
                    <h2>Map</h2>
                    <div id="single-gmap" style="width:100%; height:500px;"></div>
                  </div>
                <?php } ?>

              </div>

            </div>
            <div class="tristate_cr_col_4">
              <div class="right-header">
                <?php
                if ($doc_link || $doc_name) {
                ?>
                  <a target="_blank" href="<?php echo $doc_link; ?>">
                    <h2><i class="fa fa-file"></i> <?php echo $doc_name; ?></h2>
                  </a>
                <?php } ?>
              </div>
              <?php if ($youtube_url) { ?>
                <div class="right-header youtube-video">

                  <a target="_blank" href="<?php echo $youtube_url; ?>">
                    <h2><i class="fa fa-video"></i> View Video</h2>
                  </a>

                </div>
              <?php } ?>


              <?php

              // $agents
              if ($agents) {
                foreach ($agents as $key => $agent) {
                  $get_agent_id = $agent;
                  $get_broker_id =  new_tristate_get_broker_id('user_id', $get_agent_id);
                  if ($get_broker_id) {
                    $get_broker_full_name = get_the_title($get_broker_id);
                    $get_broker_email = get_post_meta($get_broker_id, 'broker_email', true);
                    $get_broker_address = get_post_meta($get_broker_id, 'broker_address', true);
                    $get_broker_job_title = get_post_meta($get_broker_id, 'broker_job_title', true);
                    $get_broker_phone_number = get_post_meta($get_broker_id, 'broker_phone_number', true);
                    $get_broker_profile_pic = get_post_meta($get_broker_id, 'broker_profile_pic', true);

                    # code...

                    echo ' <div class="tcr_members_wrapper tristate_cr_d-flex">
                <div class="tcr_member_thumb">
                  <img src="' . $get_broker_profile_pic . '" />
                </div>
                <div class="tcr_members_details">
                  <h4>' . $get_broker_full_name . '</h4>
                  <h5>' . $get_broker_job_title . '</h5>
                  <div><a href="tel:' . $get_broker_phone_number . '"><i class="fa fa-phone-alt"></i> ' . $get_broker_phone_number . '</a></div>
                  <div><a href="mailto:' . $get_broker_email . '"><i class="fa fa-envelope"></i> ' . $get_broker_email . '</a></div>
                </div>

              </div>';

              ?>

              <?php }
                }
              } ?>

              <div class="tcr_socialshare">
                <h2>Share to Social</h2>
                <?php echo do_shortcode('[Sassy_Social_Share]'); ?>
              </div>
              <div class="tcr_property_form">
                <h2>Request a More Info</h2>
                <?php echo do_shortcode('[contact-form-7 id="ac5603a" title="Request a more info"]'); ?>
              </div>
            </div>
          </div>
        </div>
        <!-- #tab1 -->
        <h3 class="tab_drawer_heading" rel="tcr_tab2">Spaces</h3>
        <div id="tcr_tab2" class="tcr_tab_content">
          <div class="tcr_content_wrapper">
            <h2>Active</h2>
            <table class="documents-table spaces-table">
              <thead>
                <tr>
                  <th scope="col">Space Name</th>
                  <th scope="col">Space Size</th>
                  <th scope="col">Lease Rate</th>
                  <th scope="col" class="d-none d-md-table-cell">Lease Term</th>
                  <th scope="col" class="d-none d-md-table-cell">Space Type</th>
                  <th scope="col" class="d-none d-md-table-cell" style="display: none !important;">Date Available</th>
                  <th></th>
                </tr>
              <tbody>
                <tr class="js-lease-space-row-toggle spaces spaces-child">
                  <th scope="row">
                    <?php echo $gsheet_buildout_address; ?>
                  </th>
                  <td>
                    <?php echo $final_space_size_text; ?>
                  </td>
                  <td>
                    <?php echo $price; ?>
                  </td>
                  <td class="d-none d-md-table-cell">
                    Negotiable
                  </td>
                  <td class="d-none d-md-table-cell">
                    <?php echo $_type; ?>
                  </td>
                  <td class="d-none d-md-table-cell">

                  </td>
                  <td class="text-end pt-3">
                    <a href="javascript:void(0)" id="spacestoggle"><i class="fas fa-chevron-down js-arrow"></i></a>
                  </td>
                </tr>
                <tr class="spaces bg-light spaces-child-content">
                  <td class="p-4" colspan="100%">
                    <h5></h5>
                    <table class="table">
                      <tbody>
                        <tr>
                          <td><b>Space Size</b></td>
                          <td><?php echo $final_space_size_text; ?></td>
                        </tr>
                        <tr>
                          <td><b>Lease Rate</b></td>
                          <td><?php echo $price; ?></td>
                        </tr>
                        <tr>
                          <td><b>Lease Term</b></td>
                          <td>Negotiable</td>
                        </tr>
                        <tr>
                          <td><b>Space Type</b></td>
                          <td><?php echo $_type; ?></td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
              </thead>

            </table>
          </div>
        </div>
        <!-- #tab2 -->
        <h3 class="tab_drawer_heading" rel="tcr_tab3">Documents</h3>
        <div id="tcr_tab3" class="tcr_tab_content">
          <div class="tcr_content_wrapper">
            
            <h2>Documents</h2>
            <table class="documents-table">
              <?php
              // var_dump($_buildout_documents);
              if ($_buildout_documents) {
                foreach ($_buildout_documents as $key => $buildout_document) {
                  $document_url =  $buildout_document->url;
                  $document_name =  $buildout_document->name;
                  # code...
                  echo '<tr><td><i class="fa fa-file-pdf"></i> <a target="_blank" href="' . $document_url . '">' . $document_name . '</a> </td>
                </tr>';
                }
              }
              ?>
            </table>
          </div>
        </div>
        <!-- #tab3 -->
        <h3 class="tab_drawer_heading" rel="tcr_tab4">Photos</h3>
        <div id="tcr_tab4" class="tcr_tab_content">
          <div class="tcr_tab_wrapper">
            <div class="gallery">

              <?php if ($property_img_gallerys) { ?>
                <div class="gallery-thumbs3">
                  <div class="swiper-wrapper1">
                    <?php


                    foreach ($property_img_gallerys as $key => $property_img_gallery) {
                      # code...
                      echo '<div class="swiper-slide"><a data-fancybox="gallery" onclick="event.preventDefault()"  href="' . $property_img_gallery->url . '  "><img src="' . $property_img_gallery->url . '" alt=""></a></div>';
                    }

                    ?>
                  </div>
                </div>
              <?php } else {
                echo 'No Photos Available for this Property';
              } ?>
            </div>
          </div>
        </div>
        <!-- #tab4 -->
        <h3 class="tab_drawer_heading" rel="tcr_tab5">Maps</h3>
        <div id="tcr_tab5" class="tcr_tab_content">

          <div class="tcr_content_wrapper">

            <?php if ($lat || $lng) { ?>
              <div id="demographic-map" style="width:100%; height:500px;"></div>
            <?php } else {
              echo 'No Map data found';
            } ?>
          </div>
        </div>
        <!-- #tab5 -->
        <h3 class="tab_drawer_heading" rel="tcr_tab6">Demographics</h3>
        <div id="tcr_tab6" class="tcr_tab_content">
          <div class="tcr_content_wrapper">
            <?php if ($lat || $lng) { ?>
              <?php tristatecr_single_property_googe_map($lat, $lng); ?>
            <?php } else {
              echo 'No Map data found';
            } ?>
          </div>
          <table class="demographics-table">
            <tr>
              <th></th>
              <th>0.25 Miles </th>
              <th>0.5 Miles </th>
              <th>1 Mile </th>
            </tr>
            <tr>
              <th>Total households</th>
              <td>3,280</td>
              <td>9,365</td>
              <td>36,056</td>
            </tr>
            <tr>
              <th>Total households</th>
              <td>3,280</td>
              <td>9,365</td>
              <td>36,056</td>
            </tr>
            <tr>
              <th>Total households</th>
              <td>3,280</td>
              <td>9,365</td>
              <td>36,056</td>
            </tr>
            <tr>
              <th>Total households</th>
              <td>3,280</td>
              <td>9,365</td>
              <td>36,056</td>
            </tr>
            <tr>
              <th>Total households</th>
              <td>3,280</td>
              <td>9,365</td>
              <td>36,056</td>
            </tr>
            <tr>
              <th>Total households</th>
              <td>3,280</td>
              <td>9,365</td>
              <td>36,056</td>
            </tr>
            <tr>
              <th>Total households</th>
              <td>3,280</td>
              <td>9,365</td>
              <td>36,056</td>
            </tr>

          </table>
          <em>* Demographic Data Derived From 2020 ACS - US Census</em>
        </div>
        <!-- #tab6 -->

      </div>
      <!-- .tab_container -->
    </div>
  </div>
</div>
</div>
<?php
$settings = get_option('tristate_cr_settings');
$get_google_map_api_key = $settings['google_maps_api_key'];
?>
<script>









  (g => {
    var h, a, k, p = "The Google Maps JavaScript API",
      c = "google",
      l = "importLibrary",
      q = "__ib__",
      m = document,
      b = window;
    b = b[c] || (b[c] = {});
    var d = b.maps || (b.maps = {}),
      r = new Set,
      e = new URLSearchParams,
      u = () => h || (h = new Promise(async (f, n) => {
        await (a = m.createElement("script"));
        e.set("libraries", [...r] + "");
        for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]);
        e.set("callback", c + ".maps." + q);
        a.src = `https://maps.${c}apis.com/maps/api/js?` + e;
        d[q] = f;
        a.onerror = () => h = n(Error(p + " could not load."));
        a.nonce = m.querySelector("script[nonce]")?.nonce || "";
        m.head.append(a)
      }));
    d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n))
  })
  ({
    key: "<?php echo $get_google_map_api_key; ?>",
    v: "weekly"
  });
</script>

<script>


  function createMap(div, lat, lng) {
    let map;

    async function initMap() {

      const position = {
        lat: lat,
        lng: lng
      };
      const {
        Map
      } = await google.maps.importLibrary("maps");
      const {
        AdvancedMarkerElement
      } = await google.maps.importLibrary("marker");
      map = new Map(document.getElementById(div), {
        zoom: 12,
        center: position,
        mapId: "roadmap",
      });
      const iconimg = document.createElement("img");
      iconimg.src = "http://s3.amazonaws.com/buildout-production/brandings/2138/profile_photo/small.png";
      const marker = new AdvancedMarkerElement({
        map: map,
        position: position,
        content: iconimg,
      });
    }
    initMap();
  }
  let lat = <?php echo $lat ?>;
  let lng = <?php echo $lng ?>;
  createMap('single-gmap', lat, lng);
  createMap('demographic-map', lat, lng);
</script>
<?php
get_footer();
