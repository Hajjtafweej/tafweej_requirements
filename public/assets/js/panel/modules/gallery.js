
/* Home Gallery */
App.directive('homeGallery', function(Helpers,API,$uibModal,$filter,$window,$rootScope) {
  return {
    template: '<div class="header-gallery"><div class="container"><h1 class="widget-title">جديد الصور والفيديو</h1></div><div class="gallery-wrapper">'+
    '<div class="loading" ng-show="header_gallery.isLoading"><div class="dot-loader"></div></div>'+
    '<div class="no-results" ng-show="!header_gallery.isLoading && !header_gallery.data.length">لا توجد صور أو فيديو ليتم عرضها</div>'+
    '<div ng-hide="header_gallery.isLoading"><div slick settings="slickConfig" ng-if="header_gallery.data.length"><div class="image" ng-repeat="item in header_gallery.data"><img ng-src="{{ \'images/thumb_\'+item.path | global_asset: \'uploads\' }}" alt /><div class="buttons d-flex justify-content-center"><a class="btn btn-outline rounded" ng-href="{{ (\'images/\'+item.path | global_asset: \'uploads\') }}" target="_blank">مشاهدة</a><a class="btn btn-outline rounded filled" ng-href="{{ $root.baseUrl+\'/download/file/images/\'+item.path }}">تحميل</a></div><div class="caption d-flex"><div class="text-truncate title">{{ item.gallery.title }}</div><div class="date">{{ item.gallery.created_at | dateF }}</div></div></div></div></div></div></div>',
    scope: {},
    link: function($scope, element, $a){
      $scope.slickConfig = {
        rtl: true,
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
/* Gallery List */
App.directive('galleryList', function(Helpers,API,$uibModal,$filter) {
  return {
    templateUrl: Helpers.getTemp('gallery/gallery-list'),
    scope: {},
    link: function($scope, element, $a) {
      $scope.view = $a.view;
      /*
      1: Here is the main object of gallery starts
      it includes: (List of gallerys, Answer gallery Modal)
      */
      $scope.gallery = {
        current_tab: 'all',
        filterResults: {},
        isLoading: true,
        /*
        * 1-1: Filter gallery list such as get completed or incompleted gallerys and all other filters
        */
        setFilter: function(type,value){
          switch (type) {
            case 'tab':
            if (value == $scope.gallery.current_tab) {
              return;
            }
            $scope.gallery.current_tab = value;
            $scope.gallery.filterResults.completion = value;
            break;
          }
          $scope.gallery.getList();
        },
        /*
        * 1-2: Get the gallery list results from API
        */
        getList: function(){
          $scope.gallery.isLoading = true;
          API.GET('gallery/list',$scope.gallery.filterResults,true).then(function(d){
            $scope.gallery.isLoading = false;
            $scope.gallery.list = d.data;
          });
        },
        /*
        * 1-3: On click answer button inside gallery list then open the answer gallery modal and all thier related functions
        */
        onAnswer: function(gallery){
          $uibModal.open({
            backdrop: 'static',
            templateUrl: Helpers.getTemp('gallery/answer-gallery-modal'),
            size: 'lg',
            scope: $scope,
            controller: function($uibModalInstance,$location,$scope,$http,$timeout,Flash,Helpers,$route,$filter,$window){
              $scope.answer_gallery_modal = {
                gallerySectionData: {},
                data: {},
                cancel: function() {
                  $uibModalInstance.close();
                },
                section: {
                  checkIsDone: function(section) {

                  },
                  setCurrentMainSection: function($index,Form,formValidity) {
                    if ($scope.answer_gallery_modal.isMainSectionLoading) {
                      return false;
                    }
                    if (Form && Form.$dirty) {
                      if (!Helpers.isValid(Form.$valid)) {
                        Flash.create('danger','يرجى منك التحقق من المدخلات المطلوبة');
                        return false;
                      }else {
                        $scope.answer_gallery_modal.onSave(Form,true);
                      }
                    }


                    $scope.answer_gallery_modal.current_main_section = $scope.answer_gallery_modal.data.main_sections[$index];
                    $scope.answer_gallery_modal.isMainSectionLoading = true;
                    API.GET('gallery/main-section/'+$scope.answer_gallery_modal.current_main_section.id).then(function(d){
                      $scope.answer_gallery_modal.current_main_section.data = d.data;
                      $timeout(function(){

                        $scope.answer_gallery_modal.isMainSectionLoading = false;
                      },200);

                      angular.forEach($scope.answer_gallery_modal.current_main_section.data,function(sub_section,sub_section_i){
                        sub_section.all_questions = [];
                        $scope.answer_gallery_modal.section.prepareSectionData(sub_section,sub_section);
                      });

                    });
                  },
                  prepareSectionData: function(first_section,section) {
                    angular.forEach(section.details.questions,function(question,question_i){
                      first_section.all_questions.push(question);
                      // If there is edit on this question we should prevent reset the field
                      if (!$scope.answer_gallery_modal.gallerySectionData[question.id]) {
                        if (question.last_answer_value) {
                          // Prepare values of fields
                          switch (question.type) {
                            case 'select_with_other':
                            if (!$filter('filter')(question.options,{id: parseInt(question.last_answer_value.value)},true).length) {
                              $scope.answer_gallery_modal.gallerySectionData[question.id] = {
                                value: 'other',
                                other_value: question.last_answer_value.value
                              };
                            }else {
                              $scope.answer_gallery_modal.gallerySectionData[question.id] = {
                                value: parseInt(question.last_answer_value.value)
                              };
                            }
                            break;
                            case 'select':
                            if (question.last_answer_value.gallery_question_option_id) {
                              $scope.answer_gallery_modal.gallerySectionData[question.id] = {
                                value: parseInt(question.last_answer_value.value)
                              };
                            }else {
                              $scope.answer_gallery_modal.gallerySectionData[question.id] = {
                                value: question.last_answer_value.value
                              };
                            }
                            break;
                            case 'time':
                            $scope.answer_gallery_modal.gallerySectionData[question.id] = {
                              value: moment(moment().format('YYYY-MM-DD')+' '+question.last_answer_value.value)
                            };
                            break;
                            case 'timerange':
                            var time_split = question.last_answer_value.value.split('-');
                            $scope.answer_gallery_modal.gallerySectionData[question.id] = {
                              value: moment(moment().format('YYYY-MM-DD')+' '+time_split[0]),
                              to_value: moment(moment().format('YYYY-MM-DD')+' '+time_split[1])
                            };
                            break;
                            case 'date_hijri': case 'date':
                            var split_date = question.last_answer_value.value.split('-');
                            $scope.answer_gallery_modal.gallerySectionData[question.id] = {
                              value: {
                                month: split_date[1],
                                day: split_date[2]
                              }
                            };
                            break;
                            case 'number':
                            $scope.answer_gallery_modal.gallerySectionData[question.id] = {
                              value: parseInt(question.last_answer_value.value)
                            };
                            break;
                            default:
                            $scope.answer_gallery_modal.gallerySectionData[question.id] = {
                              value: question.last_answer_value.value
                            };
                            break;
                          }
                          $scope.answer_gallery_modal.gallerySectionData[question.id].notes = question.last_answer_value.notes;
                          if (question.is_has_notes && question.last_answer_value.notes) {
                            $scope.answer_gallery_modal.gallerySectionData[question.id].show_notes_field = true;
                          }
                        }else {
                          // Set default value
                          var question_default_value = null;
                          switch (question.type) {
                            case 'percentage':

                            break;
                          }
                          $scope.answer_gallery_modal.gallerySectionData[question.id] = {
                            value: question_default_value
                          };
                        }
                      }
                    });
                    if (section.sections.length) {
                      angular.forEach(section.sections,function(sub_section,sub_section_i){
                        $scope.answer_gallery_modal.section.prepareSectionData(first_section,sub_section);
                      });
                    }
                  }
                },
                getgallery: function(){
                  $scope.answer_gallery_modal.isLoading = true;
                  API.GET('gallery/show/'+gallery.id).then(function(d){
                    $scope.answer_gallery_modal.isLoading = false;
                    $scope.answer_gallery_modal.data = d.data;
                    $scope.answer_gallery_modal.section.setCurrentMainSection(0);
                  });
                },
                prepareSendAnswerValues: function(answers){
                  var prepareAnswers = angular.copy(answers);
                  angular.forEach(prepareAnswers,function(item,item_k){
                    if(item.value === null){
                      delete prepareAnswers[item_k];
                    }else {
                      if ($('#question_'+item_k+'_time_value').length) {
                        prepareAnswers[item_k].value = $('#question_'+item_k+'_time_value').val();
                      }
                      if ($('#question_'+item_k+'_time_to_value').length) {
                        prepareAnswers[item_k].to_value = $('#question_'+item_k+'_time_to_value').val();
                      }
                    }
                  });
                  return prepareAnswers;
                },
                onSave: function(Form,isHideMessage) {
                  if (!Helpers.isValid(Form.$valid)) {
                    Flash.create('danger','يرجى منك التحقق من المدخلات المطلوبة');
                    return false;
                  }
                  $scope.answer_gallery_modal.isSending = true;
                  API.POST('gallery/answer/'+gallery.id,{section_id: $scope.answer_gallery_modal.current_main_section.id,answers: $scope.answer_gallery_modal.prepareSendAnswerValues($scope.answer_gallery_modal.gallerySectionData)}).then(function(d){
                    $scope.answer_gallery_modal.isSending = false;
                    if (d.data && d.data.message == 'success') {
                      if (!isHideMessage) {
                        Flash.create('success','تم حفظ الإستبانة بنجاح');
                        $scope.answer_gallery_modal.cancel();
                      }
                      $scope.gallery.getList();
                      Form.$setPristine();
                    }else if (d.data && d.data.message == 'invalid_fields') {
                      Flash.create('danger','يرجى منك التحقق من الحقول جيداً');
                    }else {
                      Flash.create('danger','حدث خطأ في ادخال البيانات يرجى المحاولة مره أخرى');
                    }
                  });
                },
                init: function(){
                  $scope.answer_gallery_modal.getgallery();
                }
              };
              $scope.answer_gallery_modal.init();
            }
          });
        },
        /*
        * 1-4: Get completion rate
        */
        getCompletionRate: function(gallery,is_integer){
          var completion_rate_val = (gallery.completed_questions_count/gallery.questions_count)*100;
          return (is_integer) ? parseInt(completion_rate_val) : ($filter('number')(completion_rate_val.toString(),1)+'').replace('٫','.');
        },
        /*
        * Init the gallery object
        */
        init: function(){
          $scope.gallery.getList();
        }
      };
      $scope.gallery.init();
    }
  };
});
