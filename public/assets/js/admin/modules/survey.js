App.factory('surveyFactory', function(Flash,$filter, $uibModal,$window, API,Helpers) {
  var surveyFactory = {
    /**
    * Export survey answers
    * @param integer id of survey
    * @param mixed onAnswersLoaded
    * @return
    **/
    exportAnswers: function(id,onAnswersLoaded,user_id,is_all_options) {
      if (is_all_options) {
        var sendData = is_all_options;
      }else {
        var sendData = (user_id) ? {user_id: user_id} : {};
      }
      API.GET('survey/export/'+id,sendData).then(function(d){
        onAnswersLoaded();
        $window.open(d.data.file);
      });
    },
    /**
    * Activate/deactivate survey
    * @param integer id of survey
    * @param integer status the value of activation 0 for deactivate and 1 for activate
    * @return
    **/
    activation: function(id,status) {
      API.PUT('survey/activation/'+id,{status: status}).then(function(){
        if (status == 1) {
          Flash.create('success','تم تفعيل الأستبانة بنجاح');
        }else {
          Flash.create('success','تم إلغاء تفعيل الأستبانة');
        }
      });
    },
    /**
    * Delete survey
    * @param integer id of survey
    * @return
    **/
    delete: function(id,options) {
      if (Helpers.confirmDelete()) {
        API.DELETE('survey/delete/'+id).then(function(){
          Flash.create('success','تم حذف الأستبانة بنجاح');
          switch (options.view) {
            case 'datatable':
              options.dtInstance.reloadData();
            break;
          }
        })
      }
    },
    /**
    * Survey info modal
    * @param string method (add|edit)
    * @param mixed survey, used when edit the survey informations from survey modal
    * @return string
    **/
    modalInfo: function(method,survey,options) {
      $uibModal.open({
        backdrop: 'static',
        templateUrl: Helpers.getTemp('survey/survey-modal-info'),
        size: 'sm',
        controller: function($uibModalInstance,$location,$scope,$http,$timeout,Flash,Helpers,$route,$filter,$window){
          $scope.survey_modal_info = {
            method: method,
            data: {},
            cancel: function() {
              $uibModalInstance.close();
            },
            onSave: function(Form) {
              $scope.survey_modal_info.isSendClicked = true;
              if (!Helpers.isValid(Form.$valid)) {
                Flash.create('danger',$filter('lang')('check_required_fields'));
                return false;
              }
              $scope.survey_modal_info.isSending = true;
              if(method == 'clone'){
                var saveSurveyInfoCall = API.POST('survey/clone/'+survey.id, $scope.survey_modal_info.data);
              }else {
                var saveSurveyInfoCall = (method == 'add') ? API.POST('survey/add',$scope.survey_modal_info.data) : API.PUT('survey/update-info/'+survey.id,$scope.survey_modal_info.data);
              }
              saveSurveyInfoCall.then(function(d){
                $scope.survey_modal_info.isSending = false;
                if (method != 'edit') {
                  surveyFactory.editModal(d.data.id);
                }else {
                  survey = angular.extend(survey,d.data);
                }
                if (options && options.view == 'datatable') {
                  options.dtInstance.reloadData();
                }
                $scope.survey_modal_info.cancel();
              });
            },
            init: function(){
              if ($scope.survey_modal_info.method != 'add') {
                  $scope.survey_modal_info.isLoading = true;
                  API.GET('survey/show-info/' + survey.id).then(function (d) {
                      $scope.survey_modal_info.data = d.data;
                      $timeout(function () {
                          $scope.survey_modal_info.isLoading = false;
                      }, 200);
                  });
              }
            }
          };
          $scope.survey_modal_info.init();
        }
      });
    },
    /**
    * Edit survey modal
    * @param string method (add|edit)
    * @param integer id
    * @return string
    **/
    editModal: function(id,options) {
      $uibModal.open({
        backdrop: 'static',
        templateUrl: Helpers.getTemp('survey/survey-modal'),
        size: 'lg',
        controller: function($uibModalInstance,$location,$scope,$http,$timeout,Flash,Helpers,$route,$filter,$window,surveyFactory){
          $scope.survey_modal = {
            data: {},
            cancel: function() {
              $uibModalInstance.close();
            },
            editInfo: function(){
              surveyFactory.modalInfo('edit',$scope.survey_modal.data,options);
            },
            section: {
              sortableOptions: {
                handle: '.survey-section-title',
                beforeStart: function(e, ui) {
                  $('.survey-section').addClass('collapsed');
                },
                update: function(e, ui) {
                  // Update fields sort order
                  delay(function(){
                    var sort_order = [];
                    angular.forEach($scope.current_field_module.value,function(v,k){
                      sort_order.push(v.id);
                    });
                    // API.PUT('settings/custom-field/update-sort',{sort: sort_order},true);
                  },1000);
                },
                axis: 'y'
              },
              setCurrentMainSection: function($index,Form,formValidity) {
                if ($scope.survey_modal.isMainSectionLoading) {
                  return false;
                }

                $scope.survey_modal.current_main_section = $scope.survey_modal.data.main_sections[$index];
                $scope.survey_modal.isMainSectionLoading = true;
                API.GET('survey/main-section/'+$scope.survey_modal.current_main_section.id).then(function(d){
                  $scope.survey_modal.current_main_section.data = d.data;
                  $timeout(function(){
                    $scope.survey_modal.isMainSectionLoading = false;
                  },200);

                  angular.forEach($scope.survey_modal.current_main_section.data,function(sub_section,sub_section_i){
                    sub_section.all_questions = [];
                  });

                });
              },
              modal: function(survey_section_method,parent_id,sections_list,section_data,section_index) {
                $uibModal.open({
                  backdrop: 'static',
                  templateUrl: Helpers.getTemp('survey/survey-section-modal'),
                  size: 'sm',
                  scope: $scope,
                  controller: function($uibModalInstance,$location,$scope,$http,$timeout,Flash,Helpers,$route,$filter,$window){
                    $scope.survey_section_modal = {
                      method: survey_section_method,
                      data: {
                        survey_id: $scope.survey_modal.data.id,
                        parent_id: parent_id
                      },
                      cancel: function() {
                        $uibModalInstance.close();
                      },
                      onSave: function(surveySectionForm) {
                        if (!Helpers.isValid(surveySectionForm.$valid)) {
                          Flash.create('danger',$filter('lang')('check_required_fields'));
                          return false;
                        }
                        $scope.survey_section_modal.isSending = true;
                        if (survey_section_method == 'edit') {
                          var section_data_id = (parent_id == 0) ? section_data.id : section_data.details.id;
                        }
                        var saveMainSectionCall = (survey_section_method == 'add') ? API.POST('survey/section/add',$scope.survey_section_modal.data) : API.PUT('survey/section/update/'+section_data_id,$scope.survey_section_modal.data);
                        saveMainSectionCall.then(function(d){
                          $scope.survey_section_modal.isSending = false;
                          if (survey_section_method == 'add') {
                            if (parent_id == 0) {
                              $scope.survey_modal.data.main_sections.push(d.data);
                              if ($scope.survey_modal.data.main_sections.length == 1) {
                                $scope.survey_modal.section.setCurrentMainSection(0);
                              }
                            }else {
                              d.data.questions = [];
                              sections_list.push({
                                details: d.data,
                                sections: []
                              });
                            }
                          }else {
                            if (parent_id == 0) {
                              section_data = angular.extend(section_data,$scope.survey_section_modal.data);
                            }else {
                              section_data.details = angular.extend(section_data.details,$scope.survey_section_modal.data);
                            }
                          }
                          $scope.survey_section_modal.cancel();
                        });
                      },
                      init: function(){
                        if ($scope.survey_section_modal.method == 'edit') {
                          console.log(section_data);
                          
                          if (parent_id == 0) {
                            $scope.survey_section_modal.data = angular.copy(section_data);
                          }else {
                            $scope.survey_section_modal.data = angular.copy(section_data.details);
                          }
                        }
                      }
                    };
                    $scope.survey_section_modal.init();
                  }
                });
              },
              /**
              * Delete section
              * @param integer id of section
              * @return
              **/
              delete: function(section_id,section_index,parent_sections) {
                if (Helpers.confirmDelete()) {
                  API.DELETE('survey/section/delete/'+section_id).then(function(){
                    parent_sections.splice(section_index,1);
                  })
                }
              },
            },
            question: {
              modal: function(survey_question_method,section,question_data,question_index) {
                $uibModal.open({
                  backdrop: 'static',
                  templateUrl: Helpers.getTemp('survey/survey-question-modal'),
                  size: 'sm',
                  scope: $scope,
                  controller: function($uibModalInstance,$location,$scope,$http,$timeout,Flash,Helpers,$route,$filter,$window){
                    $scope.survey_question_modal = {
                      method: survey_question_method,
                      types_list: [
                        {code: 'input',name: 'حقل نصي من سطر واحد'},
                        {code: 'textarea',name: 'حقل نصي متعدد الأسطر'},
                        {code: 'select',name: 'قائمة منسدلة'},
                        {code: 'select_with_other',name: 'قائمة منسدلة مع خيار أخرى'},
                        {code: 'number',name: 'حقل رقمي'},
                        {code: 'percentage',name: 'حقل نسبي'},
                        {code: 'checkbox',name: 'مربعات أختيار متعدد'},
                        {code: 'date',name: 'حقل تاريخ'},
                        {code: 'time',name: 'حقل وقت'},
                        {code: 'timerange',name: 'حقل وقت من وإلى'},
                        {code: 'establishments_list',name: 'قائمة أسماء المؤسسات'},
                        {code: 'hajj_days',name: 'قائمة أيام الحج'},
                        {code: 'makkah_towns_list',name: 'قائمة أحياء مكة المكرمة'},
                        {code: 'madinah_towns_list',name: 'قائمة أحياء المدينة المنورة'},
                        {code: 'ports_list',name: 'قائمة المنافذ'},
                        {code: 'transportation_list',name: 'قائمة وسائل النقل'}
                      ],
                      data: {
                        survey_id: $scope.survey_modal.data.id,
                        survey_section_id: section.details.id,
                        options: [{

                        }],
                        type_options: {minute_step: 1}
                      },
                      cancel: function() {
                        $uibModalInstance.close();
                      },
                      addOption: function() {
                        $scope.survey_question_modal.data.options.push({});
                      },
                      removeOption: function(option,option_index) {
                        if (option.id) {
                          if (!$scope.survey_question_modal.data.deleted_options) {
                            $scope.survey_question_modal.data.deleted_options = [];
                          }
                          $scope.survey_question_modal.data.deleted_options.push(option.id);
                        }
                        $scope.survey_question_modal.data.options.splice(option_index,1);
                      },
                      onSave: function(surveyQuestionForm) {
                        if (!Helpers.isValid(surveyQuestionForm.$valid)) {
                          Flash.create('danger',$filter('lang')('check_required_fields'));
                          return false;
                        }
                        $scope.survey_question_modal.isSending = true;
                        var saveQuestionCall = (survey_question_method == 'add') ? API.POST('survey/question/add',$scope.survey_question_modal.data) : API.PUT('survey/question/update/'+question_data.id,$scope.survey_question_modal.data);
                        saveQuestionCall.then(function(d){
                          $scope.survey_question_modal.isSending = false;
                          if (survey_question_method == 'add') {
                            section.details.questions.push(d.data);
                          }else {
                            question_data = angular.extend(question_data,$scope.survey_question_modal.data);
                          }
                          $scope.survey_question_modal.cancel();
                        });
                      },
                      init: function(){
                        if ($scope.survey_question_modal.method == 'edit') {
                          $scope.survey_question_modal.data = angular.copy(question_data);
                        }
                      }
                    };
                    $scope.survey_question_modal.init();
                  }
                });
              },
              /**
              * Delete question
              * @param object section
              * @param integer question_id
              * @param integer question_index
              * @return
              **/
              delete: function(section,question_id,question_index) {
                if (Helpers.confirmDelete()) {
                  API.DELETE('survey/question/delete/'+question_id).then(function(){
                    section.details.questions.splice(question_index,1);
                  })
                }
              }
            },
            getSurvey: function(){
              $scope.survey_modal.isLoading = true;
              API.GET('survey/show/'+id).then(function(d){
                $scope.survey_modal.isLoading = false;
                $scope.survey_modal.data = d.data;
                if (d.data.main_sections.length) {
                  $scope.survey_modal.section.setCurrentMainSection(0);
                }
              });
            },
            init: function(){
              $scope.survey_modal.getSurvey();
            }
          };
          $scope.survey_modal.init();
        }
      });
    }
  };
  return surveyFactory;
});


