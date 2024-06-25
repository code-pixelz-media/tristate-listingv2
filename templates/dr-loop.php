<?php

$ID             = $args['ID'];
$buildout_lease = meta_of_api_sheet($ID ,'lease');
$buildout_sale  =  meta_of_api_sheet($ID, 'sale');
$buildout_id    = (int) meta_of_api_sheet($ID, 'id');
$title          = meta_of_api_sheet($ID, 'sale_listing_web_title');
$subtitle       = implode(', ', array(meta_of_api_sheet($ID, 'city'), meta_of_api_sheet($ID, 'county'), meta_of_api_sheet($ID, 'state')));
$property_use_type = get_post_meta($ID,'_buildout_property_type_id',true);
$property_use_name = get_usesname_by_propertyID($property_use_type);
$badges         = array(
                    'use' =>$property_use_name ? $property_use_name : '',
                    'type' =>  ($buildout_lease == '1' && $buildout_sale == '1') ? 'for Lease' :
                    (($buildout_lease == '1') ? 'for Lease' :
                    (($buildout_sale == '1') ? 'for Sale' : false)),
                    'commission' => meta_of_api_sheet($ID, 'commission')
                );
                
$_price_sf      = meta_of_api_sheet($ID, 'price_sf');
$_price_sf      = preg_replace('/\.[0-9]+/', '', $_price_sf);
$_price_sf      = (int) preg_replace('/[^0-9]/', '', $_price_sf);

$min_size       = get_post_meta($ID, '_gsheet_min_size_fm',true);
$max_size       = get_post_meta($ID, '_gsheet__max_size_fm',true);

//max and min values for for lease properties sf
$max_lease_sf_value       = get_post_meta($ID, '_gsheet_price_sf',true);
$max_lease_sf = (float) preg_replace('/[^0-9.]/', '', $max_lease_sf_value);


$zoning         = meta_of_api_sheet($ID, 'zoning');
$key_tag        = meta_of_api_sheet($ID, 'key_tag');
$_agent         = meta_of_api_sheet($ID, 'listing_agent');

$bo_price       = meta_of_api_sheet($ID, 'sale_price_dollars');

$price          = meta_of_api_sheet($ID, 'monthly_rent');
$_price         = preg_replace('/\.[0-9]+/', '', $price);
$_price         = (int) preg_replace('/[^0-9]/', '', $_price);

$agents = get_post_meta($ID,'_buildout_listing_agent', true);

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

// check the property type
$type = ($buildout_lease == '1' && $buildout_sale == '1') ? 'FOR LEASE' :
        (($buildout_lease == '1') ? 'FOR LEASE' :
        (($buildout_sale == '1') ? 'FOR SALE' : false));
        
//Checking the type of the properties 
$formatted_type = str_replace(' ', '', trim(strtolower($type)));

$monthly_rent_lease = false;
//if the property is of forlease type
if($formatted_type == 'forlease'){
    $monthly_rent_lease = !empty($_price) ? 'Monthly rent: $'. number_format($_price) : false;
    $new_price = !empty($_price_sf  ) ? 'Price per SF: $'. number_format($_price_sf) : false;
    
//if the property is of forsale type    
}else if($formatted_type == 'forsale'){

    $sale_price = meta_of_api_sheet($ID, 'sale_price_dollars');
    $new_price = !empty($sale_price) ? 'Price : $'. number_format($sale_price) : false;
// if the price is not found
}else {
    $new_price = false;
}


/* if(empty($_agent)){
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
        
} */
 

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

