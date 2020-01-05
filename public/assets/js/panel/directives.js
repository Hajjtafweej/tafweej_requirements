/*
directives.js requires all angularjs directives
Echarts
Mask Input
ngEnter
ngLoading
ngFocus
*/
/* Echarts */
echarts.registerTheme('App',{
  color: [
    '#395465','#ddbc5d','#45B39D','#AED6F1'
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
