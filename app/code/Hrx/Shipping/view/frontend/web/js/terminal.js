/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "HrxMapping", function() { return HrxMapping; });
/* harmony import */ var _styles_main_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(1);
/* harmony import */ var _styles_main_css__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_styles_main_css__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _modules_DependencyCheck_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(2);
/* harmony import */ var _modules_DOMManipulator_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(3);
/* harmony import */ var _modules_Map_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(5);
/* harmony import */ var _modules_Tools_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(4);
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }






var HrxMapping = /*#__PURE__*/function () {
  function HrxMapping(api_server_url) {
    var _this = this;

    _classCallCheck(this, HrxMapping);

    /* Terminal Mapping version */
    this.version = '1.1.3';
    this._isDebug = false;
    this.prefix = '[TMJS] '; // Default path to images

    this.imagePath = ''; // Terminal API server URL

    if (typeof api_server_url == 'undefined') {
      console.error(this.prefix + 'Terminal server API MUST be provided');
      return;
    }

    this.api_server_url = api_server_url ; // Parcels country and identifier

    this.country_code = null;
    this.identifier = null;
    this.containerId = 'hrxml_' + Object(_modules_Tools_js__WEBPACK_IMPORTED_MODULE_4__["generateId"])(); // Default strings and proxy to detect changes

    this.strings = new Proxy({
      modal_header: 'Terminal map',
      terminal_list_header: 'Terminal list',
      seach_header: 'Search around',
      search_btn: 'Find',
      modal_open_btn: 'Select terminal',
      geolocation_btn: 'Use my location',
      your_position: 'Distance calculated from this point',
      nothing_found: 'Nothing found',
      no_cities_found: 'There were no cities found for your search term',
      geolocation_not_supported: 'Geolocation is not supported',
      select_pickup_point: 'Select a pickup point',
      // Unused strings
      search_placeholder: 'Enter postcode/address',
      workhours_header: 'Workhours',
      contacts_header: 'Contacts',
      no_pickup_points: 'No points to select',
      select_btn: 'select',
      back_to_list_btn: 'reset search',
      no_information: 'No information'
    }, {
      set: function set(obj, prop, value) {
        // update DOM
        _this.dom.updateString(prop, value); // default functionality


        obj[prop] = value;
        return true;
      }
    });
    this.subscribers = {};
    this.depend = new _modules_DependencyCheck_js__WEBPACK_IMPORTED_MODULE_1__["DependencyCheck"](this);
    this.dom = new _modules_DOMManipulator_js__WEBPACK_IMPORTED_MODULE_2__["DOMManipulator"](this);
    this.map = null;
  }

  _createClass(HrxMapping, [{
    key: "init",
    value: function init() {
      var _this2 = this;

      var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
          _ref$country_code = _ref.country_code,
          country_code = _ref$country_code === void 0 ? null : _ref$country_code,
          _ref$identifier = _ref.identifier,
          identifier = _ref$identifier === void 0 ? null : _ref$identifier,
          _ref$postal_code = _ref.postal_code,
          postal_code = _ref$postal_code === void 0 ? null : _ref$postal_code,
          _ref$city = _ref.city,
          city = _ref$city === void 0 ? null : _ref$city,
          _ref$receiver_address = _ref.receiver_address,
          receiver_address = _ref$receiver_address === void 0 ? null : _ref$receiver_address,
          _ref$max_distance = _ref.max_distance,
          max_distance = _ref$max_distance === void 0 ? null : _ref$max_distance,
          _ref$isModal = _ref.isModal,
          isModal = _ref$isModal === void 0 ? true : _ref$isModal,
          _ref$modalParent = _ref.modalParent,
          modalParent = _ref$modalParent === void 0 ? null : _ref$modalParent,
          _ref$hideContainer = _ref.hideContainer,
          hideContainer = _ref$hideContainer === void 0 ? false : _ref$hideContainer,
          _ref$hideSelectBtn = _ref.hideSelectBtn,
          hideSelectBtn = _ref$hideSelectBtn === void 0 ? false : _ref$hideSelectBtn;

      this.country_code = country_code;
      this.identifier = identifier;
      this.dom.isModal = isModal;
      this.dom.hideContainer = hideContainer;
      this.dom.hideSelectBtn = hideSelectBtn;

      if (modalParent) {
        this.dom.setModalParent(modalParent);
      }

      console.info(this.prefix + 'Initializing Terminal Mapping');
      this.dom.addOverlay();
      this.dom.addContainer(this.containerId, this.strings); // load check for leaflet and plugins first

      this.depend.loadLeaflet(function () {
        _this2.map = new _modules_Map_js__WEBPACK_IMPORTED_MODULE_3__["Map"](_this2.dom.UI.map, _this2);
          var params = Object(_modules_Tools_js__WEBPACK_IMPORTED_MODULE_4__["makeQueryParams"])({
          'q[country_code_eq]': country_code,
          'q[identifier_eq]': identifier,
          //'q[city_eq]': city,
          'country_code': country_code,
          'postal_code': postal_code,
          'city': city,
          'receiver_address': receiver_address,
          'distance': max_distance
        }); // Get terminal list
        //console.log('fetch ' + _this2.api_server_url + 'parcel_machines' + (params ? '?' + params : ''));
        fetch(_this2.api_server_url + '/' + country_code + (params ? '?' + params : ''), 
        {
        }).then(function (response) {
          return response.json();
        }).then(function (json) {
          var terminals = json.map(function (terminal) {
            terminal['coords'] = {
              lat: terminal.y,
              lng: terminal.x
            };
            return terminal;
          }); //.filter(terminal => terminal.identifier == 'lp_express');
          _this2.setTerminals(terminals);

          _this2.dom.renderTerminalList(_this2.map.locations);

          console.info(_this2.prefix + 'Terminals loaded');

          _this2.dom.removeOverlay();

          _this2.publish('hrxml-ready', _this2);
        }).catch((error) => {
          _this2.dom.removeOverlay();
        });
          
      });
    }
  }, {
    key: "setImagesPath",
    value: function setImagesPath() {
      var path = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
      this.imagePath = path;
    }
  }, {
    key: "setTranslation",
    value: function setTranslation() {
      var newStrings = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
      this.strings = Object.assign(this.strings, newStrings);
      console.info(this.prefix + 'Translation updated');
      return this;
    }
  }, {
    key: "setTerminals",
    value: function setTerminals(terminals) {
      this.map.setLocations(terminals);
    }
  }, {
    key: "sub",
    value: function sub(eventName, callback) {
      if (!this.subscribers[eventName]) {
        this.subscribers[eventName] = [];
      }

      this.subscribers[eventName].push(callback);
      return this;
    }
  }, {
    key: "unsub",
    value: function unsub(eventName, reference) {
      if (!this.subscribers[eventName]) {
        return true;
      }

      this.subscribers[eventName] = this.subscribers[eventName].filter(function (callback) {
        return callback !== reference;
      });
    }
  }, {
    key: "publish",
    value: function publish(eventName, data) {
      if (!this.subscribers[eventName]) {
        return;
      }

      this.subscribers[eventName].forEach(function (callback) {
        return callback(data);
      });
    }
  }]);

  return HrxMapping;
}();
window.HrxMapping = HrxMapping;

