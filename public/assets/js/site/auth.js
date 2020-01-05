/*
	Includes:
	LoginCtrl
*/

App.directive('afterInitPage', function(API, Helpers) {
  return {
    restrict: 'AE',
    link: function($scope,$e,$a) {
      if ($a.afterInitPage == 'hide') {
        $e.addClass('d-none');
      }else {
        $e.removeClass('d-none');
      }
    }
  };
});

/* Login Ctrl */
App.controller('LoginCtrl',function($scope,$timeout,API,Helpers,Flash){
	$scope.login = {};
	$scope.sendLogin = function(validity){
		$scope.is_send_clicked = true;
		if ($scope.isLoading) {
			return false;
		}
		$scope.invalid_login = false;
		if (!Helpers.isValid(validity)) {
			return false;
		}else {
			$scope.isLoading = true;
		}
		// Save form
		API.is_web = true;
		API.POST('auth/login',$scope.login).then(function(d){
			$scope.isLoading = false;
			if (d.data.message == 'invalid_fields') {
				$scope.form_errors = d.data.errors;
			}else{
				if (d.data.message == 'logged_in') {
					window.location.href = baseUrl+'/panel';
				}else {
					$scope.invalid_login = true;
				}
			}
		});
	};

});
