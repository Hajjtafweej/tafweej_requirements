
/* Home Gallery */
App.directive('homeGallery', function(Helpers,API,$uibModal,$filter,$window) {
  return {
    template: '<div class="header-gallery"><div class="container"><h1 class="widget-title">جديد الصور والفيديو</h1></div>'+
          '<div class="loading" ng-show="header_gallery.isLoading"><div class="dot-loader"></div></div>'+
          '<div ng-hide="header_gallery.isLoading"><div slick settings="slickConfig" ng-if="header_gallery.data.length"><div class="image" ng-repeat="item in header_gallery.data" ng-click="header_gallery.onClick(item,\'show\')"><img ng-src="{{ \'images/thumb_\'+item.path | global_asset: \'uploads\' }}" alt /><div class="d-none buttons d-flex justify-content-center"><span class="btn btn-outline" ng-click="header_gallery.onClick(item,\'show\')">مشاهدة</span><span class="btn btn-outline" ng-click="header_gallery.onClick(item,\'download\')">تحميل</span></div><div class="caption d-flex"><div class="title">{{ item.gallery.title }}</div><div class="date">{{ item.gallery.created_at | dateF }}</div></div></div></div></div></div>',
    scope: {},
    link: function($scope, element, $a) {
      $scope.slickConfig = {
        rtl: true,
        dots: false,
        arrows: true,
        slidesToShow: 5,
        slidesToScroll: 5,
        variableWidth: true,
        infinite: true,
        draggable: true
      };
      /* Start Header Gallery */
      $scope.header_gallery = {
        data: {},
        getResults: function(){
          $scope.header_gallery.isLoading = true;
          API.GET('gallery/recent',{},true).then(function(d){
            $scope.header_gallery.isLoading = false;
            $scope.header_gallery.data = d.data;
          });
        },
        onClick: function(item,type){
          if (type == 'show') {
            $window.open($filter('global_asset')('images/'+item.path,'uploads'), '_blank');
          }
        },
        init: function(){
          $scope.header_gallery.getResults();
        }
      };
      $scope.header_gallery.init();
      /* End Header Gallery */
    }
  };
});