$_buildout_second_broker_id =  get_post_meta($ID, '_buildout_second_broker_id', true);

   // $buildout_agent = get_post_meta($ID, '_buildout_second_broker_id', true);
    if(!empty($_buildout_second_broker_id)){
        $query = $wpdb->prepare(
            "SELECT pm.post_id 
             FROM $wpdb->postmeta pm
             INNER JOIN $wpdb->posts p ON pm.post_id = p.ID
             WHERE pm.meta_key = 'user_id' 
               AND pm.meta_value = %s
               AND p.post_type = 'brokers'",
            $_buildout_second_broker_id
        );
        $agent_id = $wpdb->get_var($query);
        if(!empty($_agent)){
       $_agent .= ', '.get_the_title($agent_id);
        }else {
            $_agent =get_the_title($agent_id);
        }
    }
        

    $_buildout_third_broker_id =  get_post_meta($ID, '_buildout_third_broker_id', true);

    // $buildout_agent = get_post_meta($ID, '_buildout_second_broker_id', true);
     if(!empty($_buildout_third_broker_id)){
         $query = $wpdb->prepare(
             "SELECT pm.post_id 
              FROM $wpdb->postmeta pm
              INNER JOIN $wpdb->posts p ON pm.post_id = p.ID
              WHERE pm.meta_key = 'user_id' 
                AND pm.meta_value = %s
                AND p.post_type = 'brokers'",
             $_buildout_third_broker_id
         );
         $agent_id = $wpdb->get_var($query);
         if(!empty($_agent)){
        $_agent .= ', '.get_the_title($agent_id);
         }else {
             $_agent =get_the_title($agent_id);
         }
     }


if(!empty($state)){
    
    if(strpos(trim(strtolower($state)) , 'newj') ){
        $state = 'NJ';
    }
    
    if(strpos(trim(strtolower($state)) , 'penn') ){
        $state = 'PA';
    }
    
    if($state == "PENNSYLVANIA"){
        $state = 'PA';
    }
    
    if(trim($state) == 'NEW JERSERY'){
        $state = 'NJ';
    }
    
    if(strpos(strtolower(trim($state)) , 'penn')){
        $state = 'PA';
    }
    
    if(strpos(strtolower(trim($state)) , 'newj')){
        $state = 'NJ';
    }
}

if(!empty($city)){
        
    if(preg_match("/\bPhil?\b/i" , trim($city))){
        $city = "Philadelphia";
    }
    
    if(strtolower(trim($city)) == 'phiadelphia'){
        $city = "Philadelphia";
    }
    if(strtolower(trim($city)) == 'philadelphia'){
        $city = "Philadelphia";
    }
    
    if(strtolower(trim($city)) == 'phildelphia'){
        $city = "Philadelphia";
    }
    
    if(strpos(trim(strtolower($city)) , 'york') ){
        $city = 'New York';
    }
        
}

if(!empty($neighborhood)){


    
    if(strtolower(trim($neighborhood)) == 'south phildelpha'){
        $neighborhood = "South Philadelphia";
    }
    
    if(strtolower(trim($neighborhood)) == 'south philadelpha'){
        $neighborhood = "South Philadelphia";
    }
    if(strtolower($neighborhood) == 'south philly'){
        $neighborhood = "South Philadelphia";
    }
    if(trim($neighborhood) == "#VALUE!"){
       unset($neighborhood);
    }
}

