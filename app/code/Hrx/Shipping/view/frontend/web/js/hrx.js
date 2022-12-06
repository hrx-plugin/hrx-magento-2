var hrx_last_selected_terminal = '';
(function ( $ ) {
    $.fn.hrx = function(options) {
        var settings = $.extend({
            maxShow: 8,
            showMap: true,
            hrxdata: [],
        }, options );
        var hrxdata = settings.hrxdata;
        var timeoutID = null;
        var currentLocationIcon = false;
        var autoSelectTerminal = false;
        var searchTimeout = null;
        var select = $(this);
        var select_terminal = hrxdata.text_select_terminal;
        var not_found = hrxdata.not_found;
        var terminalIcon = null;
        var homeIcon = null;
        var map = null;
        var terminals = [];
        $(this).find("option").each(function(){
            if ($(this).val()){
                var terminal = {
                    name: $(this).attr('data-name'),
                    id: $(this).val(),
                    y: $(this).attr('data-x'),
                    x: $(this).attr('data-y'),
                    address: $(this).attr('data-location'),
                    city: $(this).attr('data-city'),
                    country: $(this).attr('data-country'),
                }
                terminals.push(terminal);
            }
        });
        var selected = false;
        var previous_list = [];
        select.hide();
        if (select.val()){
            selected = {'id':select.val(),'text':select.find('option:selected').text(),'distance':false};
        }
        var container = $(document.createElement('div'));
        container.addClass("hrx-terminals-list");
        var dropdown = $('<div class = "hrx-dropdown">'+hrxdata.text_select_terminal+'</div>');
        updateSelection();
        
        var search = $('<input type = "text" placeholder = "'+hrxdata.text_enter_address+'" class = "hrx-search-input"/>');
        var loader = $('<div class = "loader"></div>').hide();
        var list = $(document.createElement('ul'));
        list.addClass('hrx-scrollbar');
        var showMapBtn = $('<a href = "#" class = "hrx-show-in-map hrx-btn">'+hrxdata.text_show_in_map+'<img src = "'+hrxdata.hrx_plugin_url+'/images/map-location.svg'+'"/></a>').hide();
        var showMore = $('<div class = "hrx-show-more"><a href = "#">'+hrxdata.text_show_more+'</a></div>').hide();
        var innerContainer = $('<div class = "hrx-inner-container"></div>').hide();

        $(container).insertAfter(select);
        $(innerContainer).append(search,showMapBtn,loader,list,showMore);
        $(container).append(dropdown,innerContainer);
        
        if (settings.showMap == true){
            initMap();
        }
        
        refreshList(false);
        
        innerContainer.on('click','a.hrx-show-in-map',function(e){
            e.preventDefault();            
            showModal();
        });
        $('body').on('click','.show-hrx-map',function(e){
            e.preventDefault();            
            showModal();
        });
        
        showMore.on('click',function(e){
            e.preventDefault();
            showAll();
        });
        
        dropdown.on('click',function(){
            toggleDropdown();
        });
        
        select.on('change',function(){
            selected = {'id':$(this).val(),'text':$(this).find('option:selected').text(),'distance':false};
            updateSelection();
        });
        
    
        search.on('keyup',function(){
            clearTimeout(searchTimeout);      
            searchTimeout = setTimeout(function() { suggest(search.val())}, 400);    
                  
        });
        search.on('selectpostcode',function(){
            findPosition(search.val(),true);    
                  
        });
        
        search.on('keypress',function(event){
            if (event.which == '13') {
              event.preventDefault();
            }
        });
        
        $(document).on('mousedown',function(e){
            var container = $(".hrx-terminals-list");
            if (!container.is(e.target) && container.has(e.target).length === 0 && container.hasClass('open')) 
                toggleDropdown();
        });   
        
        $('.hrx-back-to-list').off('click').on('click',function(){
            listTerminals(terminals,0,previous_list);
            $(this).hide();
        });
        
        select.on('change',function(){
            hrx_last_selected_terminal = $(this).val();         
        });
       
        searchByAddress();
        
        
        function showModal(){
            getLocation();
            $('#hrx-search input').val(search.val());
            //$('#hrx-search button').trigger('click');
              if ($('.hrx-terminals-list input.hrx-search-input').val() != ''){
                  $('#hrx-search input').val($('.hrx-terminals-list input.hrx-search-input').val());
                 // $('#hrx-search button').trigger('click')
              }
            if (selected != false){
                $(terminals).each(function(i,val){
                    if (selected.id == val.id){
                        zoomTo([val.x, val.y], selected.id);
                        return false;
                    }
                });
            }
            $('#hrxModal').show();
            //getLocation();
            var event;
            if(typeof(Event) === 'function') {
                event = new Event('resize');
            }else{
                event = document.createEvent('Event');
                event.initEvent('resize', true, true);
            }
            window.dispatchEvent(event);
          }

        function searchByAddress(){
            if (selected == false){
            
            if (hrxdata.postcode != ''){
                    search.val(hrxdata.postcode).trigger('selectpostcode');
                }
            }
        }

        function showAll(){
            list.find('li').show();
            showMore.hide();
        }
        
        function refreshList(autoselect){            
            $('.hrx-back-to-list').hide();
            var counter = 0;
            var city = false;
            var html = '';
            list.html('');
            $('.hrx-found-terminals').html('');
            $.each(terminals,function(i,val){
                var li = $(document.createElement("li"));
                li.attr('data-id',val.id);
                li.html(val.name);
                if (val.distance !== undefined && val.distance != false){
                    li.append(' <strong>' + val.distance + 'km</strong>');  
                    counter++;
                    if (settings.showMap == true && counter <= settings.maxShow){
                        //console.log('add-to-map');
                        html += '<li data-pos="['+[val.x, val.y]+']" data-id="'+val.id+'" ><div><a class="hrx-li">'+counter+'. '+val.name+'</a> <b>'+val.distance+' km.</b>\
                                  <div align="left" id="hrx-'+val.id+'" class="hrx-details" style="display:none;"><br/>\
                                  <button type="button" class="btn-marker hrx-btn" style="font-size:14px; padding:0px 5px;margin-bottom:10px; margin-top:5px;height:25px;" data-id="'+val.id+'">'+select_terminal+'</button>\
                                  </div>\
                                  </div></li>';
                    }
                } else {
                    if (settings.showMap == true ){
                        //console.log('add-to-map');
                        html += '<li data-pos="['+[val.x, val.y]+']" data-id="'+val.id+'" ><div><a class="hrx-li">'+(i+1)+'. '+val.name+'</a>\
                                  <div align="left" id="hrx-'+val.id+'" class="hrx-details" style="display:none;"><br/>\
                                  <button type="button" class="btn-marker hrx-btn" style="font-size:14px; padding:0px 5px;margin-bottom:10px; margin-top:5px;height:25px;" data-id="'+val.id+'">'+select_terminal+'</button>\
                                  </div>\
                                  </div></li>';
                    }
                }
                if (selected != false && selected.id == val.id){
                    li.addClass('selected');
                }
                if (counter > settings.maxShow){
                    li.hide();
                }
                if (val.city != city){
                    var li_city = $('<li class = "city">'+val.city+'</li>');
                    if (counter > settings.maxShow){
                        li_city.hide();
                    }
                    list.append(li_city);
                    city = val.city;
                }
                list.append(li);
            });
            list.find('li').on('click',function(){
                if (!$(this).hasClass('city')){
                    list.find('li').removeClass('selected');
                    $(this).addClass('selected');
                    selectOption($(this));
                }
            });
            if (autoselect == true){
                var first = list.find('li:not(.city):first');
                list.find('li').removeClass('selected');
                first.addClass('selected');
                selectOption(first);
            }
            var selectedLi = list.find('li.selected');
            var topOffset = 0;
            /*
            if (selectedLi !== undefined){
                topOffset = selectedLi.offset().top - list.offset().top + list.scrollTop();                
            }
            console.log(topOffset);
            */
            list.scrollTop(topOffset);
            if (settings.showMap == true){
                document.querySelector('.hrx-found-terminals').innerHTML = '<ul class="hrx-terminals-listing" start="1">'+html+'</ul>';
                if (selected != false && selected.id != 0){
                    map.eachLayer(function (layer) { 
                        if (layer.options.terminalId !== undefined && L.DomUtil.hasClass(layer._icon, "active")){
                            L.DomUtil.removeClass(layer._icon, "active");
                        }
                        if (layer.options.terminalId == selected.id) {
                            //layer.setLatLng([newLat,newLon])
                            L.DomUtil.addClass(layer._icon, "active");
                        } 
                    });
                }
            }
        }
        
        function selectOption(option){
            select.val(option.attr('data-id'));
            select.trigger('change');
            selected = {'id':option.attr('data-id'),'text':option.text(),'distance':false};
            updateSelection();
            closeDropdown();
        }
        
        function updateSelection(){
            if (selected != false){
               dropdown.html(selected.text); 
            }
        }
        
        function toggleDropdown(){
            if (container.hasClass('open')){
                innerContainer.hide();
                container.removeClass('open') 
            } else {
                innerContainer.show();
                container.addClass('open');
            }
        }  
        
        function closeDropdown(){
            if (container.hasClass('open')){
                innerContainer.hide();
                container.removeClass('open') 
            } 
        }
        
        function resetList(){
   
            $.each( terminals, function( key, location ) {
                location.distance = false;
                
            });
    
            terminals.sort(function(a, b) {
                var distOne = a[0];
                var distTwo = b[0];
                if (parseFloat(distOne) < parseFloat(distTwo)) {
                    return -1;
                }
                if (parseFloat(distOne) > parseFloat(distTwo)) {
                    return 1;
                }
                    return 0;
            });   
        }
        
        function calculateDistance(y,x){
   
            $.each( terminals, function( key, location ) {
                distance = calcCrow(y, x, location.x, location.y);
                location.distance = distance.toFixed(2);
                
            });
    
            terminals.sort(function(a, b) {
                var distOne = a.distance;
                var distTwo = b.distance;
                if (parseFloat(distOne) < parseFloat(distTwo)) {
                    return -1;
                }
                if (parseFloat(distOne) > parseFloat(distTwo)) {
                    return 1;
                }
                    return 0;
            });   
        }
        
        function toRad(Value) 
        {
           return Value * Math.PI / 180;
        }
    
        function calcCrow(lat1, lon1, lat2, lon2) 
        {
          var R = 6371;
          var dLat = toRad(lat2-lat1);
          var dLon = toRad(lon2-lon1);
          var lat1 = toRad(lat1);
          var lat2 = toRad(lat2);
    
          var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.sin(dLon/2) * Math.sin(dLon/2) * Math.cos(lat1) * Math.cos(lat2); 
          var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
          var d = R * c;
          return d;
        }
        
        function findPosition(address,autoselect){
            //console.log(address);
            if (address == "" || address.length < 3){
                resetList();
                showMore.hide();
                refreshList(autoselect);
                return false;
            }
            $.getJSON( "https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/findAddressCandidates?singleLine="+address+"&sourceCountry="+hrxdata.hrx_current_country+"&category=&outFields=Postal&maxLocations=1&forStorage=false&f=pjson", function( data ) {
              if (data.candidates != undefined && data.candidates.length > 0){
                calculateDistance(data.candidates[0].location.y,data.candidates[0].location.x);
                refreshList(autoselect);
                //list.prepend(showMapBtn);
                showMapBtn.show();
                //console.log('add');
                showMore.show();
                if (settings.showMap == true){
                    setCurrentLocation([data.candidates[0].location.y,data.candidates[0].location.x]);
                }
              }
            });
        }
        
        function suggest(address){
            $.getJSON( "https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/suggest?text="+address+"&f=pjson&sourceCountry="+hrxdata.hrx_current_country+"&maxSuggestions=1", function( data ) {
              if (data.suggestions != undefined && data.suggestions.length > 0){
                findPosition(data.suggestions[0].text,false);
              }
            });
        }
        
        function initMap(){
           $('#hrxMapContainer').html('<div id="hrxMap"></div>');
          if (hrxdata.hrx_current_country == "LT"){
            map = L.map('hrxMap').setView([54.999921, 23.96472], 8);
          }
          else if (hrxdata.hrx_current_country == "LV"){
            map = L.map('hrxMap').setView([56.8796, 24.6032], 8);
          }
          else if (hrxdata.hrx_current_country == "EE"){
            map = L.map('hrxMap').setView([58.7952, 25.5923], 7);
          } else {
            map = L.map('hrxMap').setView([54.999921, 23.96472], 8);
          }
          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.hrx.eu">HRX</a>' +
                    ' | Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>'
            }).addTo(map);

            var Icon = L.Icon.extend({
                options: {
                    //shadowUrl: 'leaf-shadow.png',
                    iconSize:     [41, 55],
                    //shadowSize:   [50, 64],
                    iconAnchor:   [15, 34],
                    //shadowAnchor: [4, 62],
                    popupAnchor:  [-3, -76]
                }
            });
          
          var Icon2 = L.Icon.extend({
                options: {
                    iconSize:     [32, 32],
                    iconAnchor:   [16, 32]
                }
            });
            
          
            terminalIcon = new Icon({iconUrl: hrxdata.hrx_plugin_url+'/images/pins/map-pin.png'});
            homeIcon = new Icon2({iconUrl: hrxdata.hrx_plugin_url+'locator_img.png'});
            
          var locations = terminals;
          
            jQuery.each( locations, function( key, location ) {
                if (['LT','LV','EE','FI','PL','SE'].includes(location.country)) {
                    terminalIcon = new Icon({iconUrl: hrxdata.hrx_plugin_url+'/images/pins/'+location.country+'.png'});
                } else {
                    terminalIcon = new Icon({iconUrl: hrxdata.hrx_plugin_url+'/images/pins/map-pin.png'});
                }
                L.marker([location.x, location.y], {icon: terminalIcon, terminalId:location.id }).on('click',function(e){ listTerminals(locations,0,this.options.terminalId);terminalDetails(this.options.terminalId);}).addTo(map);
            });
          
          //show button
          $('.show-hrx-map').show(); 
          
          $('#closeHrxModal').on('click',function(){$('#hrxModal').hide();});
          $('#hrx-search input').off('keyup focus').on('keyup focus',function(){
                clearTimeout(timeoutID);      
                timeoutID = setTimeout(function(){ autoComplete($('#hrx-search input').val())}, 500);    
                      
            });
            
            $('.hrx-autocomplete ul').off('click').on('click','li',function(){
                $('#hrx-search input').val($(this).text());
                /*
                if ($(this).attr('data-location-y') !== undefined){
                    setCurrentLocation([$(this).attr('data-location-y'),$(this).attr('data-location-x')]);
                    calculateDistance($(this).attr('data-location-y'),$(this).attr('data-location-x'));
                    refreshList(false);
                }
                */
                $('#hrx-search #hrx-search-button').trigger('click');
                $('.hrx-autocomplete').hide();
            });
            $(document).click(function(e){
                var container = $(".hrx-autocomplete");
                if (!container.is(e.target) && container.has(e.target).length === 0) 
                    container.hide();
            });
          
            $('#closeHrxModal').on('click',function(){
                $('#hrxModal').hide();
            });
            $('#hrx-search #hrx-search-button').off('click').on('click',function(e){
              e.preventDefault();
              var postcode = $('#hrx-search input').val();
              findPosition(postcode,false);
            });
            $('.hrx-found-terminals').on('click','li',function(){
                $('.hrx-found-terminals li').removeClass('active');
                $(this).addClass('active');
                zoomTo(JSON.parse($(this).attr('data-pos')),$(this).attr('data-id'));
            });
            $('.hrx-found-terminals').on('click','li button',function(){
                terminalSelected($(this).attr('data-id'));
            });
        }
        
        function autoComplete(address){
            var founded = [];
            $('.hrx-autocomplete ul').html('');
            $('.hrx-autocomplete').hide();
            if (address == "" || address.length < 3) return false;
            $('#hrx-search input').val(address);
            //$.getJSON( "https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/findAddressCandidates?singleLine="+address+"&sourceCountry="+hrx_current_country+"&category=&outFields=Postal,StAddr&maxLocations=5&forStorage=false&f=pjson", function( data ) {
            $.getJSON( "https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/suggest?text="+address+"&sourceCountry="+hrxdata.hrx_current_country+"&f=pjson&maxSuggestions=4", function( data ) {
              if (data.suggestions != undefined && data.suggestions.length > 0){
                  $.each(data.suggestions ,function(i,item){
                    //console.log(item);
                    //if (founded.indexOf(item.attributes.StAddr) == -1){
                        //const li = $("<li data-location-y = '"+item.location.y+"' data-location-x = '"+item.location.x+"'>"+item.address+"</li>");
                        const li = $("<li data-magickey = '"+item.magicKey+"' data-text = '"+item.text+"'>"+item.text+"</li>");
                        $(".hrx-autocomplete ul").append(li);
                    //}
                    //if (item.attributes.StAddr != ""){
                    //    founded.push(item.attributes.StAddr);
                    //}
                  });
              }
                  if ($(".hrx-autocomplete ul li").length == 0){
                      $(".hrx-autocomplete ul").append('<li>'+not_found+'</li>');
                  }
              $('.hrx-autocomplete').show();
            });
        }
        
        function terminalDetails(id) {
            /*
            terminals = document.querySelectorAll(".hrx-details")
            for(i=0; i <terminals.length; i++) {
                terminals[i].style.display = 'none';
            }
            */
            $('.hrx-terminals-listing li div.hrx-details').hide();
            id = 'hrx-'+id;
            dispHrx = document.getElementById(id)
            if(dispHrx){
                dispHrx.style.display = 'block';
            }      
        }
        
        function getLocation() {
          if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(loc) {
                if (selected == false){
                    setCurrentLocation([loc.coords.latitude, loc.coords.longitude]);
                }
            });
          } 
        }
        
        function setCurrentLocation(pos){
            if (currentLocationIcon){
              map.removeLayer(currentLocationIcon);
            }
            //console.log('home');
            currentLocationIcon = L.marker(pos, {icon: homeIcon}).addTo(map);
            map.setView(pos,16);
            //calculateDistance(pos[0],pos[1]);
            //refreshList(false);
        }
        function listTerminals(locations,limit,id){
              if (limit === undefined){
                  limit=0;
              }
              if (id === undefined){
                  id=0;
              }
             var html = '', counter=1;
             if (id != 0 && !$.isArray(id)){
                previous_list = [];
                $('.hrx-found-terminals li').each(function(){
                    previous_list.push($(this).attr('data-id'));
                });
                $('.hrx-back-to-list').show();
             }
             if ($.isArray(id)){
                previous_list = []; 
             }
            $('.hrx-found-terminals').html('');
            //console.log(id);
            $.each( locations, function( key, location ) {
              if (limit != 0 && limit < counter){
                return false;
              }
              if ($.isArray(id)){
                if ( $.inArray( location.id, id) == -1){
                    return true;
                }
              }
              else if (id !=0 && id != location.id){
                return true;
              }
              if (autoSelectTerminal && counter == 1){
                terminalSelected(location.id,false);
              }
              var destination = [location.x, location.y]
              var distance = 0;
              if (location['distance'] != undefined){
                distance = location['distance'];
              }
              html += '<li data-pos="['+destination+']" data-id="'+location.id+'" ><div><a class="hrx-li">'+counter+'. <b>'+location.name+'</b></a>';
              if (distance != 0) {
              html += ' <b>'+distance+' km.</b>';
              }
               html += '<div align="left" id="hrx-'+location.id+'" class="hrx-details" style="display:none;"><br/>\
                                          <button type="button" class="btn-marker hrx-btn" style="font-size:14px; padding:0px 5px;margin-bottom:10px; margin-top:5px;height:25px;" data-id="'+location.id+'">'+select_terminal+'</button>\
                                          </div>\
                                          </div></li>';
                                              
                              counter++;           
                               
            });
            document.querySelector('.hrx-found-terminals').innerHTML = '<ul class="hrx-terminals-listing" start="1">'+html+'</ul>';
            if (id != 0){
                map.eachLayer(function (layer) { 
                    if (layer.options.terminalId !== undefined && L.DomUtil.hasClass(layer._icon, "active")){
                        L.DomUtil.removeClass(layer._icon, "active");
                    }
                    if (layer.options.terminalId == id) {
                        //layer.setLatLng([newLat,newLon])
                        L.DomUtil.addClass(layer._icon, "active");
                    } 
                });
            }
        }
        
        function zoomTo(pos, id){
            terminalDetails(id);
            map.setView(pos,14);
            map.eachLayer(function (layer) { 
                if (layer.options.terminalId !== undefined && L.DomUtil.hasClass(layer._icon, "active")){
                    L.DomUtil.removeClass(layer._icon, "active");
                }
                if (layer.options.terminalId == id) {
                    //layer.setLatLng([newLat,newLon])
                    L.DomUtil.addClass(layer._icon, "active");
                } 
            });
        }
        
        function terminalSelected(terminal,close) {
          if (close === undefined){
              close = true;
          }
              var matches = document.querySelectorAll(".hrxOption");
              for (var i = 0; i < matches.length; i++) {
                node = matches[i]
                if ( node.value.includes(terminal)) {
                  node.selected = 'selected';
                } else {
                  node.selected = false;
                }
              }
                    
              select.val(terminal);
              select.trigger("change");
              if (close){
                $('#hrxModal').hide();
            }
        }
        
        return this;
    };
 
}( jQuery ));