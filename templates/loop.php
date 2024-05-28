<?php



$ID             = $args['ID'];
$buildout_lease = meta_of_api_sheet($ID ,'lease');
$buildout_sale  =  meta_of_api_sheet($ID, 'sale');
$buildout_id    = (int) meta_of_api_sheet($ID, 'id');
$title          = meta_of_api_sheet($ID, 'sale_listing_web_title');
$subtitle       = implode(', ', array(meta_of_api_sheet($ID, 'city'), meta_of_api_sheet($ID, 'county'), meta_of_api_sheet($ID, 'state')));
$badges         = array(
                    'use' => meta_of_api_sheet($ID, 'use'),
                    'type' =>  ($buildout_lease == '1' && $buildout_sale == '1') ? 'for Lease' :
                    (($buildout_lease == '1') ? 'for Lease' :
                    (($buildout_sale == '1') ? 'for Sale' : false)),
                    'price_sf' => meta_of_api_sheet($ID, 'price_sf'),
                    'commission' => meta_of_api_sheet($ID, 'commission')
                );
$_price_sf      = meta_of_api_sheet($ID, 'price_sf');
$_price_sf      = preg_replace('/\.[0-9]+/', '', $_price_sf);
$_price_sf      = (int) preg_replace('/[^0-9]/', '', $_price_sf);

$min_size       = get_post_meta($ID, '_gsheet_min_size_fm',true);
$max_size       = get_post_meta($ID, '_gsheet__max_size_fm',true);

$zoning         = meta_of_api_sheet($ID, 'zoning');
$key_tag        = meta_of_api_sheet($ID, 'key_tag');
$_agent         = meta_of_api_sheet($ID, 'listing_agent');

$bo_price       = meta_of_api_sheet($ID, 'sale_price_dollars');
$price          = meta_of_api_sheet($ID, 'monthly_rent');

$_price         = preg_replace('/\.[0-9]+/', '', $price);

$_price         = (int) preg_replace('/[^0-9]/', '', $_price);

$neighborhood   = meta_of_api_sheet($ID, 'neighborhood');
$vented         = meta_of_api_sheet($ID, 'vented');
$borough        = meta_of_api_sheet($ID, 'borough');
$streets        = meta_of_api_sheet($ID, 'cross_street') ;
$state          = meta_of_api_sheet($ID, 'state');
$zip            = meta_of_api_sheet($ID,'zip');
$city           = meta_of_api_sheet($ID,'city');
$address        = meta_of_api_sheet($ID, 'address');
$county         = meta_of_api_sheet($ID, 'county');
$country_code   = meta_of_api_sheet($ID,'country_code');
$lease_conditions = meta_of_api_sheet($ID,  'lease_description');
$title          = meta_of_api_sheet($ID, 'sale_listing_web_title');
$subtitle       = implode(', ', array_filter(array($streets, $city, $state, $zip), 'strlen'));
$address_c      = implode(', ', array_filter(array($county, $country_code, ), 'strlen'));
$image          = false;
if ($photos = meta_of_api_sheet($ID, 'photos')) {
    $photo = reset($photos);
    $image = $photo->formats->thumb ?? '';
}


$sale_marker = TRISTATECRLISTING_PLUGIN_URL . '/assets/img/sale.webp';
$lease_marker = TRISTATECRLISTING_PLUGIN_URL . '/assets/img/lease.webp';

$marker_img = ($buildout_lease == '1' && $buildout_sale == '1') ? $lease_marker :
              (($buildout_lease == '1') ? $lease_marker :
              (($buildout_sale == '1') ? $sale_marker : false));

$type = ($buildout_lease == '1' && $buildout_sale == '1') ? 'FOR LEASE' :
        (($buildout_lease == '1') ? 'FOR LEASE' :
        (($buildout_sale == '1') ? 'FOR SALE' : false));

