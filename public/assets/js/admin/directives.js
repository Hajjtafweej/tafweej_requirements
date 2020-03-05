/*
directives.js requires all angularjs directives
Echarts
Mask Input
ngEnter
ngLoading
ngFocus
timePicker
usersRolesList
*/
/* Echarts */
echarts.registerTheme('App',{
  color: [
    '#0DA2B1', '#11adbd', '#16b7c7', '#1dc3d4', '#42e6ba', '#42e6ba', '#67e6c4'
  ],
  valueAxis: {
    axisLine: {
      lineStyle: {
        color: ['#C4C7CC']
      }
    },
    axisLabel: {
      color: '#313233'
    },
    splitArea : {
      show: false
    },
    splitLine: {
      lineStyle: {
        color: ['#DDE0E6']
      }
    }
  },
  categoryAxis: {
    axisLine: {
      lineStyle: {
        color: ['#C4C7CC']
      }
    },
    axisLabel: {
      color: '#313233'
    },
    splitArea : {
      show: false
    },
    splitLine: {
      lineStyle: {
        color: ['#DDE0E6']
      }
    }
  }

});
App.directive('echarts', function($window,$timeout) {
  return {
    restrict: 'EA',
    scope: {
      options: '=options',
      events: '=events'
    },
    link: function(scope, ele, attrs) {
      if(attrs.initLoader){
        ele.wrap('<div class="echart-init"></div>');
      }
      var chart, options, chartEvent = [];
      chart = echarts.init(ele[0],'App');

      function createChart(options) {
        if (!options) return;

        chart.setOption(options);
        $timeout(function(){
          chart.resize();
        },1);
        angular.element($window).bind('resize', function() {
          chart.resize();
        });

      }

      scope.$watch('options', function(newVal, oldVal) {
        var options = {
          animation: false
        };
        if (scope.options) {
          options = angular.merge(scope.options,options);
        }
        createChart(options);
        $timeout(function(){
          chart.resize();
          if(attrs.initLoader){
            ele.closest('.echart-init').addClass('active');
          }
        },1000);
      });

      scope.$watch('events', function(newVal, oldVal) {
        if (scope.events) {
          if (Array.isArray(scope.events)) {
            scope.events.forEach(function(ele) {
              if (!chartEvent[ele.type]) {
                $timeout(function(){
                  chartEvent[ele.type] = true;
                  chart.on(ele.type, function(param) {
                    ele.fn(param);
                    scope.$apply();
                  });
                },1);
              }
            });
          }
        }
      });
    }
  };
});
/* Mask Input */
App.directive('maskInput', function() {
  return {
    require: 'ngModel',
    scope: {
      maskOptions: '='
    },
    link: function($scope, element, attrs, ngModelCtrl) {
      $scope.maskOptions = (angular.isObject($scope.maskOptions)) ? $scope.maskOptions : {};
      element.mask(attrs.maskInput,$scope.maskOptions);
      ngModelCtrl.$parsers.unshift(function(value) {
        return element.cleanVal();
      });
      ngModelCtrl.$formatters.unshift(function(value) {
        return element.masked(value);
      });
    }
  };
});

