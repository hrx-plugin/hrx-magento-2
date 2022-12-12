var hrx_global_terminals_loading = false;
var hrxGlobalSettings = [];
var hrxGlobalData = [];
var preselected_terminal = false;
  jQuery('body').on('load-hrx-terminals', () => {
     if(jQuery('.hrxml-container').length == 0 && hrx_global_terminals_loading === false){
        loadHrxMapping();
     }
  });

    
function loadHrxMapping() {
  hrx_global_terminals_loading = true;
  let isModalReady = false;
  var hrxml = new HrxMapping(hrxGlobalSettings.api_url);
  hrxml
    .sub('terminal-selected', data => {
      jQuery('input[name="order[receiver_attributes][parcel_machine_id]"]').val(data.id);
      jQuery('#order_receiver_attributes_terminal_address').val(data.name + ", " + data.address);
      jQuery('.receiver_parcel_machine_address_filled').text('');
      jQuery('.receiver_parcel_machine_address_filled').append('<div class="d-inline-flex" style="margin-top: 5px;">' +
        '<img class="my-auto mx-0 me-2" src="'+hrxGlobalSettings.api_url + '/default_icon_icon.svg" width="25" height="25">' +
        '<h5 class="my-auto mx-0">' + data.address + ", " + data.zip + ", " + data.city + '</h5></div>' +
        '<br><a class="hrx_select_parcel_btn select_parcel_href" data-remote="true" href="#">Pakeisti</a>')
      jQuery('.receiver_parcel_machine_address_filled').show();
      jQuery('.receiver_parcel_machine_address_notfilled').hide();

      hrxml.publish('close-map-modal');
    });

  hrxml_country_code = jQuery('#order_receiver_attributes_country_code').val();
  hrxml_identifier = jQuery('#order_receiver_attributes_service_identifier').val();

  hrxml.setImagesPath(hrxGlobalSettings.hrx_plugin_url + '/images/');
  hrxml.init({country_code: hrxGlobalSettings.country , identifier: hrxGlobalSettings.identifier, city: hrxGlobalSettings.city , post_code: hrxGlobalSettings.postcode, receiver_address: hrxGlobalSettings.address, max_distance: hrxGlobalSettings.max_distance});

  window['hrxml'] = hrxml;

  hrxml.setTranslation({
    modal_header: hrxGlobalData.text_map,
    terminal_list_header: hrxGlobalData.text_list,
    seach_header: hrxGlobalData.text_search,
    search_btn: hrxGlobalData.text_search,
    modal_open_btn: hrxGlobalData.text_select_terminal,
    geolocation_btn: hrxGlobalData.text_my_loc,
    your_position: 'Distance calculated from this point',
    nothing_found: hrxGlobalData.text_not_found,
    no_cities_found: hrxGlobalData.text_no_city,
    geolocation_not_supported: 'Geolocation not supported',

    // Unused strings
    search_placeholder: hrxGlobalData.text_enter_address,
    workhours_header: 'Work hours',
    contacts_header: 'Contacts',
    select_pickup_point: hrxGlobalData.text_select_terminal,
    no_pickup_points: 'No terminal',
    select_btn: hrxGlobalData.text_select,
    back_to_list_btn: hrxGlobalData.text_reset,
    no_information: hrxGlobalData.text_not_found
  })

  hrxml.sub('hrxml-ready', function(t) {
    t.map.ZOOM_SELECTED = 8;
    isModalReady = true;
    jQuery('.spinner-border').hide();
    jQuery('.hrx_select_parcel_btn').removeClass('disabled').html(hrxGlobalData.text_select_terminal);
    hrx_global_terminals_loading = false;
    if (preselected_terminal) {
      $('input[name="hrx_global_terminal"]').val(preselected_terminal);
      hrxml.setActiveTerminal(preselected_terminal, false);
    }
  });

  jQuery(document).on('click', '.hrx_select_parcel_btn', function(e) {
    e.preventDefault();
    if (!isModalReady) {
      return;
    }
    hrxml.publish('open-map-modal');
    coords = {lng: jQuery('.receiver_coords').attr('value-x'), lat: jQuery('.receiver_coords').attr('value-y')};
    if (coords != undefined) {
      hrxml.map.addReferencePosition(coords);
      hrxml.dom.renderTerminalList(hrxml.map.addDistance(coords), true)
    }
  });

}