/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),
/* 2 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "DependencyCheck", function() { return DependencyCheck; });
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var DependencyCheck = /*#__PURE__*/function () {
  function DependencyCheck(TMJS) {
    _classCallCheck(this, DependencyCheck);

    this.TMJS = TMJS;
    this.prefix = TMJS.prefix;
    this.leafletJsCdn = "https://unpkg.com/leaflet@1.6.0/dist/leaflet.js";
    this.leafletCssCdn = "https://unpkg.com/leaflet@1.6.0/dist/leaflet.css";
    this.leafletMarkerClusterJsCdn = "https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js";
    this.leafletMarkerClusterCssCdn = "https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css";
    this.leafletMarkerClusterDefaultCssCdn = "https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css";
  }

  _createClass(DependencyCheck, [{
    key: "isLeaflet",
    value: function isLeaflet() {
      return typeof L !== "undefined";
    }
  }, {
    key: "isMarkerCluster",
    value: function isMarkerCluster() {
      return typeof L.markerClusterGroup !== 'undefined';
    }
  }, {
    key: "makeIdFromUrl",
    value: function makeIdFromUrl(url) {
      return url.split('/').pop().replace(/\./gi, '-').toLowerCase();
    }
  }, {
    key: "loadScript",
    value: function loadScript(url, callback) {
      var script_id = this.makeIdFromUrl(url);

      if (document.getElementById(script_id)) {
        return;
      }

      var script = document.createElement("script");
      script.type = "text/javascript";
      script.id = script_id;

      if (script.readyState) {
        //IE
        script.onreadystatechange = function () {
          if (script.readyState == "loaded" || script.readyState == "complete") {
            script.onreadystatechange = null;
            callback();
          }
        };
      } else {
        //Others
        script.onload = function () {
          callback();
        };
      }

      script.src = url;
      document.getElementsByTagName("body")[0].appendChild(script);
    }
  }, {
    key: "loadCSS",
    value: function loadCSS(url) {
      var cssId = this.makeIdFromUrl(url);

      if (document.getElementById(cssId)) {
        return;
      }

      var head = document.getElementsByTagName('head')[0];
      var link = document.createElement('link');
      link.id = cssId;
      link.rel = 'stylesheet';
      link.type = 'text/css';
      link.href = url;
      link.media = 'all';
      head.appendChild(link);
    }
  }, {
    key: "loadScripts",
    value: function loadScripts() {
      var callback = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
      this.loadScript(this.leafletJsCdn, function () {
        if (callback && typeof callback === "function") {
          callback();
        }
      });
    }
  }, {
    key: "loadStyles",
    value: function loadStyles() {
      this.loadCSS(this.leafletCssCdn);
    }
  }, {
    key: "loadLeaflet",
    value: function loadLeaflet() {
      var _this = this;

      var callback = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

      if (this.isLeaflet()) {
        console.info(this.prefix + 'Found Leaflet version:', L.version);
        callback();
        //this.loadLeafletPlugins(callback);
      } else {
        console.info(this.prefix + 'Loading Leaflet');
        var that = this;
        this.loadScript(this.leafletJsCdn, function () {
          return _this.loadLeaflet(callback);
        }); // add leaflet css

        this.loadCSS(this.leafletCssCdn);
      }
    }
    /**
     * Check and if needed loads required Leaflet plugins
     * @param {Function} callback - Function to call when finished adding plugins
     */

  }, {
    key: "loadLeafletPlugins",
    value: function loadLeafletPlugins() {
      var _this2 = this;

      var callback = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

      if (this.isMarkerCluster()) {
        console.info(this.prefix + 'Leaflet.markercluster plugin found');

        if (callback && typeof callback === "function") {
          callback();
        }
      } else {
        console.info(this.prefix + 'Loading Leaflet.markercluster plugin');
        var that = this;
        this.loadScript(this.leafletMarkerClusterJsCdn, function () {
          return _this2.loadLeafletPlugins(callback);
        }); // add Leaflet.markercluster default css

        this.loadCSS(this.leafletMarkerClusterCssCdn);
        this.loadCSS(this.leafletMarkerClusterDefaultCssCdn);
      }
    }
  }]);

  return DependencyCheck;
}();

