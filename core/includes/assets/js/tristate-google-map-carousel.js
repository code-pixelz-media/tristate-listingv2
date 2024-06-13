
let map;
let markers = [];
let infoWindows = [];
let markerCluster;

var preV = '<path fill="currentColor" d="M31.7 239l136-136c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9L127.9 256l96.4 96.4c9.4 9.4 9.4 24.6 0 33.9L201.7 409c-9.4 9.4-24.6 9.4-33.9 0l-136-136c-9.5-9.4-9.5-24.6-.1-34z"></path>';
var nxT = ''

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

function getCarouselInfowindowHtml(markersInfo) {
  let carouselItems = markersInfo.map((markerInfo, index) => `
      <div class="carousel-item ${index === 0 ? 'active' : ''}">
          <img src="${markerInfo.img}" />
          <div class="carousel-caption">
              <h3>${markerInfo.type}</h3>
              <h1>${markerInfo.title}</h1>
              <h2>${markerInfo.subtitle.address_a}</h2>
              <h2>${markerInfo.subtitle.address_b}</h2>
              <h2>${markerInfo.subtitle.address_c}</h2>
              <p><a class="listing-more" href="${markerInfo.link}" target="_blank">View Listing</a></p>
              <p class="carousel-count">${index + 1} of ${markersInfo.length}</p>
          </div>
      </div>
  `).join('');

  return `
      <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
          <div class="carousel-inner">
              ${carouselItems}
          </div>
          <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="sr-only">Previous</span>
          </a>
          <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="sr-only">Next</span>
          </a>
      </div>
  `;
}


function get_infowindow_html(markerInfo){
  const contentString =
  '<div id="content" data-mkid="230">' +
  '<div id="bodyContent">' +
  '<span class="prev"><i class="fa-solid fa-arrow-left"></i></span>'+
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
  '<span class="next">&#8594;</span>'+
  "</div>" +
  "</div>";
  
  return contentString;
}


function get_markerData(fromId=true ,id){
  markers.forEach(function(marker) {
    marker.setMap(null); 
  });
  markers = []; 
  var allData = get_visible_properties(),
  markerData = fromId ? markersLatLng(id,true) : markersLatLng(JSON.stringify(allData.mapdata),false);
 
  if(markerData){

 
    var bounds = new google.maps.LatLngBounds();
    markerData.forEach(function(markerInfo) {
  

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
      marker.addListener("click", (ev) => {
        var clickedPosition = marker.getPosition(); // Get the clicked position
        var overlappingMarkers = findOverlappingMarkers(clickedPosition);
    
        let infoWindowContent;
        if (overlappingMarkers.length > 1) {
            // Get info window content for carousel
            let markerInfos = overlappingMarkers.map(m => markerData.find(data => data.pid === m.pid));
            infoWindowContent = getCarouselInfowindowHtml(markerInfos);
        } else {
            // Get info window content for a single marker
            infoWindowContent = get_infowindow_html(markerInfo);
        }
    
        const infoWindow = new google.maps.InfoWindow({
            content: infoWindowContent,
            ariaLabel: markerInfo.title,
        });
    
        infoWindows.push(infoWindow);
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

function findOverlappingMarkers(position) {
  return markers.filter(marker => 
      marker.getPosition().equals(position)
  );
}

function findClosestMarkers(position) {
  var closestMarkers = [];
  var closestDistance = Number.MAX_VALUE;

  markers.forEach(function(marker) {
      var distance = google.maps.geometry.spherical.computeDistanceBetween(position, marker.getPosition());
      if (distance < closestDistance) {
          closestMarkers = [marker];
          closestDistance = distance;
      } else if (distance === closestDistance) {
          closestMarkers.push(marker);
      }
  });

  return closestMarkers;
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