/* Survey Section */
App.directive('surveySection', function(Helpers,API,$uibModal,$filter) {
  return {
    templateUrl: Helpers.getTemp('survey/survey-section-directive'),
    scope: {
      surveyModal: '=',
      form: '=',
      mainSection: '=',
      isFirstParentSection: '=',
      sectionIndex: '=',
      parentSections: '=',
      section: '=',
      parentNo: '='
    },
    link: function($scope, element, $a) {
      $scope.no = $a.no;
      $scope.getSectionNo = function(){
        $scope.parent_no_text = (!$scope.isFirstParentSection) ? ($scope.parentNo+1)+'-' : '';
        return $scope.no;
      };
      $scope.collapseSection = function(section,$event){
        if ($scope.isFirstParentSection && ['A','I'].indexOf($($event.target).get(0).tagName) < 0) {
          section.is_collapsed = !section.is_collapsed;
        }
      };

      /*
      * Lists used in select type fields
      */
      $scope.prepareLists = function(list){
        var new_list = [];
        angular.forEach(list,function(list_val,list_key){
          new_list.push({
            key: list_key,
            label: list_val
          });
        });
        return new_list;
      };


      $scope.lists = {
        days: ['01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31'],
        hijri_days: ['01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30'],
        months: ['01','02','03','04','05','06','07','08','09','10','11','12'],
        hajj_days: ['7','8','9','10','11','12','13','14'],
        establishments: $scope.prepareLists(window.lists.establishments),
        makkah_towns: $scope.prepareLists(window.lists.makkah_towns),
        madinah_towns: $scope.prepareLists(window.lists.madinah_towns),
        transportation: $scope.prepareLists(window.lists.transportation),
        ports: $scope.prepareLists(window.lists.ports)
      };


      $scope.getPercentageOptions = function(section){
        var percentage_list = [0];
        var calculate_percentage = 100;
        calculate_percentage = (!calculate_percentage) ? 0 : calculate_percentage;
        for (var i = 0; i <= calculate_percentage; i++) {
          if ( i && (i % 5 === 0)) {
            percentage_list.push(i);
          }
        }
        return percentage_list;
      };

      /*
      * Prepare Question Field
      */
      $scope.prepareSectionQuestions = function(questions){
        var result = [];
        angular.forEach(questions,function(question){
          switch (question.type) {
            case 'select_with_other':
              if (angular.isArray(question.options) && !$filter('filter')(question.options,{id: 'other'}).length) {
                question.options.push({id: 'other',title_ar: $filter('lang')('other')});
              }
            break;
          }
          result.push(question);
        });
        return result;
      };

    }
  };
});
