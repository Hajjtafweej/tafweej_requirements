
/* Start Angularjs */
var App = angular.module('App',['ngSanitize', 'ngResource', 'angular-loading-bar','ngFlash','ui.bootstrap','thatisuday.dropzone']);
/* API */
App.factory('API', function($http, $location, $rootScope, $window) {
  var api_factory = {
    is_web: false,
    // Prepare JSON Send
    JSON: function(type, path) {
      return {
        headers: {
          'Content-Type': 'application/json'
        },
        url: baseUrl+((api_factory.is_web) ? '/api/web/'+path : '/api/'+path),
        method: type
      }
    },
    // API Get Method
    GET: function(path, data, ignore_bar) {
      var json = this.JSON('GET', path);
      if (data) {
        json.params = data;
      }
      if (ignore_bar) {
        json.ignoreLoadingBar = true;
      }

      var http = $http(json);
      return http.catch(function(e){
        alert('حدث خطأ, يرجى المحاولة مرة اخرى');
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
          alert('حدث خطأ, يرجى المحاولة مرة اخرى');
        }
        return true;
      });
    },
    // API PUT Method
    PUT: function(path, data, ignore_bar) {
      var json = this.JSON('PUT', path);
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
          alert('حدث خطأ, يرجى المحاولة مرة اخرى');
        }
        return true;
      });
    },
    // API DELETE Method
    DELETE: function(path, data, ignore_bar) {
      var json = this.JSON('DELETE', path);
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
          alert('حدث خطأ, يرجى المحاولة مرة اخرى');
        }
        return true;
      });
    }
  };
  return api_factory;
});
/* Helpers */
App.factory('Helpers', function($http, Flash, $location,$rootScope, $timeout,$filter, API) {
  return {
    /**
    * Hide basic html loading before init angularjs
    * @return
    **/
    hideHTMLLoading: function() {
      $('.before-init').fadeOut(function(){
  			$('.after-init').fadeIn();
  		});
    },
    /**
    * When submit form and it still invalidated
    * @param boolean validity
    * @return boolean
    **/
    isValid: function(validity) {
      if (!validity) {
        $('form:visible').addClass('invalid-form');
        return false;
      } else {
        $('form:visible').removeClass('invalid-form');
        return true;
      }
    },
    /**
    * Prepare path of template
    * @param string part of path
    * @return string
    **/
    getTemp: function(path) {
      return home_url+'/assets/templates/' + path + '.html?v=' + assets_ver;
    }

  }

});

App.config(function($interpolateProvider,$locationProvider) {
  $interpolateProvider.startSymbol('{#');
  $interpolateProvider.endSymbol('#}');
  $locationProvider.html5Mode({
    enabled: true,
    requireBase: false,
    rewriteLinks: false
  });
});
App.run(function(Helpers) {
  Helpers.hideHTMLLoading();
});
