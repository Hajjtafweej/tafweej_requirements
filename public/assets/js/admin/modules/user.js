App.factory('userFactory', function(Flash,$filter, $uibModal, API,Helpers) {
  var userFactory = {
    /**
    * Delete registration
    * @param integer id of registration
    * @return
    **/
    deleteRegistration: function(id,options) {
      if (Helpers.confirmDelete()) {
        API.DELETE('user/registration/delete/'+id).then(function(){
          Flash.create('success','تم حذف طلب التسجيل بنجاح');
          switch (options.view) {
            case 'datatable':
              options.dtInstance.reloadData();
            break;
          }
        })
      }
    },
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
    },
    /**
    * User role modal
    * @param string method (add|edit)
    * @param mixed id of user
    * @param object options, we use it to pass any additional data to this function
    * @return string
    **/
    roleModal: function(method,id,options) {
      $uibModal.open({
        backdrop: 'static',
        templateUrl: Helpers.getTemp('user/user-role-modal'),
        size: 'sm',
        controller: function($uibModalInstance,$location,$scope,$http,$timeout,Flash,Helpers,$route,$filter,$window){
          $scope.user_role_modal = {
            method: method,
            data: {
              general_user_role: 0
            },
            cancel: function() {
              $uibModalInstance.close();
            },
            getUserRole: function(){
              $scope.user_role_modal.isLoading = true;
              API.GET('user/role/show/'+id).then(function(d){
                $scope.user_role_modal.isLoading = false;
                $scope.user_role_modal.data = d.data;

                if (d.data.is_supervisor) {
                  $scope.user_role_modal.data.general_user_role = 2;
                }else if (d.data.is_admin) {
                  $scope.user_role_modal.data.general_user_role = 1;
                }else {
                  $scope.user_role_modal.data.general_user_role = 0;
                }
              });
            },
            onSave: function(Form) {
              $scope.user_role_modal.isSendClicked = true;
              if (!Helpers.isValid(Form.$valid)) {
                Flash.create('danger',$filter('lang')('check_required_fields'));
                return false;
              }
              $scope.user_role_modal.isSending = true;
              var saveUserCall = (method == 'add') ? API.POST('user/role/add',$scope.user_role_modal.data) : API.PUT('user/role/update/'+$scope.user_role_modal.data.id,$scope.user_role_modal.data);
              saveUserCall.then(function(d){
                $scope.user_role_modal.isSending = false;
                if (options && options.view == 'datatable') {
                  options.dtInstance.reloadData();
                }
                $scope.user_role_modal.cancel();
              });
            },
            init: function(){
              if ($scope.user_role_modal.method == 'edit') {
                $scope.user_role_modal.getUserRole();
              }


            }
          };
          $scope.user_role_modal.init();
        }
      });
    },
    /**
    * Delete user role
    * @param integer id of user role
    * @return
    **/
    deleteRole: function(id,options) {
      if (Helpers.confirmDelete()) {
        API.DELETE('user/role/delete/'+id).then(function(d){
          if (d.data.message == 'success') {
            Flash.create('success','تم حذف نوع المستخدم بنجاح');
            switch (options.view) {
              case 'datatable':
              options.dtInstance.reloadData();
              break;
            }
          }else if (d.data.message == 'there_are_users_exist') {
            Flash.create('danger','لا يمنك الحذف لإن هنالك مستخدمين تحت هذا النوع');
          }else {
            Helpers.httpErrorOccurs();
          }
        });
      }
    },
  };
  return userFactory;
});