/*
ngEnter
*/
App.directive('ngEnter', function() {
  return function(scope, element, attrs) {
    element.bind("keydown keypress", function(event) {
      if(event.which === 13) {
        scope.$apply(function(){
          scope.$eval(attrs.ngEnter, {'event': event});
        });

        event.preventDefault();
      }
    });
  };
});
App.directive('ngFocus', function($timeout) {
  return {
    link: function ( scope, element, attrs ) {
      scope.$watch( attrs.ngFocus, function ( val ) {
        if ( angular.isDefined( val ) && val ) {
          $timeout( function () { element[0].focus(); } );
        }
      }, true);

      element.bind('blur', function () {
        if ( angular.isDefined( attrs.ngFocusLost ) ) {
          scope.$apply( attrs.ngFocusLost );

        }
      });
    }
  };
});
App.directive('ngLoading', function() {
  return {
    link: function($scope,$e,$a) {
      $scope.$watch($a.ngLoading, function (n,o) {
        // Btn loader
        if ($e.hasClass('btn')) {
          var loader_class = 'circle-loader';
          if (n) {
            $e.addClass('btn-loading');
          }
          if (!$e.find('.dot-loader').length && n) {
            $e.append('<span class="'+loader_class+((!n) ? ' d-none' : '')+'"></span>');
          }else {
            if (n) {
              $e.find('.'+loader_class).removeClass('d-none');
            }else {
              $e.find('.'+loader_class).addClass('d-none');
            }
          }
          if(!n) {
            $e.removeClass('btn-loading');
          }
        }
      },true);
    }
  };
});
App.directive('timePicker', function() {
  return {
    template: '<div uib-dropdown is-open="timepicker.isDropdownOpen" auto-close="outsideClick">'+
    '<div ng-if="!isTimerange" class="form-control dropdown-toggle d-flex align-items-center" ng-class="{\'active\': timepicker.isDropdownOpen && timepicker.currentTab == \'from\'}" uib-dropdown-toggle><span class="text-muted" ng-hide="value">{{ "choose_time" | lang }}</span> <span ng-show="value">{{ value }}</span></div>'+
    '<div class="input-group" ng-if="isTimerange" uib-dropdown-toggle><div class="form-control dropdown-toggle d-flex align-items-center" ng-class="{\'active\': timepicker.isDropdownOpen && timepicker.currentTab == \'from\'}" ng-click="timepicker.currentTab = \'from\'"><span class="text-muted" ng-hide="value">{{ "choose_time" | lang }}</span> <span ng-show="value">{{ value }}</span></div><div class="input-group-center"><span class="input-group-text" ng-click="timepicker.currentTab = \'to\'">{{ "to" | lang }}</span></div><div class="form-control dropdown-toggle d-flex align-items-center" ng-class="{\'active\': timepicker.isDropdownOpen && timepicker.currentTab == \'to\'}" ng-click="timepicker.currentTab = \'to\'"><span class="text-muted" ng-hide="toValue">{{ "choose_time" | lang }}</span> <span ng-show="toValue">{{ toValue }}</span></div></div>'+
    '<div uib-dropdown-menu class="dropdown-menu timepicker-dropdown timepicker p-2 dropdown-menu-right"><div class="row">'+
    '<div class="col-7"><div class="label">{{ "hour" | lang }}</div><div class="row mx-0"><div class="col px-0" ng-repeat="hour in timepicker.lists.hours"><button type="button" ng-click="timepicker.onClick(\'hour\',hour)" class="btn btn-light" ng-class="{\'active\': hour == timepicker[timepicker.currentTab].hour}">{{ hour }}</button></div></div></div>'+
    '<div class="col-1 d-flex  align-items-center seperator">:</div>'+
    '<div class="col-4"><div class="label">{{ "minute" | lang }}</div><div class="row mx-0"><div class="col px-0" ng-repeat="minute in timepicker.lists.minutes"><button type="button" ng-click="timepicker.onClick(\'minute\',minute)" class="btn btn-light" ng-class="{\'active\': minute == timepicker[timepicker.currentTab].minute}">{{ minute }}</button></div></div></div>'+
    '</div></div></div>',
    scope: {
      isTimerange: '=',
      value: '=',
      toValue: '=',
      hourStep: '=',
      minuteStep: '=',
      onChange: '&'
    },
    link: function($scope,$e,$a) {
      $scope.isTimerange = (!$scope.isTimerange) ? false : $scope.isTimerange;
      $scope.hourStep = (!$scope.hourStep) ? 1 : $scope.hourStep;
      $scope.minuteStep = (!$scope.minuteStep) ? 5 : $scope.minuteStep;
      $scope.timepicker = {
        isDropdownOpen: false,
        currentTab: 'from',
        from: {
          minute: null,
          hour: null
        },
        to: {
          minute: null,
          hour: null
        },
        prepareLists: function(){
          $scope.timepicker.lists = {
            hours: [],
            minutes: []
          };
          for(var hour_i = 0;hour_i < 24;hour_i++){
            if (hour_i % $scope.hourStep == 0) {
              $scope.timepicker.lists.hours.push(('0'+hour_i).slice(-2));
            }
          }
          for(var minute_i = 0;minute_i < 60;minute_i++){
            if (minute_i % $scope.minuteStep == 0) {
              $scope.timepicker.lists.minutes.push(('0'+minute_i).slice(-2));
            }
          }
        },
        onClick: function(type,value){
          $scope.timepicker[$scope.timepicker.currentTab][type] = value;
          $scope.timepicker.clicked[type] = true;

          if ($scope.timepicker[$scope.timepicker.currentTab].minute && $scope.timepicker[$scope.timepicker.currentTab].hour) {
            $scope[($scope.timepicker.currentTab == 'from') ? 'value' : 'toValue'] = $scope.timepicker[$scope.timepicker.currentTab].hour+':'+$scope.timepicker[$scope.timepicker.currentTab].minute;
            if(!$scope.isTimerange || ($scope.value && $scope.toValue)){
              $scope.onChange();
            }

            if ($scope.timepicker.clicked.minute && $scope.timepicker.clicked.hour) {
              $scope.timepicker.isDropdownOpen = false;
            }
          }
        },
        init: function(){
          $scope.timepicker.prepareLists();
          if ($scope.value) {
            var split_value = $scope.value.split(':');
            $scope.timepicker.from.hour = split_value[0];
            $scope.timepicker.from.minute = split_value[1];
          }
          if ($scope.toValue) {
            var split_to_value = $scope.toValue.split(':');
            $scope.timepicker.to.hour = split_to_value[0];
            $scope.timepicker.to.minute = split_to_value[1];
          }
          $scope.$watchGroup(['hourStep','minuteStep'],function(n,o){
            $scope.timepicker.prepareLists();
          });

          $scope.$watch('timepicker.isDropdownOpen',function(n,o){
            if (n) {
              $scope.timepicker.clicked = {
                minute: false,
                hour: false
              };
            }
          });

        }
      };



      $scope.timepicker.init();

    }
  };
});
App.directive('usersRolesList', function($rootScope) {
  return {
    template: '<ui-select ng-required="required" on-select="onChange()" append-to-body="{{ appendToBody }}" search-enabled="false" ng-model="parent[value]"><ui-select-match placeholder="أختر">{{ $select.selected.name }}</ui-select-match><ui-select-choices repeat="item.id as item in list"><div ng-bind-html="item.name"></div></ui-select-choices></ui-select>',
    scope: {
      allOption: '=',
      parent: '=',
      appendToBody: '=',
      required: '=',
      onChange: '&'
    },
    link: function($scope,$e,$a) {
      $scope.value = $a.value;
      $rootScope.$watch('main_lists',function(n){
        if(n){
          $scope.list = angular.copy($rootScope.main_lists.users_roles);
          if ($scope.allOption && $scope.list) {
            $scope.list.unshift({id: 0,name: 'الكل'});
          }
        }
      });
    }
  };
});
