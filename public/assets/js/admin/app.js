var App = angular.module('App', ['ngLocale', 'ngRoute', 'ngCookies', 'ngResource', 'datatables', 'ngFlash', 'ui.bootstrap', 'ui.sortable', 'flow', 'ui.select', 'ngSanitize', 'angularMoment', 'thatisuday.dropzone', 'slickCarousel', 'selectize']);
/* API */
App.factory('API', function($http,$timeout, $location, $rootScope, $window,$filter) {
  var api_factory = {
    is_web: false,
    without_api_prefix: ['flow-uploader/start-import'],
    // Prepare JSON Send
    JSON: function(type, path) {
      var perm_api_perfix = '/admin';
      var api_prefix = (api_factory.without_api_prefix.indexOf(path) == -1) ? '/api'+perm_api_perfix : '';
      return {
        headers: {
          'Content-Type': 'application/json',
          'X-App-Locale': window.current_lang
        },
        url: baseUrl+api_prefix+'/'+path,
        method: type
      }
    },
    // API Get Method
    GET: function(path, data, ignore_bar) {
      var json = this.JSON('GET', path);
      if (!data) {
        var data = {};
      }
      data.is_web = true;
      if (data) {
        json.params = data;
      }
      if (ignore_bar) {
        json.ignoreLoadingBar = true;
      }

      var http = $http(json);
      return http.catch(function(e){
        alert($filter('lang')('unexpected_error_happened'));
        return true;
      });
    },
    // API Post Method
    POST: function(path, data, ignore_bar) {
      var json = this.JSON('POST', path);
      if (data) {
        json.data = data;
      }else {
        json.data = {};
      }
      json.data.is_web = true;
      if (ignore_bar) {
        json.ignoreLoadingBar = true;
      }
      return $http(json).catch(function(e){
        if (!e.data.message) {
          alert($filter('lang')('unexpected_error_happened'));
        }
        return true;
      });
    },
    // API PUT Method
    PUT: function(path, data, ignore_bar) {

      // NOTE: here we just replaced the JSON Method from PUT to POST temporary to be suitable with the backend
      var json = this.JSON('POST', path);
      if (data) {
        json.data = data;
      }else {
        json.data = {};
      }
      json.data.is_web = true;
      if (ignore_bar) {
        json.ignoreLoadingBar = true;
      }
      return $http(json).catch(function(e){
        if (!e.data.message) {
          alert($filter('lang')('unexpected_error_happened'));
        }
        return true;
      });
    },
    // API DELETE Method
    DELETE: function(path, data, ignore_bar) {

      // NOTE: here we just replaced the JSON Method from DELETE to POST temporary to be suitable with the backend
      var json = this.JSON('POST', path);
      if (data) {
        json.data = data;
      }else {
        json.data = {};
      }
      json.data.is_web = true;
      if (ignore_bar) {
        json.ignoreLoadingBar = true;
      }
      return $http(json).catch(function(e){
        if (!e.data.message) {
          alert($filter('lang')('unexpected_error_happened'));
        }
        return true;
      });
    }
  };
  return api_factory;
});

