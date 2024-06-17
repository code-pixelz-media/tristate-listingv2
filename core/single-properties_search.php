<?php get_header();
?>


<section id="container">


    <?php if (have_posts()) : the_post(); ?>

        <h1 class="page-title"><?php echo get_the_title(); ?></h1>

        <?php
        $listing_layers = get_post_meta(get_the_ID(), 'listing_ids', false);
        $layer_names         = get_post_meta(get_the_ID(), 'layer_name', false);
        $post_name = get_post_field('post_name', get_post());
        $layers = array();

        foreach ($listing_layers as $index => $listing_layer) :
            $layers[] = $IDs = explode(',', $listing_layer);
        endforeach;

       
        ?>

        <div class="layers">
            <?php foreach ($listing_layers as $index => $listing_layer) : $IDs = explode(',', $listing_layer); ?>
                <label for="layer-<?php echo $index; ?>" class="layer">
                    <input type="checkbox" id="layer-<?php echo $index; ?>" name="layer_<?php echo $index; ?>" checked="checked" onClick="window.updateMap()" />
                    <span><?php echo $layer_names[$index] ?: 'Layer #' . $index + 1; ?></span>
                    <span> — <?php echo count($IDs); ?> properties</span>
                </label><!-- .layer -->
            <?php endforeach; ?>
        </div><!-- .layers -->

    <?php endif; ?>

    <?php if (current_user_can('edit_posts')) :
      if(isset($_GET['redirectId']) && !empty($_GET['redirectId'])){
        $redirect_page_link = get_the_permalink($_GET['redirectId']);
        $new_link =  add_query_arg(['search_id'=>get_the_id()], $redirect_page_link);
        }else{
            $new_link= "#";
        }
    ?>
        <p><a class="button" href="<?php echo $new_link; ?>" target="_blank">Add layers to this search</a></p>
    <?php endif; ?>

    <section id="content">
        <div class="listings"></div>

        <template id="listing-template">
            <div class="listing">
                <div class="listing--title"></div>
                <div class="listing--subtitle"></div>
                <div class="listing--meta">
                    <span class="listing--type"></span>
                    <span class="listing--price"></span>
                </div>
            </div><!-- .listing -->
        </template><!-- #listing-template -->

        <div id="map" style="height: 600px; width: 100%;"></div>
    </section><!-- #content -->


    </div><!-- #container -->

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyASjYF9QSfmERIuCuLv1X9PSglIo7QRVkM&callback=updateMap&v=weekly" defer></script>

    <script>
        let REST_URL = '<?php echo get_rest_url(); ?>';
        
     

        let LISTINGS_ROUTE_URL = REST_URL + 'tristatectr/v3/listings';
        let BROKERS_ROUTE_URL = REST_URL + 'tristatectr/v1/brokers';
        console.log(LISTINGS_ROUTE_URL);
        let listings = [];
        let listingsMarkers = [];

        fetch(LISTINGS_ROUTE_URL)
            .then(response => response.json())
            .then(data => {
                console.debug('listings', data);
                console.debug('listings[0]', data[0]);
                listings = data;
                window.updateMap();
            });

        let listingLayers = <?php echo json_encode($layers) ?? ''; ?>;
        console.debug('listingLayers', listingLayers);

        let layers = [];
        listingLayers.forEach(layer => {
            let item = {
                title: 'Layer ' + (layers.length + 1),
                listings: layer,
                visible: true,
            };
            layers.push(item);
        });
        console.log('layers', layers);

        let markerColors = [
            'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
            'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
            'http://maps.google.com/mapfiles/ms/icons/green-dot.png',
            'http://maps.google.com/mapfiles/ms/icons/yellow-dot.png',
            'http://maps.google.com/mapfiles/ms/icons/orange-dot.png',
            'http://maps.google.com/mapfiles/ms/icons/purple-dot.png',
            'http://maps.google.com/mapfiles/ms/icons/pink-dot.png',
        ];

        let infoWindows = [];

        function updateMap() {
            console.debug('updateMap');

            let checkboxes = document.querySelectorAll('div.layers input[type="checkbox"]');
            checkboxes.forEach((checkbox, index) => {
                layers[index].visible = checkbox.checked;
            });

            const centerCoord = {
                lat: 40.7128,
                lng: -74.0060
            };
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 10,
                center: centerCoord,
            });

            let bounds = new google.maps.LatLngBounds();

            layers.forEach((layer, index) => {
                if (!layer.visible) {
                    return;
                }

                let markerColorUrl = markerColors[index];
                layer.listings.forEach(listingId => {
                    let results = listings.filter(listing => listing.id == listingId);
                    if (!results.length) {
                        console.debug('No listing found for listingId', listingId);
                        return;
                    } else {
                        console.debug('Listing found for listingId', listingId, results);
                    }

                    let listing = results[0];
                    
                    var markerIcon = {
                        url:listing._icon,
                        scaledSize: new google.maps.Size(38, 38) 
                      };
                    
                   
                    const marker = new google.maps.Marker({
                        position: {
                            lat: parseFloat(listing.lat),
                            lng: parseFloat(listing.lng),
                        },
                        map,
                        title: "google.maps.Marker:title",
                        icon: markerIcon
                    });

                    listingsMarkers[listingId] = marker;

                    bounds.extend(marker.position);

                    let thumbImage = listing.image ? '<img src="' + listing.image + '" />' : '';

                    let thirdHeading = listing._type_str;
                    // console.log(thirdHeading);
                    // if (listing._type_str.toLowerCase() === 'for sale') {
                    //     thirdHeading.push(listing.price);
                    // }
                    // if (listing._type_str.toLowerCase() === 'for lease') {}
                    // thirdHeading = thirdHeading.filter(Boolean);
                    const backGround = thirdHeading ==='FOR LEASE' ? 'lease-bgopacity' : 'sale-bgopacity';
                    const contentString =
                        '<div class="search-results-map" id="content">' +
                        '<div id="siteNotice">' + '</div>' +
                        '<h1 class="firstHeading">' + listing.title + '</h1>' +
                        '<h2 class="secondHeading">' + listing.subtitle + '</h2>' +
                        '<hr/>' +
                        '<div id="bodyContent"><h3 class="thirdHeading '+backGround+'">' + thirdHeading + '</h3>' +
                        thumbImage + "<div class='single-search'>" +
                        '<p>' + listing.summary + '</p>' +
                        '<p><a class="listing-more" href="' + listing.get_page_link + '" target="_blank">More Info</a></p>' +
                        "</div>" +
                        "</div>" +
                        "</div>";

                    const infoWindow = new google.maps.InfoWindow({
                        content: contentString,
                        ariaLabel: listing.title,
                    });
                    infoWindows.push(infoWindow);

                    marker.addListener("click", () => {
                        infoWindows.forEach(item => item.close());
                        infoWindow.open({
                            anchor: marker,
                            map,
                        });
                    });
                });
            });
            map.fitBounds(bounds);
            renderListingsList();
        }
        window.updateMap = updateMap;

        function renderListingsList() {
            console.debug('renderListingsList');

            let listingsList = document.querySelector('div.listings');
            listingsList.innerHTML = '';

            layers.forEach((layer, index) => {
                if (!layer.visible) return;
                layer.listings.forEach(listingId => {
                    let results = listings.filter(listing => listing.id == listingId);
                    if (!results.length) {
                        console.debug('No listing found for listingId', listingId);
                        return;
                    } else {
                        console.debug('Listing found for listingId', listingId, results);
                    }

                    let listing = results[0];

                    let listingType = listing._type_str;
                    let template = document.querySelector('#listing-template');
                    let clone = template.content.cloneNode(true);
                    let listingElement = clone.querySelector('.listing');
                    listingElement.setAttribute('data-listing-id', listing.id);
                    listingElement.querySelector('.listing--title').innerHTML = listing.title;
                     listingElement.querySelector('.listing--subtitle').innerHTML = listingType;
                    listingElement.querySelector('.listing--type').innerHTML = listing._type;
                    listingElement.querySelector('.listing--price').innerHTML = listing.price ? listing.price : '';
                    listingsList.appendChild(listingElement);
  


                    let marker = listingsMarkers[listing.id];
                    listingElement.addEventListener('click', () => {
                        if (listingElement.classList.contains('active')) {
                            listingElement.classList.remove('active');
                            infoWindows.forEach(item => item.close());
                        } else {
                            document.querySelectorAll('.listings .listing').forEach(item => item.classList.remove('active'));
                            listingElement.classList.toggle('active');
                            google.maps.event.trigger(marker, 'click');
                        }
                    });

                    var listingSubtitle = listingElement.querySelector('.listing--subtitle');

                    if (listingType == "FOR SALE") {
                    listingSubtitle.classList.add('type_for_sale_listing');
                    } else if (listingType== "FOR LEASE") {
                    listingSubtitle.classList.add('type_for_lease_listing');
                    }


                });
            });
        }
    </script>

    <style type="text/css">
        #container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 80px 20px;
        }

        #container h1.page-title {
            font-size: 3rem;
            margin-bottom: 1.5rem;
        }

        #container p {
            margin: 1em 0;
        }

        #map {
            flex: 1;
        }

        div.layers {}

        label.layer {
            cursor: pointer;
            color: #333;
        }

        section#content {
            display: flex;
            flex-direction: row;
            gap: 10px;
            margin: 40px 0;
        }

        div.listings {
            flex: 0 1 240px;
            height: 600px;
            overflow-y: scroll;
        }

        div.listings .listing {
            cursor: pointer;
            padding: 1rem;
            border: 1px solid #ccc;
        }

        div.listings .listing+.listing {
            margin-top: 10px;
        }

        div.listings .listing:hover {
            background-color: #f5f5f5;
        }

        div.listings .listing.active {
            background-color: #154D80;
            color: #fff;
        }

        div.listings .listing--title {
            font-size: 16px;
            font-weight: bold;
            line-height: 1.2;
            margin-bottom: 5px;
        }

        div.listings .listing--subtitle {
            font-size: 16px;
        }

        div.listings .listing--meta {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            font-size: 14px;
        }

        div.listings .listing--type {}

        div.listings .listing--price {}

        .firstHeading {
            font-size: 20px;
            margin-bottom: 10px;
            line-height: 1.2;
            font-weight: bold;
        }

        .secondHeading {
            font-size: 18px;
            margin-bottom: 10px;
            line-height: 1.2;
            font-weight: bold;
        }

        .thirdHeading {
            font-size: 16px;
            margin-bottom: 10px;
            line-height: 1.2;
            font-weight: bold;
        }

        #bodyContent {
            font-size: 14px;
            line-height: 1.2;
        }

        #bodyContent img {
            width: 150px;
            height: auto;
            max-width: 200px;
            max-height: 300px;
            margin: 0 1rem 1rem 0;
            float: left;
        }

        #bodyContent .listing-more {
            display: inline-block;
            padding: 8px 12px;
            color: #fff;
            background-color: #154D80;
            border: none;
        }

        /* section.elementor-element-44b80298 {
            position: static !important;
        } */

        section.elementor-element-44b80298+section.elementor-element-44b80298 {
            display: none;
        }
    </style>
</section>

<?php get_footer();
?>