/***/ }),
/* 3 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "DOMManipulator", function() { return DOMManipulator; });
/* harmony import */ var _Tools_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(4);
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }


/**
 * DOM manipulation class
 */

var DOMManipulator = /*#__PURE__*/function () {
  /**
   * @param TMJS
   * {string} this.prefix - prefix to use with console messages
   */
  function DOMManipulator(TMJS) {
    _classCallCheck(this, DOMManipulator);

    this.TIMEOUT_DEBOUCE = 1000;
    this.TMJS = TMJS;
    this.prefix = TMJS.prefix;
    this._searchTimeoutId = null;
    this._lastSearchTerm = '';
    this.containerParent = null;
    this.modalParent = null;
    this.isModal = true;
    this.hideContainer = true;
    this.hideSelectBtn = false;
    this.UI = {
      container: null,
      modal: null,
      map: null,
      overlay: null
    };
    this.registerSubs();
  }

  _createClass(DOMManipulator, [{
    key: "registerSubs",
    value: function registerSubs() {
      var _this = this;

      this.TMJS.sub('terminal-selected', function (data) {
        _this.UI.container.querySelector('.hrxml-selected-terminal').innerText = `${data.name}, ${data.address}`;
      });
      this.TMJS.sub('geolocation', function (coords) {
        _this.UI.modal.querySelector('.hrxml-search-result').innerText = "Lat: ".concat(coords.lat, " Long: ").concat(coords.lng);
      });
      this.TMJS.sub('add-search-loader', function (data) {
        _this.UI.modal.querySelector('.hrxml-search-result').innerHTML = "<div id=\"hrxml-terminals-loader\" class=\"hrxml-loading\"></div>";
      });
      this.TMJS.sub('reset-search-result', function (data) {
        _this.UI.modal.querySelector('.hrxml-search-result').innerText = '';
      });
      this.TMJS.sub('search-result', function (data) {
        _this.UI.modal.querySelector('.hrxml-search-result').innerText = data.address;
        console.info('GEOCODE RESPONSE:', data);
      });
      this.TMJS.sub('list-updated', function (data) {
        _this.showSelected();
      });
      this.TMJS.sub('close-map-modal', function (data) {
        _this.closeModal();
      });
      this.TMJS.sub('open-map-modal', function (data) {
        _this.openModal();
      });
    }
  }, {
    key: "setContainerParent",
    value: function setContainerParent(el) {
      if (el instanceof HTMLElement) {
        this.containerParent = el;
        this.attachContainerToParent(this.UI.container, el);
        return this;
      }

      console.error(this.prefix + 'Container parent element not changed! Must be HTMLElement');
    }
  }, {
    key: "setModalParent",
    value: function setModalParent(el) {
      if (el instanceof HTMLElement) {
        this.modalParent = el;
        this.attachContainerToParent(this.UI.modal, el);
        return this;
      }

      console.error(this.prefix + 'Modal parent element not changed! Must be HTMLElement');
    }
  }, {
    key: "attachContainerToParent",
    value: function attachContainerToParent(child) {
      var parent = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

      if (!child) {
        return null;
      }

      if (parent && child) {
        return parent.appendChild(child);
      } // default attach to body tag


      return document.body.appendChild(child);
    }
    /**
     * Creates loading overlay (only one active overlay is allowed)
     * @param {HTMLElement} parentNode - HTML Element to attach overlay to.
     */

  }, {
    key: "addOverlay",
    value: function addOverlay(parentNode) {
      this.removeOverlay(); // default attach to body tag

      if (parentNode instanceof HTMLElement === false) {
        parentNode = document.body;
      }

      var overlayNode = document.createElement('div');
      overlayNode.className = 'hrxml-loading-overlay';
      overlayNode.innerHTML = '<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>';
      this.UI.overlay = parentNode.appendChild(overlayNode);
      this.bodyOverflow(false);
    }
    /**
     * Removes loading overlay
     */

  }, {
    key: "removeOverlay",
    value: function removeOverlay() {
      if (this.UI.overlay) {
        this.UI.overlay.parentNode.removeChild(this.UI.overlay);
        this.UI.overlay = null;
        this.bodyOverflow(true);
      }
    }
    /**
     * Add or remove `overflow: hidden` style to body tag
     * 
     * true - removes style (default)
     * 
     * false - adds style
     * 
     * @param {boolean} show 
     */

  }, {
    key: "bodyOverflow",
    value: function bodyOverflow() {
      var show = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
      show ? document.body.classList.remove('hrxml-hide-overflow') : document.body.classList.add('hrxml-hide-overflow');
    }
  }, {
    key: "addContainer",
    value: function addContainer(id, strings) {
      var template = "\n    <div class=\"hrxml-selected-terminal\" data-hrxml-string=\"select_pickup_point\">".concat(strings.select_pickup_point, "</div>\n    <a href=\"#hrxmlmodal\" class=\"hrxml-open-modal-btn hrx-btn\" data-hrxml-string=\"modal_open_btn\">").concat(strings.modal_open_btn, "</a>\n    ");
      var container = this.createElement('div', {
        classList: ['hrxml-container', this.hideContainer ? 'hrxml-hidden' : ''],
        innerHTML: template
      });
      container.id = id;
      this.UI.container = container;
      jQuery(`#hrx_global_map_container td`).append(container);
      this.addModal(id + '_modal', strings);
      this.attachListeners();
    }
  }, {
    key: "updateString",
    value: function updateString(stringName, newValue) {
      document.querySelectorAll("[data-hrxml-string=\"".concat(stringName, "\"]")).forEach(function (el) {
        return el.innerText = newValue;
      });
    }
  }, {
    key: "createElement",
    value: function createElement(tag) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {
        innerHTML: '',
        classList: []
      };

      /**
       * @type HTMLElement
       */
      var el = document.createElement(tag);

      if (options.classList instanceof Array && options.classList.length) {
        el.className = options.classList.join(' ');
      }

      if (options.innerHTML) {
        el.innerHTML = options.innerHTML;
      }

      return el;
    }
  }, {
    key: "addModal",
    value: function addModal(id, strings) {
      var close_button_class = this.isModal ? '' : 'hrxml-hidden';
      var template = "\n      <div class=\"hrxml-modal-content\">\n\n        <div class=\"hrxml-modal-body\">\n          <div class=\"hrxml-map-container\"><div class=\"hrxml-map\"></div></div>\n          <div class=\"hrxml-terminal-sidebar\">\n            <div class=\"hrxml-terminal-finder\">\n              <h2 data-hrxml-string=\"modal_header\">".concat(strings.modal_header, "</h2>\n              <div class=\"hrxml-close-modal-btn ").concat(close_button_class, "\"></div>\n\n              <div class=\"hrxml-d-block\">\n                <input type=\"text\" class=\"hrxml-search-input\" placeholder=\"Type address\">\n                <a href=\"#search\" class=\"hrxml-search-btn\" ><img src=\"").concat(this.TMJS.imagePath, "map-search.svg\" width=\"18\"></a>\n              </div>\n\n              <div class=\"hrxml-d-block hrxml-pt-1\">\n                <a href=\"#useMyLocation\" class=\"hrxml-geolocation-btn\"><img src=\"").concat(this.TMJS.imagePath, "my-loc.svg\" width=\"15\"><span data-hrxml-string=\"geolocation_btn\">").concat(strings.geolocation_btn, "</span></a>\n              </div>\n              <div class=\"hrxml-search-result hrxml-d-block hrxml-pt-2\"></div>\n            </div>\n\n            <div class=\"hrxml-terminal-block\">\n              <h3 data-hrxml-string=\"terminal_list_header\">").concat(strings.terminal_list_header, "</h3>\n              <ul class=\"hrxml-terminal-list\"></ul>\n            </div>\n          </div>\n        </div>\n      </div>\n    ");
      var modal = this.createElement('div', {
        classList: [this.isModal ? 'hrxml-modal hrx-global' : 'hrxml-modal-flat', this.isModal ? 'hrxml-hidden' : ''],
        innerHTML: template
      });
      modal.id = id;
      /* if exists destroy and rebuild */

      if (this.UI.modal !== null) {
        this.UI.modal.parentNode.removeChild(this.UI.modal);
        this.UI.modal = null;
      }

      this.UI.modal = modal;
      this.UI.map = modal.querySelector('.hrxml-map');
      this.UI.terminalList = modal.querySelector('.hrxml-terminal-list');
      this.attachContainerToParent(modal, this.modalParent); //document.body.appendChild(this.UI.modal);
    }
  }, {
    key: "attachListeners",
    value: function attachListeners() {
      var _this2 = this;

      this.UI.container.querySelector('.hrxml-open-modal-btn').addEventListener('click', function (e) {
        e.preventDefault();

        _this2.openModal();
      });
      this.UI.modal.querySelector('.hrxml-close-modal-btn').addEventListener('click', function (e) {
        e.preventDefault();

        _this2.closeModal();
      });
      this.UI.modal.querySelector('.hrxml-terminal-list').addEventListener('click', function (event) {
        _this2.handleTerminalListEvents(event, _this2.findTerminalElement(event.target));
      });
      this.UI.modal.querySelector('.hrxml-search-input').addEventListener('keyup', function (e) {
        e.preventDefault();
        e.stopPropagation();

        _this2.searchNearestDebounce(e.target.value, e.keyCode == '13');
      });
      this.UI.modal.querySelector('.hrxml-search-btn').addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        _this2.searchNearest(_this2.UI.modal.querySelector('.hrxml-search-input').value);
      });
      this.UI.modal.querySelector('.hrxml-geolocation-btn').addEventListener('click', function (e) {
        e.preventDefault();

        _this2.useGeolocation();
      });
    }
  }, {
    key: "openModal",
    value: function openModal() {
      if (!this.isModal) {
        return this;
      }

      this.bodyOverflow(false);
      this.UI.modal.classList.remove('hrxml-hidden');
      this.TMJS.map.zoomMap();
      this.TMJS.publish('modal-opened', true);
      return this;
    }
  }, {
    key: "closeModal",
    value: function closeModal() {
      if (!this.isModal) {
        return this;
      }

      this.bodyOverflow();
      this.UI.modal.classList.add('hrxml-hidden');
      this.TMJS.publish('modal-closed', true);
      return this;
    }
  }, {
    key: "findTerminalElement",
    value: function findTerminalElement(target) {
      if (target.tagName === 'BODY') {
        return null;
      }

      if (target instanceof HTMLElement && target.classList.contains('hrxml-terminal')) {
        return target;
      }

      return target.parentElement ? this.findTerminalElement(target.parentElement) : null;
    }
  }, {
    key: "useGeolocation",
    value: function useGeolocation() {
      if (!navigator.geolocation) {
        console.log('Browser doesnt support geolocation');
      } else {
        //status.textContent = 'Locating…';
        console.log('Getting coords...');
        this.TMJS.publish('add-search-loader');
        navigator.geolocation.getCurrentPosition(this.geoLocationSuccess.bind(this), this.geoLocationError.bind(this));
      }
    }
  }, {
    key: "geoLocationSuccess",
    value: function geoLocationSuccess(position) {
      this._lastSearchTerm = '';
      var referencePoint = {
        lat: position.coords.latitude,
        lng: position.coords.longitude
      };
      console.log('Your position', referencePoint);
      this.TMJS.map.addReferencePosition(referencePoint);
      this.TMJS.publish('geolocation', referencePoint);
      this.renderTerminalList(this.TMJS.map.addDistance(referencePoint), true);
    }
  }, {
    key: "geoLocationError",
    value: function geoLocationError() {
      this.UI.modal.querySelector('.hrxml-search-result').innerText = this.TMJS.strings.geolocation_not_supported;
      console.log('wasnt able to retrieve position');
    }
    /**
     * 
     * @param {MouseEvent} event 
     * @param {HTMLElement} data 
     */

  }, {
    key: "handleTerminalListEvents",
    value: function handleTerminalListEvents(event, data) {
      event.stopPropagation();
      event.preventDefault(); // non terminal element

      if (!data) {
        return;
      }

      if (event.target.classList.contains('hrxml-select-btn')) {
          
        //console.log('Trying to select terminal:', data.dataset.id);
        jQuery('input[name="hrx_global_terminal"]').val(data.dataset.id).trigger('change');
        /*
        jQuery.ajax({
          type: 'POST',
          headers: { "cache-control": "no-cache" },
          url: hrx_front_controller_url,
          cache: false,
          dataType: 'json',
          data: 'action=saveTerminal' + '&terminal=' + data.dataset.id,
          success: function(jsonData)
          {
              //console.log(jsonData);
          },
          error: function(XMLHttpRequest, textStatus, errorThrown) {
              if (textStatus !== 'abort'){
                  if (!!jQuery.prototype.fancybox)
                  jQuery.fancybox.open([
                      {
                          type: 'inline',
                          autoScale: true,
                          minHeight: 30,
                          content: '<p class="fancybox-error">' + hrxlt_parcel_terminal_error + '</p>'
                      }],
                      {
                          padding: 0
                      }
                  );
                  else
                      alert(hrxlt_parcel_terminal_error);
              }
          }
      });*/
        this.TMJS.publish('terminal-selected', this.TMJS.map.getActiveLocation()); // seting scrollIntoView as false since we already see it.
        //this.setActiveTerminal(target.dataset.id, false);

        return;
      } // Everything else assume as selecting terminal


      console.log('Pressed on terminal:', data.dataset.id, data.innerText); // seting scrollIntoView as false since we already see it.

      this.setActiveTerminal(data.dataset.id, false);
    }
  }, {
    key: "renderTerminalList",
    value: function renderTerminalList(terminals) {
      var _this3 = this;

      var force = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
      var listHTML = [];
      var city = false;

      if (this.UI.terminalList) {
        this.UI.terminalList.innerHTML = '';
      }

      terminals.forEach(function (loc) {
        if (city !== loc.city.toLowerCase()) {
          city = loc.city.toLowerCase();

          var cityEl = _this3.createElement('li', {
            classList: ['hrxml-city']
          });

          cityEl.innerText = loc.city.toLocaleUpperCase();
          listHTML.push(cityEl);
        }

        var selectBtnHidden = _this3.hideSelectBtn ? 'hrxml-hidden' : '';
        var name = loc.name == null ? '' : "".concat(loc.name, ", ");
        var template = "<span class=\"hrxml-terminal-name\">".concat(name).concat(loc.address);

        if (typeof loc.distance != 'undefined' && loc.distance !== null) {
          template += "<span class=\"hrxml-terminal-distance\"><img src=\"".concat(_this3.TMJS.imagePath, "my-loc.svg\" width=\"13\">").concat(loc.distance.toFixed(2), " km.</span>");
        }

        template += "</span><div class=\"hrxml-terminal-info\"><p class=\"hrxml-terminal-comment\">";

        if (loc.comment) {
          template += "<img src=\"".concat(_this3.TMJS.imagePath, "info.svg\" width=\"17\" style=\"margin-bottom:-1px; padding-right:5px;\">").concat(loc.comment);
        }

        template += "</p><a href=\"#terminalSelected\" class=\"hrxml-select-btn hrx-btn ".concat(selectBtnHidden, "\" data-hrxml-string=\"select_btn\">").concat(_this3.TMJS.strings.select_btn, "</a>\n      </div>\n      ");
        /* check if we allready have html object, otherwise create new one */

        var li = Object.prototype.toString.call(loc._li) == '[object HTMLLIElement]' && !force ? loc._li : _this3.createElement('li', {
          classList: ['hrxml-terminal'],
          innerHTML: template
        });
        li.dataset.id = loc.id;
        listHTML.push(li);
        loc._li = li;
      });
      var docFrag = document.createDocumentFragment();
      listHTML.forEach(function (el) {
        return docFrag.appendChild(el);
      });
      this.UI.terminalList.appendChild(docFrag);
      this.TMJS.publish('list-updated', this.UI.terminalList);
      return this;
    }
  }, {
    key: "setActiveTerminal",
    value: function setActiveTerminal(id) {
      var scrollIntoView = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

      if (this.TMJS.map.isActive(id)) {
        console.log('Allready active');

        if (scrollIntoView) {
          var _location = this.TMJS.map.getActiveLocation();

          this.scrollIntoView(_location._li);
        }

        return;
      }
      var location = this.TMJS.map.getLocationById(id);

      if (!location) {
        console.log('Location has no List element associated');
        return;
      }

      this.UI.terminalList.querySelectorAll("li.hrxml-active").forEach(function (el) {
        return el.classList.remove('hrxml-active');
      });

      location._li.classList.add('hrxml-active');

      if (scrollIntoView) {
        this.scrollIntoView(location._li);
      }

      this.TMJS.map.zoomToMarker(location._marker);
      this.TMJS.map.setActiveLocation(location);
    }
  }, {
    key: "scrollIntoView",
    value: function scrollIntoView(el) {
      el.scrollIntoView({
        behavior: 'smooth',
        block: 'nearest',
        inline: 'start'
      });
    }
  }, {
    key: "searchNearestDebounce",
    value: function searchNearestDebounce(search) {
      var force = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
      clearTimeout(this._searchTimeoutId);
      /* if enter is pressed no need to wait */

      if (force) {
        this.searchNearest(search);
        return;
      }

      this._searchTimeoutId = setTimeout(this.searchNearest.bind(this), this.TIMEOUT_DEBOUCE, search);
    }
  }, {
    key: "resetSearch",
    value: function resetSearch() {
      this._lastSearchTerm = '';
      this.renderTerminalList(this.TMJS.map.resetDistance(), true);
      this.TMJS.map.removeReferencePosition();
      this.TMJS.publish('reset-search-result');
    }
  }, {
    key: "searchNearest",
    value: function searchNearest(search) {
      var _this4 = this;

      clearTimeout(this._searchTimeoutId);
      /* reset dropdown if search is empty */

      if (!search.length) {
        this.resetSearch();
        return;
      }

      if (search === this._lastSearchTerm) {
        console.log('Search term hasnt changed');
        return;
      }

      this.TMJS.publish('add-search-loader');
      this._lastSearchTerm = search;
      var queryParams = {
        sourceCountry: this.TMJS.country_code ? this.TMJS.country_code : false,
        singleLine: search,
        category: '',
        outFields: 'Postal',
        maxLocations: 1,
        forStorage: 'false',
        f: 'pjson'
      };
      var query = Object(_Tools_js__WEBPACK_IMPORTED_MODULE_0__["makeQueryParams"])(queryParams);
      fetch("https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/findAddressCandidates?" + query).then(function (response) {
        if (!response.ok) throw new Error(response.status);
        return response.json();
      }).then(function (json) {
        return _this4.updateDistanceByCandidate(json);
      })["catch"](function (error) {
        return _this4.candidateError(error);
      });
    }
  }, {
    key: "candidateError",
    value: function candidateError(error) {
      console.log(error);
    }
  }, {
    key: "updateDistanceByCandidate",
    value: function updateDistanceByCandidate(json) {
      if (typeof json.candidates === 'undefined' || !json.candidates.length) {
        this.UI.modal.querySelector('.hrxml-search-result').innerText = this.TMJS.strings.no_cities_found;
        console.log('Response had no candidates');
        return false;
      }

      var candidates = json.candidates;
      var referencePoint = {
        lat: candidates[0].location.y,
        lng: candidates[0].location.x
      };
      this.TMJS.map.addReferencePosition(referencePoint);
      this.renderTerminalList(this.TMJS.map.addDistance(referencePoint), true);
      this.TMJS.publish('search-result', candidates[0]);
      return true;
    }
  }, {
    key: "showSelected",
    value: function showSelected() {
      if (this.TMJS.map._activeLocation) {
        this.TMJS.map._activeLocation._li.classList.add('hrxml-active');

        this.scrollIntoView(this.TMJS.map._activeLocation._li);
      }
    }
  }]);

  return DOMManipulator;
}();

