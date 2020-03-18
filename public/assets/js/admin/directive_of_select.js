function selectizeTemp(){
  return '<selectize ng-if="select_id" ng-attr-id="{{ select_id }}" config="config" ng-model="parent[model]"></selectize>';
}
App.directive('selectUser', function(API,$rootScope,$filter,$timeout,Helpers) {
  return {
    restrict: 'E',
    template: selectizeTemp(),
    scope: {
      default: '=', // For set a default value and prevent them being removed
      parent: '=',
      multiple: '=',
      appendToBody: '=',
      required: '=',
      appendList: '='
    },
    link: function($scope, elem, $a, ctrl) {
      // make unique element id to access selectize api
      $scope.select_id = 'user_selectize_'+getRandomInt(1111,9999);

      $scope.model = ($a.model) ? $a.model : 'users';
      $scope.templateType = 'user';
      if (!$scope.parent) {
        $scope.parent = {};
      }
      if ($scope.parent && !$scope.parent[$scope.model]){
        $scope.parent[$scope.model] = [];
      }

      /* Manage serialization */
      var serialized = false;
      $timeout(function(){
        $scope.$watch('parent.users',function(n,o){
          if ($scope.parent && $scope.parent.id && !serialized) {
            if (angular.isArray(n) && n.length) {
              var append_data = [];
              var append_model = [];
              angular.forEach(n,function(row_v,row_k){
                if (row_v.user) {
                  append_data.push(row_v.user);
                  append_model.push(row_v.user.id);
                }else {
                  append_data.push(row_v);
                  append_model.push(row_v.id);
                }
              });
              $('#'+$scope.select_id)[0].selectize.addOption(append_data);
              $scope.parent[$scope.model] = ($scope.multiple) ? append_model : append_model[0];
            }
            serialized = true;
          }
        });
      },1);

      // Select default option if does not exists in options
      if ($scope.parent[$scope.model] && !$scope.parent.users && !$scope.appendList) {
        API.GET('helpers/list/users', {
                q: $scope.parent[$scope.model],
                type: 'id'
            }, true).then(function (d) {
          $('#'+$scope.select_id)[0].selectize.addOption(d.data);
        });
      }


      // Selectize config
      $scope.config = {
        create: false,
        valueField: 'id',
        searchField: ['name','username'],
        labelField: 'name',
        placeholder: 'ابحث عن مستخدم',
        onItemAdd: function() {
          this.blur();
        },
        render: {
          option: function(item, escape) {
            var meta = '';
            meta = '<div class="text-truncate meta">'+escape(item.username)+'</div>';
            return '<div>'+escape(item.name)+' '+meta+'</div>';
          },
          item: function(item, escape){
            return '<div>'
            + escape(item.name)
            +'<i class="ic-user ml-1"></i></div>';
          }
        },
        load: function(query, callback) {
          if (!query.length) return callback();
          $scope.isLoading = true;
          $.ajax({
            url: '/api/admin/helpers/list/users?is_web=true',
            type: 'GET',
            dataType: 'json',
            data: {
              q: query,
            },
            error: function() {
              $scope.isLoading = false;
              callback();
            },
            success: function(res) {
              $scope.isLoading = false;
              callback(res);
            }
          });
        },
        onInitialize: function(selectize){
          // Append default list
          $scope.$watch('appendList',function(n,o){
            if (n && n.length) {
              selectize.addOption(n);
            }
          });
          // Autofocus
          if('autofocus' in $a){
            selectize.focus();
          }
        },
        dropdownParent: 'body',
        maxItems: ($scope.multiple) ? 200 : 1
      };



    }
  }
});
