App.factory('userFactory', function(Flash,$filter, $uibModal, API,Helpers) {
  var userFactory = {
    /**
    * Delete user
    * @param integer id of user
    * @return
    **/
    delete: function(id,options) {
      if (Helpers.confirmDelete()) {
        API.DELETE('user/delete/'+id).then(function(){
          Flash.create('success','تم حذف المستخدم بنجاح');
          switch (options.view) {
            case 'datatable':
              options.dtInstance.reloadData();
            break;
          }
        })
      }
    },
    /**
    * User modal
    * @param string method (add|edit)
    * @param mixed id of user
    * @param object options, we use it to pass any additional data to this function
    * @return string
    **/
    modal: function(method,id,options) {
      $uibModal.open({
        backdrop: 'static',
        templateUrl: Helpers.getTemp('user/user-modal'),
        size: 'sm',
        controller: function($uibModalInstance,$location,$scope,$http,$timeout,Flash,Helpers,$route,$filter,$window){
          $scope.user_modal = {
            method: method,
            data: {
              general_user_role: 0
            },
            cancel: function() {
              $uibModalInstance.close();
            },
            getUser: function(){
              $scope.user_modal.isLoading = true;
              API.GET('user/show/'+id).then(function(d){
                $scope.user_modal.isLoading = false;
                $scope.user_modal.data = d.data;

                if (d.data.is_supervisor) {
                  $scope.user_modal.data.general_user_role = 2;
                }else if (d.data.is_admin) {
                  $scope.user_modal.data.general_user_role = 1;
                }else {
                  $scope.user_modal.data.general_user_role = 0;
                }
              });
            },
            onSave: function(Form,isNew) {
              $scope.user_modal.isSendClicked = true;
              if (!Helpers.isValid(Form.$valid)) {
                Flash.create('danger',$filter('lang')('check_required_fields'));
                return false;
              }
              if ($scope.user_modal.data.general_user_role == 2) {
                $scope.user_modal.data.is_supervisor = 1;
              }else if ($scope.user_modal.data.general_user_role == 1) {
                $scope.user_modal.data.is_supervisor = 0;
                $scope.user_modal.data.is_admin = 1;
              }else {
                $scope.user_modal.data.is_supervisor = 0;
                $scope.user_modal.data.is_admin = 0;
              }
              $scope.user_modal.isSending = true;
              var saveUserCall = (method == 'add') ? API.POST('user/add',$scope.user_modal.data) : API.PUT('user/update/'+$scope.user_modal.data.id,$scope.user_modal.data);
              saveUserCall.then(function(d){
                $scope.user_modal.isSending = false;
                if (d.data.message == 'email_already_exists') {
                  Flash.create('danger','البريد الألكتروني موجود مسبقاً');
                }else if (d.data.message == 'username_already_exists') {
                  Flash.create('danger','اسم المستخدم موجود مسبقاً');
                }else {
                  if (options && options.view == 'datatable') {
                    options.dtInstance.reloadData();
                  }
                  if (isNew) {
                    $scope.user_modal.data = {};
                  }else {
                    $scope.user_modal.cancel();
                  }
                }
              });
            },
            init: function(){
              if ($scope.user_modal.method == 'edit') {
                $scope.user_modal.getUser();
              }


            }
          };
          $scope.user_modal.init();
        }
      });
    }
  };
  return userFactory;
});