/***/ }),
/* 4 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "generateId", function() { return generateId; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "makeQueryParams", function() { return makeQueryParams; });
/**
 * Generates random 6 number string
 */
function generateId() {
  return Math.random().toString(36).substr(2, 6);
}
function makeQueryParams(queryParams) {
  var keys = Object.keys(queryParams);

  if (!keys.length) {
    return null;
  }

  return keys.map(function (key) {
    if (queryParams[key] !== false && queryParams[key] !== null && queryParams[key] !== '') {
      return key + '=' + queryParams[key];
    }

    return false;
  }).filter(function (el) {
    return el !== false;
  }).join('&');
}

/***/ }),
/* 5 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Map", function() { return Map; });
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var Map = /*#__PURE__*/function () {
  function Map(root, TMJS) {
    _classCallCheck(this, Map);

    this.TMJS = TMJS;
    this.prefix = TMJS.prefix;
    
    this._tileServerUrl = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
    this._attribution = "\n      &copy; <a href=\"https://www.mijora.lt\">Mijora</a>\n      | Map data &copy; <a href=\"https://www.openstreetmap.org/\">OpenStreetMap</a> contributors,\n      <a href=\"https://creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>\n    ";
    /* default map center Lithuania Kaunas */

    this._defaultMapPos = [54.890926, 23.919338];
    this._map = null;
    this._icons = {};
    this.locations = [];
    this._activeLocation = null;
    this._dummyMarker = null;
    this._referenceMarker = null;
    /* zoom levels for map */

    this.ZOOM_DEFAULT = 8;
    this.ZOOM_SELECTED = 13;
    this.ZOOM_MAX = 18;
    this.ZOOM_MIN = 4; // create map

    this.setupLeafletMap(root);
  }

  _createClass(Map, [{
    key: "loadIcons",
    value: function loadIcons() {
      var _this = this;

      var Icon = L.Icon.extend({
        options: {
          iconSize: [30, 40],
          iconAnchor: [15, 40],
          popupAnchor: [0, -40]
        }
      });
      this._icons["default"] = new Icon({
        iconUrl: this.TMJS.imagePath + 'pins/map-pin.png'
      });
      ['LT','LV','EE','PL','SE','FI'].map(function(country){
        _this._icons[country] = new Icon({
          iconUrl: _this.TMJS.imagePath + 'pins/'+country+'.png'
        });
      });
      this._icons.reference = new Icon({
        iconUrl: this.TMJS.imagePath + 'locator_img.png'
      });
    }
  }, {
    key: "getIcon",
    value: function getIcon() {
      var identifier = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';

      if (Object.keys(this._icons).indexOf(identifier) !== -1) {
        return this._icons[identifier];
      }

      return this._icons["default"];
    }
  }, {
    key: "refreshMarkerIcons",
    value: function refreshMarkerIcons() {
      var _this2 = this;

      this.locations.forEach(function (terminal) {
        //terminal._marker.options.icon = this.getIcon(terminal.identifier);
        terminal._marker.setIcon(_this2.getIcon(terminal.identifier));

        if (_this2._map.hasLayer(terminal._marker)) {
          console.log('Found visible marker'); // terminal._marker.refreshIconOptions();
        }
      });

      if (this._dummyMarker && this._activeLocation) {
        this._dummyMarker.setIcon(this.getIcon(this._activeLocation.identifier));
      }

      this.updateActiveMarkerClass();
    }
  }, {
    key: "setupLeafletMap",
    value: function setupLeafletMap(rootEl) {
      this._map = L.map(rootEl, {
        zoomControl: false,
        minZoom: this.ZOOM_MIN,
        maxZoom: this.ZOOM_MAX
      });
      new L.Control.Zoom({
        position: 'bottomright'
      }).addTo(this._map);
      this.loadIcons();
      L.tileLayer(this._tileServerUrl, {
        attribution: this._attribution
      }).addTo(this._map);
      this._markerLayer = L.markerClusterGroup({//zoomToBoundsOnClick: false
      });
      this._activeMarkerLayer = L.markerClusterGroup({//zoomToBoundsOnClick: false
      });

      this._map.addLayer(this._markerLayer);

      this._map.addLayer(this._activeMarkerLayer);

      if (!this.TMJS.dom.isModal) {
        this._map.setView(this._defaultMapPos, this.ZOOM_DEFAULT);
      }

      return this;
    }
  }, {
    key: "sortByCity",
    value: function sortByCity(a, b) {
      var result = a.city.toLocaleLowerCase().localeCompare(b.city.toLocaleLowerCase());

      if (result == 0 && b.name != null) {
        result = a.name.toLocaleLowerCase().localeCompare(b.name.toLocaleLowerCase());
      } else if (result == 0) {
        result = a.address.toLocaleLowerCase().localeCompare(b.address.toLocaleLowerCase());
      }

      return result;
    }
  }, {
    key: "sortByDistance",
    value: function sortByDistance(a, b) {
      return a.distance - b.distance;
    }
  }, {
    key: "setLocations",
    value: function setLocations(locations) {
      this.locations = JSON.parse(JSON.stringify(locations));
      this.locations.sort(this.sortByCity);

      if (this._map) {
        this.updateMapMarkers();
      }
    }
  }, {
    key: "addMarker",
    value: function addMarker(latLong, id) {
      var identifier = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : '';
      var className = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : '';
      return L.marker(latLong, {
        icon: this.getIcon(identifier),
        terminalId: id,
        hrxml_map: this,
        className: className
      }).bindPopup(this.markerPopUpInfo);
    }
    /**
    * Finds location by id and puts location comment into popup.
    * TODO: Could be too resource intensive, maybe better rework addMarker to have exclusive parameter for popup text.
    */

  }, {
    key: "markerPopUpInfo",
    value: function markerPopUpInfo(e) {
      var defaultText = '';

      if (typeof e.options.terminalId !== 'undefined') {
        var location = e.options.hrxml_map.getLocationById(e.options.terminalId);

        if (location) {
          var text = location.comment ? location.comment : location.address;
          var distance = location.distance ? " ( ".concat(location.distance.toFixed(2), " km )") : '';
          return "".concat(text, " ").concat(distance);
        }

        defaultText = e.options.terminalId + '<br/>';
      }

      var latLng = e.getLatLng();

      if (!latLng) {
        return '';
      }

      return "".concat(defaultText, "Lat: ").concat(latLng.lat, " Long: ").concat(latLng.lng);
    }
  }, {
    key: "addReferencePosition",
    value: function addReferencePosition(coords) {
      this.removeReferencePosition();
      this._referenceMarker = this.addMarker(coords, this.TMJS.strings.your_position, 'reference');

      this._map.addLayer(this._referenceMarker);

      this._map.setView(coords, this.ZOOM_SELECTED);
    }
  }, {
    key: "removeReferencePosition",
    value: function removeReferencePosition() {
      if (this._referenceMarker) {
        this._map.removeLayer(this._referenceMarker);
      }
    }
  }, {
    key: "updateMapMarkers",
    value: function updateMapMarkers() {
      var _this3 = this;

      // only update markers if we have terminal list
      if (!this.locations.length) {
        return this;
      }

      if (this._markerLayer !== null) {
        this._markerLayer.clearLayers();
      }
      /* add markers to marker layer and link icon in locations list */


      var markers = [];
      this.locations.forEach(function (location) {
        location._marker = _this3.addMarker(location.coords, location.id, location.identifier);
        markers.push(location._marker);
      });

      this._markerLayer.addLayers(markers);

      this._activeMarkerLayer.on('click', function (e) {
        _this3._map.setView(e.layer.getLatLng(), _this3._map.getZoom());

        _this3.TMJS.dom.setActiveTerminal(e.layer.options.terminalId);
      });

      this._markerLayer.on('click', function (e) {
        _this3._map.setView(e.layer.getLatLng(), _this3._map.getZoom());

        _this3.TMJS.dom.setActiveTerminal(e.layer.options.terminalId);
      });

      this._markerLayer.on('animationend', function (e) {
        _this3.updateActiveMarkerClass();
      }); // Update default center


      this._defaultMapPos = this._markerLayer.getBounds().getCenter();
      return this;
    }
  }, {
    key: "updateActiveMarkerClass",
    value: function updateActiveMarkerClass() {
      if (this._dummyMarker) {
        // there is dummy
        this._dummyMarker._icon.classList.add('hrxml-active-marker');
      } // hide marker in general layer


      if (this._activeLocation && this._activeLocation._marker._icon) {
        this._activeLocation._marker._icon.classList.add('hrxml-active-marker-hidden');
      }
    }
  }, {
    key: "setActiveLocation",
    value: function setActiveLocation(location) {
      this.removeActiveMarkerAnimations();
      this._activeLocation = location;
      this.updateActiveMarkerLayer();
      this.updateActiveMarkerClass();
    }
  }, {
    key: "zoomToActiveMarker",
    value: function zoomToActiveMarker() {
      // assume marker is in activeMarkerLayer
      this._markerLayer.zoomToShowLayer(this._activeLocation._marker);
    }
  }, {
    key: "zoomMap",
    value: function zoomMap() {
      if (this._activeLocation) {
        this._map.setView(this._activeLocation.coords, this.ZOOM_MAX);

        return;
      }

      this._map.setView(this._defaultMapPos, this.ZOOM_DEFAULT);
    }
  }, {
    key: "zoomToMarker",
    value: function zoomToMarker(marker) {
      this._markerLayer.zoomToShowLayer(marker);
    }
  }, {
    key: "updateActiveMarkerLayer",
    value: function updateActiveMarkerLayer() {
      if (this._dummyMarker) {
        this._map.removeLayer(this._dummyMarker);
      }

      this._dummyMarker = this.addMarker(this._activeLocation._marker._latlng, this._activeLocation.id, this._activeLocation.identifier, 'hrxml-active-marker');

      this._map.addLayer(this._dummyMarker);

      console.log('Current dummy:', this._dummyMarker);
    }
  }, {
    key: "handleActiveMarkers",
    value: function handleActiveMarkers(location) {
      // no need to do anything if its already active
      if (this._activeLocation == location._marker) {
        return;
      } // move current active


      if (this._activeLocation) {
        this._markerLayer.addLayer(this._activeLocation._marker);

        this._activeMarkerLayer.clearLayers();
      } // move new active


      this._markerLayer.removeLayer(location._marker);

      this._activeMarkerLayer.addLayer(location._marker);

      console.log('active now', this._activeMarkerLayer.getLayers(), this._activeLocation); // add active-marker class

      location._marker._icon.classList.add('hrxml-active-marker');
    }
  }, {
    key: "removeActiveMarkerAnimations",
    value: function removeActiveMarkerAnimations() {
      this.TMJS.dom.UI.modal.querySelectorAll('.hrxml-active-marker-hidden').forEach(function (el) {
        return el.classList.remove('hrxml-active-marker-hidden');
      });
    }
  }, {
    key: "getLocationById",
    value: function getLocationById(id) {
      if (!this.locations) {
        return undefined;
      }

      return this.locations.find(function (loc) {
        return loc.id === id;
      });
    }
  }, {
    key: "resetDistance",
    value: function resetDistance() {
      this.locations.forEach(function (loc) {
        loc.distance = null;
      });
      this.locations.sort(this.sortByCity);
      return this.locations;
    }
  }, {
    key: "addDistance",
    value: function addDistance(origin) {
      var _this4 = this;

      this.locations.forEach(function (loc) {
        loc.distance = _this4.calculateDistance(origin, loc.coords);
      });
      this.locations.sort(this.sortByDistance);
      return this.locations;
    }
  }, {
    key: "deg2rad",
    value: function deg2rad(degress) {
      return degress * Math.PI / 180;
    }
  }, {
    key: "rad2deg",
    value: function rad2deg(radians) {
      return radians * 180 / Math.PI;
    }
  }, {
    key: "calculateDistance",
    value: function calculateDistance(loc1, loc2) {
      var distance = null;

      if (loc1.lat == loc2.lat && loc1.lng == loc2.lng) {
        return 0;
      } else {
        var theta = loc1.lng - loc2.lng;
        var dist = Math.sin(this.deg2rad(loc1.lat)) * Math.sin(this.deg2rad(loc2.lat)) + Math.cos(this.deg2rad(loc1.lat)) * Math.cos(this.deg2rad(loc2.lat)) * Math.cos(this.deg2rad(theta));
        dist = Math.acos(dist);
        dist = this.rad2deg(dist);
        distance = dist * 60 * 1.1515 * 1.609344;
      }

      return distance;
    }
  }, {
    key: "isActive",
    value: function isActive(id) {
      return this._activeLocation && this._activeLocation.id == id;
    }
  }, {
    key: "getActiveLocation",
    value: function getActiveLocation() {
      return this._activeLocation;
    }
  }]);

  return Map;
}();

/***/ })
/******/ ]);