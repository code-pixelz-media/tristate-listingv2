<?php 
function get_usesname_by_propertyID($propertyTypeID)
{

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

function get_usesname_subtype_by_id($subtypeID)
{
    $property_uses = [
      "Office" =>[
        "101" => "Office Building",
        "102" => "Creative/Loft",
        "103" => "Executive Suites",
        "104" => "Medical",
        "105" => "Institutional/Governmental",
        "106" => "Office Warehouse",
        "107" => "Office Condo",
        "108" => "Coworking",
        "109" => "Lab",
      ],
      "Retail" =>[
        "201" => "Street Retail",
        "202" => "Strip Center",
        "203" => "Free Standing Building",
        "204" => "Regional Mall",
        "205" => "Retail Pad",
        "206" => "Vehicle Related",
        "207" => "Outlet Center",
        "208" => "Power Center",
        "209" => "Neighborhood Center",
        "210" => "Community Center",
        "211" => "Specialty Center",
        "212" => "Theme/Festival Center",
        "213" => "Restaurant",
        "214" => "Post Office",
        "215" => "Retail Condo",
        "216" => "Lifestyle Center",
      ],
      
      "Industrial" =>[
        "301" => "Manufacturing",
        "302" => "Warehouse/Distribution",
        "303" => "Flex Space",
        "304" => "Research & Development",
        "305" => "Refrigerated/Cold Storage",
        "306" => "Office Showroom",
        "307" => "Truck Terminal/Hub/Transit",
        "308" => "Self Storage",
        "309" => "Industrial Condo",
        "310" => "Data Center",
      ],
      
      "Land" => [
        "501" => "Office",
        "502" => "Retail",
        "503" => "Retail-Pad",
        "504" => "Industrial",
        "505" => "Residential",
        "506" => "Multifamily",
        "507" => "Other",
      ],
      
      "Multifamily" => [
        "601" => "High-Rise",
        "602" => "Mid-Rise",
        "603" => "Low-Rise/Garden",
        "604" => "Government Subsidized",
        "605" => "Mobile Home Park",
        "606" => "Senior Living",
        "607" => "Skilled Nursing",
        "608" => "Single Family Rental Portfolio",
      ],
      
      "Special Purpose" => [
        "701" => "School",
        "702" => "Marina",
        "703" => "Other",
        "704" => "Golf Course",
        "705" => "Church",
      ],
      
      "Hospitality" => [
        "801" => "Full Service",
        "802" => "Limited Service",
        "803" => "Select Service",
        "804" => "Resort",
        "805" => "Economy",
        "806" => "Extended Stay",
        "807" => "Casino"
      ]


    ];

    $result = array_filter($property_uses, function($subtypes) use ($subtypeID) {
      return array_key_exists($subtypeID, $subtypes);
  });

  return !empty($result) ? array_keys($result)[0] : false;
}


function get_uses_name_subtype_by_id($subtypeID)
{
    $property_uses = [
      
        "101" => "Office Building",
        "102" => "Creative/Loft",
        "103" => "Executive Suites",
        "104" => "Medical",
        "105" => "Institutional/Governmental",
        "106" => "Office Warehouse",
        "107" => "Office Condo",
        "108" => "Coworking",
        "109" => "Lab",
        "201" => "Street Retail",
        "202" => "Strip Center",
        "203" => "Free Standing Building",
        "204" => "Regional Mall",
        "205" => "Retail Pad",
        "206" => "Vehicle Related",
        "207" => "Outlet Center",
        "208" => "Power Center",
        "209" => "Neighborhood Center",
        "210" => "Community Center",
        "211" => "Specialty Center",
        "212" => "Theme/Festival Center",
        "213" => "Restaurant",
        "214" => "Post Office",
        "215" => "Retail Condo",
        "216" => "Lifestyle Center",
        "301" => "Manufacturing",
        "302" => "Warehouse/Distribution",
        "303" => "Flex Space",
        "304" => "Research & Development",
        "305" => "Refrigerated/Cold Storage",
        "306" => "Office Showroom",
        "307" => "Truck Terminal/Hub/Transit",
        "308" => "Self Storage",
        "309" => "Industrial Condo",
        "310" => "Data Center",
        "501" => "Office",
        "502" => "Retail",
        "503" => "Retail-Pad",
        "504" => "Industrial",
        "505" => "Residential",
        "506" => "Multifamily",
        "507" => "Other",
        "601" => "High-Rise",
        "602" => "Mid-Rise",
        "603" => "Low-Rise/Garden",
        "604" => "Government Subsidized",
        "605" => "Mobile Home Park",
        "606" => "Senior Living",
        "607" => "Skilled Nursing",
        "608" => "Single Family Rental Portfolio",
        "701" => "School",
        "702" => "Marina",
        "703" => "Other",
        "704" => "Golf Course",
        "705" => "Church",
        "801" => "Full Service",
        "802" => "Limited Service",
        "803" => "Select Service",
        "804" => "Resort",
        "805" => "Economy",
        "806" => "Extended Stay",
        "807" => "Casino"
    ];

    return $property_uses[$subtypeID] ?? false;
}


function get_usename_subtype($property_subtype_id)
{

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


