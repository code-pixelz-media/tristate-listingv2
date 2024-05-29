let map;
let markers = [];
let infoWindows = [];
let markerCluster;


function markersLatLng(tId) {
  var allMarkers = document.getElementById(tId).value;

  var markerData = [];
  if(allMarkers.length > 0){
    var markersArray = JSON.parse(allMarkers);
    console.log(typeof markersArray);
    if(markersArray){
      markersArray.forEach(function(input) {
        var latitude = parseFloat(input.lat);
        var longitude = parseFloat(input.long);
        var img = input.marker_image;
       
    
        if (!isNaN(latitude) && !isNaN(longitude) && isFinite(latitude) && isFinite(longitude)) {
          var title = input.popup_data.title ;
          if (title.length > 35) {
            title = title.substring(0, 23);
            title += " ...";
          }
          var subs = input.popup_data.sub_title;
          var image = input.popup_data.image ;
          var link = input.popup_data.link ?? '#';
          var listingType= input.popup_data.type;
          var markerDataObject = { mkid: input.post_id ,lat: latitude, lng: longitude, imgIcon:img, title: title , subtitle:subs, type: listingType , img:image ,link :link };
          markerData.push(markerDataObject);
        }
      });
  }
  }


  return markerData;
}


  jQuery(document).on('keyup', '#search-by-text', function() {

    let arr = [];
    $('.propertylisting-content').each(function() {
      if ($(this).css('display') === 'block') {
        var dataId = $(this).attr('data-pid');
        arr.push(dataId);
      }
    });

    markers.forEach(function(marker) {
      marker.setMap(null); 
    });
    
  });
  


jQuery(document).on('change', '#ajax-marker-data', function() {


  markers = []; 
  var newMarkerData = markersLatLng('ajax-marker-data');
  if(newMarkerData.length > 0){


  var bounds = new google.maps.LatLngBounds();
  newMarkerData.forEach(function(markerInfo) {

    const contentString =
    '<div id="content">' +
    '<div id="bodyContent">' +
    '<div id="content-left">' +
    '<img src="' + markerInfo.img + '" />' +
    '<h3 class="thirdHeading">' + markerInfo.type + '</h3>' +
    '</div>'+
    '<div id="content-right">' +
    '<div id="siteNotice">' + '</div>' +
    '<h1 class="firstHeading">' + markerInfo.title + '</h1>' +
    '<h2 class="">' + markerInfo.subtitle.address_a + '</h2>' +
    '<h2 class="">' + markerInfo.subtitle.address_b + '</h2>' +
    '<h2 class="">' + markerInfo.subtitle.address_c + '</h2>' +
    '<p><a class="listing-more" href="' + markerInfo.link + '" target="_blank">View Listing</a></p>' +
    '</div>'+
    "</div>" +
    "</div>";
    var markerIcon = {
      url: markerInfo.imgIcon,
      // scaledSize: new google.maps.Size(38, 38) // Adjust the size as per your requirement
    };
    
    var marker = new google.maps.Marker({
      position: { lat: markerInfo.lat, lng: markerInfo.lng },
      map: map,
      title: markerInfo.title,
      icon : markerIcon,
    });
    
    marker.set('PID',markerInfo.mkid);
    
    markers.push(marker);
    const infoWindow = new google.maps.InfoWindow({
      content: contentString,
      ariaLabel: markerInfo.title,
    });
    
    infoWindows.push(infoWindow);
    
    marker.addListener("click", () => {
      infoWindows.forEach(item => item.close());
      infoWindow.open({
          anchor: marker,
          map,
      });
  });
    
    bounds.extend(marker.getPosition());
  });

  map.fitBounds(bounds);
}
});

function initMap() {
  map = new google.maps.Map(document.getElementById("tristate-map"), {
    center: { lat: 40.844784, lng: -73.86483 },
    zoom: 9,
    mapTypeId: "terrain",
    // styles: mapTheme
  });
  
  var markerData = markersLatLng('marker_data_all');

  var bounds = new google.maps.LatLngBounds();
  if(markerData.length > 0){
  markerData.forEach(function(markerInfo) {
  
  
    const contentString =
    '<div id="content">' +
    '<div id="bodyContent">' +
    '<div id="content-left">' +
    '<img src="' + markerInfo.img + '" />' +
    '<h3 class="thirdHeading">' + markerInfo.type + '</h3>' +
    '</div>'+
    '<div id="content-right">' +
    '<div id="siteNotice">' + '</div>' +
    '<h1 class="firstHeading">' + markerInfo.title + '</h1>' +
    '<h2 class="">' + markerInfo.subtitle.address_a + '</h2>' +
    '<h2 class="">' + markerInfo.subtitle.address_b + '</h2>' +
    '<h2 class="">' + markerInfo.subtitle.address_c + '</h2>' +
    '<p><a class="listing-more" href="' + markerInfo.link + '" target="_blank">View Listing</a></p>' +
    '</div>'+
    "</div>" +
    "</div>";
    
    var markerIcon = {
      url: markerInfo.imgIcon,
      // scaledSize: new google.maps.Size(38, 38) 
    };
    
    var marker = new google.maps.Marker({
      position: { lat: markerInfo.lat, lng: markerInfo.lng },
      map: map,
      title: markerInfo.title,
      icon : markerIcon,
    });
    marker.set('PID',markerInfo.mkid);
    markers.push(marker);
    const infoWindow = new google.maps.InfoWindow({
      content: contentString,
      ariaLabel: markerInfo.title,
    });
    
    infoWindows.push(infoWindow);
    
    marker.addListener("click", () => {
      infoWindows.forEach(item => item.close());
      infoWindow.open({
          anchor: marker,
          map,
      });
  });
  
    bounds.extend(marker.getPosition());
  });
  
 
  // markerCluster = new MarkerClusterer(map, markers, {
  //   imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
  // });  
map.fitBounds(bounds);
  }
}