/* Helpers */
App.factory('Helpers', function($cacheFactory,$timeout,$http, Flash, $location,$filter, $uibModal,$rootScope, API) {
  return {
    /**
    * Add some red colors on invalid fields
    * @param boolean validity of form
    * @return boolean
    **/
    isValid: function(validity) {
      if (!validity) {
        $('form').addClass('invalid-form');
        return false;
      } else {
        $('form').removeClass('invalid-form');
        return true;
      }
    },

    /**
    * Prepare path of template
    * @param string part of path
    * @return string
    **/
    getTemp: function(path) {
      return baseUrl+'/assets/templates/admin/' + path + '.html?v=' + assets_ver;
    },
    /**
    * Cache system to store and retrieve data
    * @return confirm
    **/
    Cache: function() {
      return $cacheFactory.get('helpers-cache') || $cacheFactory('helpers-cache');
    },
    /**
    * Format file size
    * @return mixed
    **/
    formatFilesize: function(size) {
      var i = Math.floor( Math.log(size) / Math.log(1024) );
      return ( size / Math.pow(1024, i) ).toFixed(2) * 1 + ' ' + ['B', 'KB', 'MB', 'GB', 'TB'][i];
    },
    /**
    * Confirm delete message
    * @return confirm
    **/
    confirmDelete: function(path) {
      return confirm($filter('lang')('confirm_delete_msg'));
    },
    /**
    * Page Wrap Laoding
    * @return
    **/
    pageWrapLoading: function(isShow,title) {
      var loadingTitle = (title) ? title : 'جاري التحميل';
      if(!$('#page_wrap_loading').length){
        $('body').append('<div id="page_wrap_loading"><div class="page-wrap-loading d-flex justify-content-center align-items-center" style="display: none;"><div><div class="circle-loader"></div><div class="page-wrap-loading-title">'+loadingTitle+'</div></div></div></div>');
        if (isShow) {
          $('#page_wrap_loading').fadeIn();
        }else {
          $('#page_wrap_loading').fadeOut();
        }
      }else {
        $('.page-wrap-loading-title').text(loadingTitle);
        if (isShow) {
          $('#page_wrap_loading').fadeIn();
        }else {
          $('#page_wrap_loading').fadeOut();
        }
      }
    },
    /**
    * Http errors occurs
    * @return Flash
    **/
    httpErrorOccurs: function() {
      return Flash.create('danger','لقد حدث خطأ في الإتصال, يرجى إعادة المحاولة');
    },
    /**
    * Most used flash messages
    * @param message
    * @return Flash
    **/
    flashMessage: function(message) {
      var flash_color = (['invalid_fields'].indexOf(message) > -1) ? 'danger' : 'success',
      flash_msg = '';
      switch (message) {
        case 'invalid_fields':
        flash_msg = $filter('lang')('check_required_fields');
        break;
      }
      if (flash_msg) {
        return Flash.create(flash_color,flash_msg);
      }
    },
    /**
    * Initlize the main lists
    * @param message
    * @return Flash
    **/
    initMainLists: function() {
      if (!$rootScope.main_lists) {
          $rootScope.main_lists = {};
      }
      
      API.GET('helpers/main-lists',{},true).then(function(d){
        $timeout(function(){

          $rootScope.main_lists = angular.extend($rootScope.main_lists,d.data);
        },2000);
      });
    }
  }
});
/* Start Config */
App.config(['$sceDelegateProvider','$routeProvider', '$locationProvider', '$interpolateProvider', 'uiSelectConfig', function($sceDelegateProvider,$routeProvider, $locationProvide, $interpolateProvider, uiSelectConfig) {

  $sceDelegateProvider.resourceUrlWhitelist([
    // Allow same origin resource loads.
    'self',
    // Allow loading from our assets domain. **.
    baseUrl+'/**'
  ]);

  uiSelectConfig.theme = 'bootstrap';
  $interpolateProvider.startSymbol('{{');
  $interpolateProvider.endSymbol('}}');
  // Templates path
  var templates_path = baseUrl+'/assets/templates/admin/';

  $routeProvider
  // .when('/admin/dashboard', {
  //   templateUrl: templates_path+'pages/dashboard.html?v='+assets_ver,
  //   controller: 'DashboardCtrl'
  // })
  .when('/admin/participants', {
    templateUrl: templates_path + 'pages/participants.html?v=' + assets_ver,
    controller: 'DatatableCtrl'
  })
  .when('/admin/requirements', {
      templateUrl: templates_path + 'pages/requirements.html?v=' + assets_ver,
      controller: 'DatatableCtrl'
  })
  // $routeProvider.otherwise('/admin/dashboard');
  $routeProvider.otherwise('/admin/requirements');

}]);

/* Start Run */
App.run(function($rootScope,$location,Helpers) {
  Helpers.initMainLists();
});

/* Add highlight on current active tab */
App.directive('findactivetab', ['$location',
function($location) {
  return {
    link: function postLink(scope, element, attrs) {
      scope.$on("$routeChangeSuccess", function(event, current, previous) {
        var pathToCheck = $location.path().split('/')[attrs.findactivetab] || "current $location.path doesn't reach this level";
        angular.forEach(element.children().not('.' + element.attr('expect-class')), function(item) {
          var a = $(item).children('li > a'),
          parent = (typeof a.attr('href') !== undefined) ? a.attr('href') : a.attr('data-href');
          if (parent != undefined && pathToCheck == parent.split('/')[attrs.findactivetab]) {
            $(item).addClass('active');
          } else {
            $(item).removeClass('active');
          }
        });
      });
    }
  };
}
]);

/* Some jQuery Codes */
$(function() {
  /* Navbar Toggle */
  var toggleNavbar = false;
  $(document).mouseup(function(e) {
    setTimeout(function() {
      if ($('.ui-select-choices').is(':visible') && $('.ui-select-choices').has('.active')) {
        $('.ui-select-choices-row').hover(function() {
          var p = $(this).closest('.ui-select-choices');
          if (!p.find('active').is($(this))) {
            p.find('.active').removeClass('active');
          }
        });
      }
    }, 1);

    if ($('body').hasClass('sidebar-toggled')) {
      var container = $(".navbar-left");
      if (!container.is(e.target) && container.has(e.target).length === 0) {
        $('body').removeClass('sidebar-toggled');
      }
    } else {
      if ($('.sidebar-toggle').is(e.target) || $('.sidebar-toggle i').is(e.target)) {
        if ($('body').hasClass('sidebar-toggled')) {
          $('body').removeClass('sidebar-toggled');
        } else {
          $('body').addClass('sidebar-toggled');
        }
        toggleNavbar = !toggleNavbar;
      }
    }

  });


});

/* Main Ctrl */
App.controller('MainCtrl', function($http,$compile,$rootScope,$scope,$location, Flash, $routeParams, API, Helpers,$filter,$uibModal) {
  $rootScope.appSettings = appSettings;
  $rootScope.baseUrl = baseUrl;
  $rootScope.auth = window.auth;
  $rootScope.curHijriYear = window.curHijriYear;
  $rootScope.curYear = moment().format('YYYY');
  $rootScope.langProp = window.lang_properties;
  /* Sidebar */
  $('.sidebar .has-submenu > a').click(function(){
    $(this).parent().toggleClass('active');
  });
  /* End Sidebar */

});

/**
* Delay any function for specific time to reduce the number of requests to the API
* @param function callback
* @param integer ms timeout
**/
var delay = (function() {
  var timer = 0;
  return function(callback, ms) {
    clearTimeout(timer);
    timer = setTimeout(callback, ms);
  };
})();
function getRandomInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}