if(empty($_agent)){
    $buildout_agent = get_post_meta($ID, '_buildout_broker_id', true);
    if(!empty($buildout_agent)){
        $query = $wpdb->prepare(
            "SELECT pm.post_id 
             FROM $wpdb->postmeta pm
             INNER JOIN $wpdb->posts p ON pm.post_id = p.ID
             WHERE pm.meta_key = 'user_id' 
               AND pm.meta_value = %s
               AND p.post_type = 'brokers'",
            $buildout_agent
        );
        $agent_id = $wpdb->get_var($query);
        
       $_agent = get_the_title($agent_id);
    }
        
}

$meta_vrs = [
    'City' => $city,
    'State' => $state,
    'Min Size' => $min_size,
    'Max Size' => $max_size,
    'Zoning' => $zoning,
    'Key Tag' => $key_tag,
    'Listing Agent' => $_agent,
    'Vented' => $vented,
    'Borough' => $borough,
    'Neighborhood' => $neighborhood,
    'Zip Code' => $zip
];


if (!empty($bo_price) && $bo_price !== '0' && $bo_price !== 0) {

    $displaying_price = '$' . number_format($bo_price);

} elseif (!empty($_price_sf) && $_price_sf !== '0' && $_price_sf !== 0) {

    $displaying_price = '$' . number_format($_price_sf);

} else {

    $displaying_price = 'Call For Price';
}

$desc = '';

if ($buildout_lease == '1') {
    $desc = meta_of_api_sheet($ID,'lease_description');

} elseif ($buildout_sale == '1') {

    $desc = meta_of_api_sheet($ID, 'sale_description');
    
} elseif($buildout_lease == '1' && $buildout_sale == '1') {
   $desc = meta_of_api_sheet($ID, 'sale_description');
}
$type_imp = get_post_meta($ID,'_import_from',true);

$selected_array = isset($_POST['selected_type']) ? $_POST['selected_type'] : array();

$selected_string = implode(', ', $selected_array);

if(!empty($selected_array)){
    if($buildout_lease == '1' && $buildout_sale == '1' ){
        if($selected_string  == 'for Lease'){
           $desc=  meta_of_api_sheet($ID,'lease_description');
        }
        if($selected_string  == 'for sale'){
        
            $desc=  meta_of_api_sheet($ID,'sale_description');
        }
    }    

}

$full_desc = $desc; 
$trimmed_desc = wp_trim_words($desc, 10, '...&nbsp<span class="desc-more">More</span>');


?>

<div class="propertylisting-content" data-id="<?php echo $buildout_id ?>">
    <div class="plc-top">
        <h2><?php echo esc_html(get_the_title()); ?></h2>
        <h4><?php echo $subtitle; ?></h4>
        <h2 style="displat:none"><?php //echo $_agent ?></h2>
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
                                    if ($buildout_lease == '1' && $buildout_sale == '1') {
                                        if($selected_string=='for Lease') {
                                  
                                          $value= $selected_string;
                                        }
                                        if($selected_string=='for Sale') {
                               
                                          $value= $selected_string;
                                        }
                                       
                                    }
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
            <?php if(!empty($desc)) : ?>
            <div class="description-container">
                <div class="trimmed-desc">
                    <?php echo $trimmed_desc; ?>
                </div>
                <div class="full-desc" style="display:none;">
                    <?php echo $full_desc; ?> <span class="desc-less"> Less</span>
                </div>
            </div>
            <?php endif; ?>
            </ul>
            <ul class="ul-content ul-features">
                <?php foreach ($meta_vrs as $k => $v) {
                    if($k == 'Key Tag'){
                        echo !empty($v) ? ' <li><p>' . $k . ': <span data-text="'.$v.'" class="key-show">Show</span>
                        <div class="tag-container"></div></p></li>' : '';
                    }else{
                        
                        echo !empty($v) ? ' <li><p>' . $k . ': <span>' . $v . '</span></p></li>' : '';
                    }
                    
                } ?>
            </ul>
        </div>
    </div>
    <div class="plc-bottom">
        <p class="price"><?php echo 'Price: ' . $displaying_price; ?></p>
        <a href="<?php the_permalink(); ?>" target="_blank" class="MuiButton-colorPrimary"> More Info </a>
    </div>
</div>