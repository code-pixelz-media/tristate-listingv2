
let map;
let markers = [];
let infoWindows = [];
let markerCluster;


function markersLatLng(tId , ID) {

  if(ID){
    var allMarkers = document.getElementById(tId).value;
  }else{
    var allMarkers = tId;
  }

  var markersArray = JSON.parse(allMarkers);
  var markerData = [];
  if(markersArray){
    markersArray.forEach(function(input) {
      var latitude = parseFloat(input.lat);
      var longitude = parseFloat(input.long);
      var img = input.marker_image;
      var postID = input.post_id;
                       
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
        var markerDataObject = { lat: latitude, lng: longitude, imgIcon:img, title: title , subtitle:subs, type: listingType , pid:postID, img:image ,link :link };
        markerData.push(markerDataObject);
      }
    });
  }


  return markerData;
}

function get_visible_properties(){

  var jsonArr=[] ;
  jQuery(".propertylisting-content:visible").each(function() {
   
            var dataJson = $(this).attr('data-json');
            var dataObj = JSON.parse(dataJson);
            if(dataObj){
              jsonArr.push(dataObj);
            }
          
      
  });
  

  return {
    'mapdata': jsonArr,
   
  }
  
  
  
}


function get_markerData(fromId=true ,id){
  markers.forEach(function(marker) {
    marker.setMap(null); 
  });
  markers = []; 
  var allData = get_visible_properties(),
  markerData = fromId ? markersLatLng(id,true) : markersLatLng(JSON.stringify(allData.mapdata),false);
  console.log(typeof allData);
  if(markerData){

 
    var bounds = new google.maps.LatLngBounds();
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
        scaledSize: new google.maps.Size(38, 38) // Adjust the size as per your requirement
      };
      
      var marker = new google.maps.Marker({
        position: { lat: markerInfo.lat, lng: markerInfo.lng },
        map: map,
        title: markerInfo.title,
        icon : markerIcon,
        pid:markerInfo.pid
      });
      
      
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
}



function initMap() {
  map = new google.maps.Map(document.getElementById("tristate-map"), {
    center: { lat: 40.844784, lng: -73.86483 },
    zoom: 9,
    mapTypeId: "terrain",
    // styles: mapTheme
  });
  get_markerData(false);
  // get_markerData(true,'marker_data_all');

}

jQuery(document).on('mouseenter','.propertylisting-content',function(){
  if(jQuery(document).find('.filter-wrapper').hasClass('ts-state-page')){
      const pid = jQuery(this).data('pid');
      const marker = markers.find(m => m.pid === pid);
      if (marker) {
        new google.maps.event.trigger( marker, 'click' );
      }
  }

})


//jQuery('.propertylisting-content:visible').on('mouseleave', function() {
  jQuery(document).on('mouseleave','.propertylisting-content',function(){
    if(jQuery(document).find('.filter-wrapper').hasClass('ts-state-page')){
      infoWindows.forEach(item => item.close());
    }
});