if(!empty($badges['use'])){
    
     if(strpos($badges['use'],'mixed')){
        $badges['use'] = 'Mixed Use';
     }
     
     if(trim($badges['use']) == "Mixed-use"){
        $badges['use'] = 'Mixed Use';
     }
     
     if(strtolower(trim($badges['use'])) == "retail/ restaurant "){
        $badges['use'] = 'Retail/Restaurant';
     }
     if (preg_match("/Retail\/ Restaurant/i",  $badges['use'])) {
        $badges['use'] = 'Retail/Restaurant';
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
    'Zip Code' => $zip,
];

$new_max_p_sf= preg_replace('/\$?(\d+)\.\d{2}/', '$1', $_price_sf);
if($_price_sf !=='0' && !empty($_price_sf))  $max_p_val[] = (int) $new_max_p_sf;


$desc = '';

if ($buildout_lease == '1') {
    $desc = meta_of_api_sheet($ID,'lease_description');

} elseif ($buildout_sale == '1') {

    $desc = meta_of_api_sheet($ID, 'sale_description');
    
} elseif($buildout_lease == '1' && $buildout_sale == '1') {
   $desc = meta_of_api_sheet($ID, 'lease_description');
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

$lat = get_post_meta($ID, '_buildout_latitude', true);
$long = get_post_meta($ID, '_buildout_longitude', true);

 $m_d = tristate_get_marker_data($ID);
 if($buildout_lease == '1' && $_price !==0 && !empty($_price) ){
        
        $displaying_price ='$' . number_format($_price).'/month';
 }
$json_data = json_encode($m_d);
$date_created = get_post_meta($ID,'_buildout_created_at',true);
$date_upd = get_post_meta($id,'_buildout_updated_at',true);


global $wpdb;
$space_tbl= $wpdb->prefix . 'lease_spaces';
$l_meta = get_post_meta($ID,'lease_space_table_id',true);
?>

<div 
    class="propertylisting-content" 
    data-pid="<?php echo $ID?>" 
    data-lat="<?php echo $lat; ?>"  
    data-lng="<?php echo $long; ?>"  
    data-id="<?php echo $buildout_id;?>"
    data-json = "<?php echo htmlspecialchars($json_data, ENT_QUOTES, 'UTF-8');?>" 
    data-price="<?php echo esc_attr(!empty($bo_price) ? $bo_price : '0'); ?>"
    data-pricesf="<?php echo esc_attr(!empty($max_lease_sf) ? $max_lease_sf : '0'); ?>"
    data-minsize="<?php echo esc_attr(!empty($min_size) ? $min_size : '0');?>"
    data-maxsize="<?php  echo esc_attr(!empty($max_size) ? $max_size : '0');?>"
    data-dateupdated="<?php echo strtotime($date_upd); ?>",
    data-datecreated="<?php echo strtotime($date_created); ?>"
    data-title = "<?php echo esc_html(get_the_title()); ?>"
    data-monthly-rent = "<?php echo !empty($_price) ? $_price :'0'; ?>"
>

<?php
if($args['state']) { ?>
<a href="<?php the_permalink(); ?>">
<?php } ?>
<div class="lisiting-feature-img" style="display:none;">
<img src="<?php echo $image; ?>" alt=""> 
<span class="listing-type-state <?php echo $type=='FOR LEASE' ? 'state-lease' : 'state-sale' ?>"><?php echo $type;?></span>
</div>
<input type="hidden" name="get_properties_id" id="get_properties_id"  value="<?php echo $ID; ?>">
    <div class="plc-top">
    <?php if($args['state']) { ?>
    <div id="state-layout-head">
        <h2 class="lisiitng__title" id="state_property_title" style="display:none"><a href="<?php the_permalink(); ?>" target="_blank" class="MuiButton-colorPrimary"> <?php echo esc_html(get_the_title()); ?></a>  </h2> 
        <h2 class="lisiitng__state_title" >
            <?php echo esc_html(get_the_title()); ?>
        </h2>
    </div>

    <?php } else { ?>
        
        <h2 class="lisiitng__title_state"><?php echo esc_html(get_the_title()); ?></h2> 
    <?php } ?>
        <h4><?php echo $subtitle; ?></h4>
     
        <div class="css-ajk2hm">
            <ul class="ul-buttons">
                <?php
                if (!empty($badges)) {
                    foreach ($badges as $key => $value) {
                        if (!empty($value)) {
                            switch ($key) {
                                case 'use':
                                    $class = 'tri_use bg-blue';
                                    break;
                                case 'type':
                                    if($value == 'for Lease'){
                                        $class = 'tri_for_lease btn-forlease';
                                    }elseif($value== 'for Sale'){
                                        $class = 'tri_for_sale btn-forsale';
                                    }
                                    // $class ='';
                                    if ($buildout_lease == '1' && $buildout_sale == '1') {
                                        if($selected_string=='for Lease') {
                                  
                                          $value= $selected_string;
                                          $class = 'tri_for_lease btn-forlease';
                                        }
                                        if($selected_string=='for Sale') {
                               
                                          $value= $selected_string;
                                          $class = 'tri_for_sale btn-forsale';
                                        }
                                       
                                    }
                                    break;
                                case 'price_sf':
                                    $class = 'tri_price_sf bg-yellow';
                                    break;
                                case 'commission':
                                    $class = 'tri_commission bg-red';
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
            <?php 
            if(!empty($l_meta)){
               echo '<div class="trimmed-unit">';
                $counter =1;
                foreach($l_meta as $l){
                    $query = $wpdb->prepare(
                        "SELECT * FROM $space_tbl WHERE id = %s AND deal_status=%s",
                        $l, '1'
                    );
                    $lsp = $wpdb->get_row($query, ARRAY_A);
    
                    $title = $lsp['lease_address'];
                    $price_units = $lsp['lease_rate_units'];
                    $price = $lsp['lease_rate'];
                    
                    $size_unit = $lsp['space_size_units'];
                    $size = $lsp['size_sf'];
                    
                   
                
                ?>
                <div id="trimmed-container" class="trimmed-unit-"<?php echo $counter ?>>
                    <h4 class="lease-space-title" style="cursor:pointer;">Unit <?=$counter ?><span class="trimmed-control">+</span></h4>
                    <ul class="ul-content ul-features" style="display:none">
                        <?php 
                        if(!empty($title)) : 
                            echo "<li><p>Title: <span>$title</span></p></li>";
                         endif; 
                         
                        if(!empty($price)) :
                            
                            if($price_units == 'dollars_per_sf_per_year'){
                                $postfix = '/SF per year';
                            }else if($price_units == 'dollars_per_sf_per_month'){
                                $postfix = '/SF per month';
                            }elseif($price_units == 'dollars_per_month'){
                                $postfix = '/per month';
                            }else{
                                $postfix = '';
                            }
                            $price_with_postfix = '$'.number_format($price).$postfix;
                            echo "<li><p>Price: <span>$price_with_postfix</span></p></li>";
                        endif;
                        
                        if($size) :
                            if($size_unit == 'sf'){
                                $pfix = 'SF';
                            }else{
                                $pfix = '';
                            }
                            $size_pfix = $size . ' ' . $pfix;
                            echo "<li><p>Size: <span>$size_pfix</span></p></li>";
                        endif;
                        
                        if(empty($title) && empty($price) && empty($size)){
                            echo "<li><p>N/A</li>";
                        }
                        ?>
                    </ul>
                </div>
                <?php
                $counter++;
                }
                echo '</div>';  
            }
            ?>
            <ul class="ul-content ul-features">   
    <?php foreach ($meta_vrs as $k => $v) {
        if($k == 'Key Tag'){
            echo !empty($v) ? ' <li><p>' . $k . ': <span id="tri_' . strtolower(str_replace(' ', '_', $k)) . '" data-text="'.$v.'"class="key-show">Show</span>
            <div class="tag-container"></div></p></li>' : '';
        } else {
            echo !empty($v) ? ' <li><p>' . $k . ': <span id="tri_' . strtolower(str_replace(' ', '_', $k)) . '" >' . $v . '</span></p></li>' : '';
        }
    } ?>
</ul>
        </div>
    </div>
    
    <div class="plc-bottom">
    <?php if($monthly_rent_lease) : ?>
        <p class="font-13 color-red"><?php echo $monthly_rent_lease; ?></p>
    <?php endif; ?>
    <!-- Starting P for Price -->
        <p class="price">
            <?php if($new_price): 
            
                echo $new_price;
            
            else: 
                 _e('Price: Call For Price','tristatecr');
            endif 
            ?>
        </p>
    <!-- p for price ends  -->
        <a href="<?php the_permalink(); ?>" target="_blank" class="MuiButton-colorPrimary"> More Info </a>
    </div>
    <?php if($args['state']) { ?>
</a>
<?php } ?>
</div>



