
/* Presentation List */
App.directive('presentationList', function(Helpers,API,$uibModal,$filter) {
  return {
    templateUrl: Helpers.getTemp('presentation/presentation-list'),
    scope: {},
    link: function($scope, element, $a) {
      $scope.view = $a.view;
      /*
        1: Here is the main object of presentation starts
        it includes: (List of presentations, Presentation Modal)
      */
      $scope.presentation = {
        current_tab: 'all',
        filterResults: {
          take: 3
        },
        isLoading: true,
        /*
        * 1-1: Filter presentation list such as get downloaded or not downloaded presentations and all other filters
        */
        setFilter: function(type,value){
          switch (type) {
            case 'tab':
              if (value == $scope.presentation.current_tab) {
                return;
              }
              $scope.presentation.current_tab = value;
              $scope.presentation.filterResults.download = value;
            break;
          }
          $scope.presentation.getList();
        },
        /*
        * 1-2: Get the presentation list results from API
        */
        getList: function(){
          $scope.presentation.isLoading = true;
          API.GET('presentation/list',$scope.presentation.filterResults,true).then(function(d){
            $scope.presentation.isLoading = false;
            $scope.presentation.list = d.data;
          });
        },
        /*
        * Init the presentation object
        */
        init: function(){
          $scope.presentation.getList();
        }
      };
      $scope.presentation.init();
    }
  };
});
