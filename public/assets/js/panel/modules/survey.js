/* Survey List */
App.directive('surveyList', function(Helpers,API,$uibModal,$filter) {
  return {
    templateUrl: Helpers.getTemp('survey/survey-list'),
    scope: {},
    link: function($scope, element, $a) {
      $scope.view = $a.view;
      /*
        1: Here is the main object of survey starts
        it includes: (List of surveys, Answer Survey Modal)
      */
      $scope.survey = {
        current_tab: 'all',
        filterResults: {},
        isLoading: true,
        /*
        * 1-1: Filter survey list such as get completed or incompleted surveys and all other filters
        */
        setFilter: function(type,value){
          switch (type) {
            case 'tab':
              if (value == $scope.survey.current_tab) {
                return;
              }
              $scope.survey.current_tab = value;
              $scope.survey.filterResults.completion = value;
            break;
          }
          $scope.survey.getList();
        },
        /*
        * 1-2: Get the survey list results from API
        */
        getList: function(){
          $scope.survey.isLoading = true;
          API.GET('survey/list',$scope.survey.filterResults,true).then(function(d){
            $scope.survey.isLoading = false;
            $scope.survey.list = d.data;
          });
        },
        /*
        * 1-3: On click answer button inside survey list then open the answer survey modal and all related functions
        */
        onAnswer: function(survey){
          $uibModal.open({
            backdrop: 'static',
            templateUrl: Helpers.getTemp('survey/answer-survey-modal'),
            size: 'lg',
            scope: $scope,
            controller: function($uibModalInstance,$location,$scope,$http,$timeout,Flash,Helpers,$route,$filter,$window){
              $scope.answer_survey_modal = {
                surveySectionData: {},
                data: {},
                cancel: function() {
                  $uibModalInstance.close();
                },
                section: {
                  checkIsDone: function(section) {
                    return false;
                  },
                  mainSectionInvalid: {},
                  checkValidation: function() {
                    var final_result = true;
                    angular.forEach($scope.answer_survey_modal.required_questions,function(question){
                      if (!$scope.answer_survey_modal.surveySectionData[question.id].value) {
                        final_result = false;
                      }
                    });
                    return final_result;
                  },
                  checkIsValid: function(section) {
                    var final_result = true;
                    angular.forEach($filter('filter')($scope.answer_survey_modal.required_questions,{main_section_id: section.id},true),function(question){
                      if (!$scope.answer_survey_modal.surveySectionData[question.id].value) {
                        final_result = false;
                      }
                    });
                    $scope.answer_survey_modal.section.mainSectionInvalid[section.id] = (final_result === false) ? true : false;
                    return final_result;
                  },
                  setCurrentMainSection: function($index,Form,formValidity) {
                    if ($scope.answer_survey_modal.isMainSectionLoading) {
                      return false;
                    }
                    if (Form && Form.$dirty) {
                      $scope.answer_survey_modal.onSave(Form,true);
                    }


                    $scope.answer_survey_modal.current_main_section = $scope.answer_survey_modal.data.main_sections[$index];
                    $scope.answer_survey_modal.isMainSectionLoading = true;
                    API.GET('survey/main-section/'+$scope.answer_survey_modal.current_main_section.id).then(function(d){
                      $scope.answer_survey_modal.current_main_section.data = d.data;
                      $timeout(function(){
                        $scope.answer_survey_modal.isMainSectionLoading = false;
                      },200);

                      angular.forEach($scope.answer_survey_modal.current_main_section.data,function(sub_section,sub_section_i){
                        sub_section.all_questions = [];
                        $scope.answer_survey_modal.section.prepareSectionData(sub_section,sub_section);
                      });

                    });
                  },
                  prepareSectionData: function(first_section,section) {
                    angular.forEach(section.details.questions,function(question,question_i){
                      first_section.all_questions.push(question);
                      // If there is editing on this question we should to prevent field reset
                      if (!$scope.answer_survey_modal.surveySectionData[question.id]) {
                        if (question.last_answer_value) {
                          // Prepare values of fields
                          switch (question.type) {
                            case 'checkbox':
                              var splitCheckboxValue = (question.last_answer_value.value) ? question.last_answer_value.value.split(',') : [],
                                  prepareCheckboxValue = {};
                              angular.forEach(splitCheckboxValue,function(option,option_key){
                                prepareCheckboxValue[option] = true;
                              });
                              $scope.answer_survey_modal.surveySectionData[question.id] = {
                                value: prepareCheckboxValue
                              };
                            break;
                            case 'select_with_other':
                              if (!$filter('filter')(question.options,{id: parseInt(question.last_answer_value.value)},true).length) {
                                $scope.answer_survey_modal.surveySectionData[question.id] = {
                                  value: 'other',
                                  other_value: question.last_answer_value.value
                                };
                              }else {
                                $scope.answer_survey_modal.surveySectionData[question.id] = {
                                  value: parseInt(question.last_answer_value.value)
                                };
                              }
                            break;
                            case 'select':
                              if (question.last_answer_value.survey_question_option_id) {
                                $scope.answer_survey_modal.surveySectionData[question.id] = {
                                  value: parseInt(question.last_answer_value.value)
                                };
                              }else {
                                $scope.answer_survey_modal.surveySectionData[question.id] = {
                                  value: question.last_answer_value.value
                                };
                              }
                            break;
                            case 'timerange':
                              var time_split = question.last_answer_value.value.split('-');
                              $scope.answer_survey_modal.surveySectionData[question.id] = {
                                value: time_split[0],
                                to_value: time_split[1]
                              };
                            break;
                            case 'date_hijri': case 'date':
                              var split_date = question.last_answer_value.value.split('-');
                              $scope.answer_survey_modal.surveySectionData[question.id] = {
                                value: {
                                  month: split_date[1],
                                  day: split_date[2]
                                }
                              };
                            break;
                            case 'number':
                              $scope.answer_survey_modal.surveySectionData[question.id] = {
                                value: parseInt(question.last_answer_value.value)
                              };
                            break;
                            default:
                              $scope.answer_survey_modal.surveySectionData[question.id] = {
                                value: question.last_answer_value.value
                              };
                            break;
                          }
                          $scope.answer_survey_modal.surveySectionData[question.id].notes = question.last_answer_value.notes;
                          if (question.is_has_notes && question.last_answer_value.notes) {
                            $scope.answer_survey_modal.surveySectionData[question.id].show_notes_field = true;
                          }
                        }else {
                          // Set default value
                          var question_default_value = null;
                          switch (question.type) {
                            case 'checkbox':
                              question_default_value = {};
                            break;
                          }
                          $scope.answer_survey_modal.surveySectionData[question.id] = {
                            value: question_default_value
                          };
                        }
                      }
                    });
                    if (section.sections.length) {
                      angular.forEach(section.sections,function(sub_section,sub_section_i){
                        $scope.answer_survey_modal.section.prepareSectionData(first_section,sub_section);
                      });
                    }
                  }
                },
                getSurvey: function(){
                  $scope.answer_survey_modal.isLoading = true;
                  API.GET('survey/show/'+survey.id).then(function(d){
                    $scope.answer_survey_modal.isLoading = false;
                    $scope.answer_survey_modal.data = d.data.survey;
                    $scope.answer_survey_modal.required_questions = d.data.required_questions;
                    $scope.answer_survey_modal.section.setCurrentMainSection(0);
                  });
                },
                prepareSendAnswerValues: function(answers){
                  var prepareAnswers = angular.copy(answers);
                  angular.forEach(prepareAnswers,function(item,item_k){
                    if(item.value === null){
                      delete prepareAnswers[item_k];
                    }
                  });
                  return prepareAnswers;
                },
                onSave: function(Form,isHideMessage) {
                  $scope.answer_survey_modal.isSendClicked = true;
                  if (!Helpers.isValid(Form.$valid) || !$scope.answer_survey_modal.section.checkValidation()) {
                    if (!isHideMessage) {
                      Flash.create('danger',$filter('lang')('check_required_fields'));
                    }
                    return false;
                  }
                  $scope.answer_survey_modal.isSending = true;
                  API.POST('survey/answer/'+survey.id,{section_id: $scope.answer_survey_modal.current_main_section.id,answers: $scope.answer_survey_modal.prepareSendAnswerValues($scope.answer_survey_modal.surveySectionData)}).then(function(d){
                    $scope.answer_survey_modal.isSending = false;
                    if (d.data && d.data.message == 'success') {
                      if (!isHideMessage) {
                        Flash.create('success',$filter('lang')('survey.saved_successfully'));
                        $scope.answer_survey_modal.cancel();
                      }
                      $scope.survey.getList();
                      Form.$setPristine();
                    }else if (d.data && d.data.message == 'invalid_fields') {
                      Flash.create('danger',$filter('lang')('check_required_fields'));
                    }else {
                      Flash.create('danger',$filter('lang')('unexpected_error_happened'));
                    }
                  });
                },
                init: function(){
                  $scope.answer_survey_modal.getSurvey();
                }
              };
              $scope.answer_survey_modal.init();
            }
          });
        },
        /*
        * 1-4: Get completion rate
        */
        getCompletionRate: function(survey,is_integer){
          var completion_rate_val = (survey.completed_questions_count/survey.questions_count)*100;
          return (is_integer) ? parseInt(completion_rate_val) : ($filter('number')(completion_rate_val.toString(),1)+'').replace('٫','.');
        },
        /*
        * Init the survey object
        */
        init: function(){
          $scope.survey.getList();
        }
      };
      $scope.survey.init();
    }
  };
});
/* Survey Section */
App.directive('surveySection', function(Helpers,API,$uibModal,$filter) {
  return {
    templateUrl: Helpers.getTemp('survey/survey-section-directive'),
    scope: {
      form: '=',
      mainSection: '=',
      isFirstParentSection: '=',
      isMainSectionInvalid: '=',
      section: '=',
      parentNo: '=',
      surveySectionData: '='
    },
    link: function($scope, element, $a) {
      $scope.no = $a.no;
      $scope.getSectionNo = function(){
        $scope.parent_no_text = (!$scope.isFirstParentSection) ? ($scope.parentNo+1)+'-' : '';
        return $scope.no;
      };
      $scope.collapseSection = function(section){
        if ($scope.isFirstParentSection) {
          section.is_collapsed = !section.is_collapsed;
        }
      };

      $scope.countSectionAnswers = function(section){
        var count = 0;
        angular.forEach(section.all_questions,function(question,question_k){
          if ((question.last_answer_value && question.last_answer_value.value) || (!angular.isObject($scope.surveySectionData[question.id].value) && $scope.surveySectionData[question.id].value) || (angular.isObject($scope.surveySectionData[question.id].value) && Object.keys($scope.surveySectionData[question.id].value).length > 0)) {
            count++;
          }
        });
        return count;
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
        if (section.details.is_apply_percentage) {
          angular.forEach(section.details.questions,function(question){
            if ($scope.surveySectionData[question.id].value) {
              calculate_percentage -=  $scope.surveySectionData[question.id].value;
            }
          })
        }
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
                question.options.push({id: 'other',title: $filter('lang')('other')});
              }
            break;
          }
          result.push(question);
        });
        return result;
      };

      /*
      * Set form dirty
      */
      $scope.setFormDirty = function(){
        if ($scope.form) {
          $scope.form.$setDirty();
        }
      };
    }
  };
});
