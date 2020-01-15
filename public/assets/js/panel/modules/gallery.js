
/* Home Gallery */
App.directive('homeGallery', function(Helpers,API,$uibModal,$filter,$window,$rootScope) {
  return {
    template: '<div class="header-gallery"><div class="container"><h1 class="widget-title">{{ "gallery.recent_title" | lang }}</h1></div><div class="gallery-wrapper">'+
    '<div class="loading" ng-show="header_gallery.isLoading"><div class="dot-loader"></div></div>'+
    '<div class="no-results" ng-show="!header_gallery.isLoading && !header_gallery.data.length">{{ "gallery.no_photos_videos_found" | lang }}</div>'+
    '<div ng-hide="header_gallery.isLoading"><div slick settings="slickConfig" ng-if="header_gallery.data.length"><div class="image" ng-repeat="item in header_gallery.data"><img ng-src="{{ \'images/thumb_\'+item.path | global_asset: \'uploads\' }}" alt /><div class="buttons d-flex justify-content-center"><a class="btn btn-outline rounded" ng-href="{{ (\'images/\'+item.path | global_asset: \'uploads\') }}" target="_blank">{{ ("view" | lang) }}</a><a class="btn btn-outline rounded filled" ng-href="{{ $root.baseUrl+\'/download/file/images/\'+item.path }}">{{ ("download" | lang) }}</a></div><div class="caption d-flex"><div class="date">{{ item.gallery.created_at | dateF }}</div></div></div></div></div></div></div>',
    scope: {},
    link: function($scope, element, $a){
      var slickConfig = {
        dots: false,
        arrows: true,
        slidesToShow: 5,
        slidesToScroll: 1,
        variableWidth: true,
        infinite: true,
        centerMode: true,
        draggable: true,
        responsive: [
          {
            breakpoint: 420,
            settings: {
              dots: true,
              arrows: false,
              slidesToShow: 1,
              slidesToScroll: 1,
              variableWidth: false
            }
          }
        ]
      };
      if (window.lang_dir == 'rtl') {
        slickConfig.rtl = true;
      }
      $scope.slickConfig = slickConfig;
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
        init: function(){
          $scope.header_gallery.getResults();
        }
      };
      $scope.header_gallery.init();
      /* End Header Gallery */
    }
  };
});
