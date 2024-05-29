let map;
let markers = [];
let infoWindows = [];
let markerCluster;


function markersLatLng(tId, ID=true) {
  

  var allMarkers = document.getElementById(tId).value;

  var markerData = [];
  if(allMarkers.length > 0){
    var markersArray = JSON.parse(allMarkers);
  
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

alert('hi');

  // jQuery(document).on('keyup', '#search-by-text', function() {
  //   markers = [];
  //   markers.forEach(function(marker) {
  //     marker.setMap(null); 
  //   });
  //   var jsonArr =[];
   
  //   $('.propertylisting-content').each(function() {
  //       if ($(this).css('display') === 'block') {
  //           var dataJson = $(this).attr('data-json');
  //           try {
  //               var dataObj = JSON.parse(dataJson);
  //               jsonArr.push(dataObj);
                
                
  //           } catch (error) {
  //               console.error('Error parsing JSON:', error);
  //           }
  //       }
  //   });
    
  //   var newMarkerData = markersLatLng(JSON.stringify(jsonArr),false);

  //   if(newMarkerData.length > 0){
        
  //     var bounds = new google.maps.LatLngBounds();
  //     newMarkerData.forEach(function(markerInfo) {
    
  //       const contentString =
  //       '<div id="content">' +
  //       '<div id="bodyContent">' +
  //       '<div id="content-left">' +
  //       '<img src="' + markerInfo.img + '" />' +
  //       '<h3 class="thirdHeading">' + markerInfo.type + '</h3>' +
  //       '</div>'+
  //       '<div id="content-right">' +
  //       '<div id="siteNotice">' + '</div>' +
  //       '<h1 class="firstHeading">' + markerInfo.title + '</h1>' +
  //       '<h2 class="">' + markerInfo.subtitle.address_a + '</h2>' +
  //       '<h2 class="">' + markerInfo.subtitle.address_b + '</h2>' +
  //       '<h2 class="">' + markerInfo.subtitle.address_c + '</h2>' +
  //       '<p><a class="listing-more" href="' + markerInfo.link + '" target="_blank">View Listing</a></p>' +
  //       '</div>'+
  //       "</div>" +
  //       "</div>";
  //       var markerIcon = {
  //         url: markerInfo.imgIcon,
  //         // scaledSize: new google.maps.Size(38, 38) // Adjust the size as per your requirement
  //       };
        
  //       var marker = new google.maps.Marker({
  //         position: { lat: markerInfo.lat, lng: markerInfo.lng },
  //         map: map,
  //         title: markerInfo.title,
  //         icon : markerIcon,
  //       });
        
  //       marker.set('PID',markerInfo.mkid);
        
  //       markers.push(marker);
  //       const infoWindow = new google.maps.InfoWindow({
  //         content: contentString,
  //         ariaLabel: markerInfo.title,
  //       });
        
  //       infoWindows.push(infoWindow);
        
  //       marker.addListener("click", () => {
  //         infoWindows.forEach(item => item.close());
  //         infoWindow.open({
  //             anchor: marker,
  //             map,
  //         });
  //     });
        
  //       bounds.extend(marker.getPosition());
  //     });
    
  //     map.fitBounds(bounds);
      
  //   }
    
  // });
  


jQuery(document).on('change', '#ajax-marker-data', function() {


 
  var zmdata = markersLatLng('ajax-marker-data',true);
  if(zmdata.length > 0){

    markers = []; 
  var bounds = new google.maps.LatLngBounds();
  zmdata.forEach(function(markerInfo) {

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
    
    var zetmarkers = new google.maps.Marker({
      position: { lat: markerInfo.lat, lng: markerInfo.lng },
      map: map,
      title: markerInfo.title,
      icon : markerIcon,
    });
    
    zetmarkers.set('PID',markerInfo.mkid);
    
    markers.push(marker);
    const infoWindow = new google.maps.InfoWindow({
      content: contentString,
      ariaLabel: markerInfo.title,
    });
    
    infoWindows.push(infoWindow);
    
    zetmarkers.addListener("click", () => {
      infoWindows.forEach(item => item.close());
      infoWindow.open({
          anchor: zetmarkers,
          map,
      });
  });
    
    bounds.extend(zetmarkers.getPosition());
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
  
  var markerData = markersLatLng('marker_data_all',true);

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
