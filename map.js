jQuery(document).ready(function($){

var _map_id = 'map';
var _map = $('#' + _map_id);

if (!_map.length)
    return false;

request = {
    QueryString : function(item){
        var svalue = location.search.match(new RegExp("[\?\&]" + item + "=([^\&]*)(\&?)","i"));
        return svalue ? svalue[1] : svalue;
    }
}

var options;

if (request.QueryString('embed') == null) {
    options = {
        center: new google.maps.LatLng(maptheme.lat, maptheme.lng),
        zoom: 5,
        maxZoom: 17,
        streetViewControl: false,
        scrollwheel: false,
    };
} else {
    options = {
        center: new google.maps.LatLng(maptheme.lat, maptheme.lng),
        zoom: 4,
        maxZoom: 17,
        streetViewControl: false,
    };
}

var map = new google.maps.Map(document.getElementById(_map_id), options);
var hovercard = new google.maps.InfoWindow({});

google.maps.event.addDomListener(window, 'load', function(e) {

    for (u in maptheme.users) {
        var user = maptheme.users[u];
        var image = new google.maps.MarkerImage(
            maptheme.imgbase + 'marker.png',
            null,
            new google.maps.Point(0,0),
            new google.maps.Point(45, 39)
        );
        var marker = new google.maps.Marker({
            position: new google.maps.LatLng(user.lat, user.lng),
            map: map,
            icon: image
        });
        marker.set('user', user);
        google.maps.event.addListener(marker, 'click', function(){
            hovercard.setContent('<div id="loading" style="color:#444">Buscando...</div>');
            hovercard.open(map, this);
            var query_user = this.user;
            $.ajax({
                'url': 'http://en.gravatar.com/' + query_user.gravatar + '.json',
                'dataType': 'jsonp',
                'timeout': 4000,
                'complete': function(xhr, status) {

                    if (status != 'success') {

                        hovercard.setContent(
                            '<div class="hovercard" style="width:300px; color:#444;">'
                                + '<span class="display-name" style="display:block; font-weight:bold; font-size:1.2em;">' + query_user.display_name + '</span>'
                            + '</div>'
                        );

                    } else {

                        var gravatar = xhr.responseJSON.entry[0];

                        var about;
                        if (gravatar.aboutMe != undefined)
                            about = gravatar.aboutMe;
                        else
                            about = '';

                        var urls = Array();
                        for (u in gravatar.urls) {
                            urls[u] = '<a target="_blank" href="' + gravatar.urls[u].value + '">' + gravatar.urls[u].title + '</a>';
                        }
                        urls = urls.join(' - ');

                        var accounts = Array();
                        for (a in gravatar.accounts) {
                            accounts[a] = '<a target="_blank" href="' + gravatar.accounts[a].url + '">'
                                + '<img src="' + maptheme.imgbase + gravatar.accounts[a].shortname + '.png" />'
                            + '</a>';
                        }
                        accounts = accounts.join(' ');

                        hovercard.setContent(
                            '<div class="hovercard" style="width:300px; color:#444;">'
                            + '<div class="col" style="float:left; width:85px">'
                                + '<span class="thumbnail"><img src="' + gravatar.thumbnailUrl + '" /></span>'
                            + '</div>'
                            + '<div class="col" style="float:left; width: 215px">'
                                + '<span class="display-name" style="display:block; font-weight:bold; font-size:1.2em;">' + gravatar.displayName + '</span>'
                                + '<span class="about" style="display:block;">' + about + '</span>'
                                + '<span class="urls" style="display:block;">' + urls + '</span>'
                                + '<span class="accounts" style="display:block;">' + accounts + '</span>'
                            + '</div>'
                            + '</div>'
                        );

                    }
                }
            });
        });
    }

});

});
