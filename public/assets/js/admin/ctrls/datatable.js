/*
all.js requires all angularjs controllers
DatatableCtrl
*/
App.controller('DatatableCtrl', function($http,$httpParamSerializer,$filter,$rootScope,$compile, DTDefaultOptions,DTOptionsBuilder, DTColumnBuilder,$scope,$location, Flash, $uibModal, $routeParams, API, Helpers,$filter,$timeout,$route,surveyFactory,userFactory) {
	/* 1: Prepare main variables */
	var ordering_column = 0;
	var ordering_type = 'DESC';
	$scope.parent_path = $location.path().split("/")[2];
	$scope.sub_path = $location.path().split("/")[3];
	$scope.parent_path_id = $location.path().split("/")[4];
	$scope.sub_path_id = $location.path().split("/")[5];
	$scope.dtInstance = {};
	$scope.isDatatableLoading = true;

	$scope.filter_data = {
		start_date: '',
		end_date: ''
	};

	$scope.filter_dates = [{
		key: 'all',
		value: 'جميع الأوقات'
	}, {
		key: 'today',
		value: 'اليوم'
	}, {
		key: 'yesterday',
		value: 'أمس'
	}];



	$timeout(function(){
		if ($scope.dtInstance.DataTable && $scope.dtInstance.DataTable.page.info().recordsTotal >= 0) {
			$scope.isDatatableLoading = false;
		}
	},2000);
	DTDefaultOptions.setDOM('<"table-responsive" t>p');
	$scope.rowClickHandler = function(d){};


	/* 2: Main Helpers */


	/* 2-1: Import data modal */
	$scope.Import = function(type,id){
		var import_title = 'استيراد بيانات',
				sample_link = '';
		switch (type) {
			case 'catering':
			import_title += ' ملف الإعاشة الخاص بالموظفين';
			import_sample_link = 'catering.xlsx';
			break;
		}

		$uibModal.open({
			backdrop: 'static',
			templateUrl: Helpers.getTemp('modals/import-modal'),
			scope: $scope,
			controller: function($uibModalInstance,$location,$scope,$http,Flash,$route,$window){
				$scope.import_modal = {
					type: type,
					title: import_title,
					sample_link: import_sample_link,
					cancel: function() {
						$uibModalInstance.close();
					},
					file: {name: ''},
					importing: false,
					startImport: function() {
						var sendJson = {type: type,file: $scope.import_modal.file.name};
						if (id) {
							sendJson.id = id;
						}
						// $scope.import_modal.importing = true;
						API.POST('flow-uploader/start-import',sendJson).then(function(d){
							if (d.data.is_file) {
								$scope.import_modal.cancel();
								$window.open(baseUrl+'/'+d.data.file,'_blank');
							}else {
								$scope.import_modal.importing = false;
								Flash.create('success','تمت العملية بنجاح');
								$scope.import_modal.cancel();
								$route.reload();
							}
						});
					}
				};

			}

		});


	};

	/* 2-3: Ready Dropzone Options */
	$scope.prepareDzOptions = function(no_img,path){
		var r = {
			url: baseUrl+'/api/upload',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			maxFiles: 1,
			paramName : 'file',
			acceptedFiles : 'image/jpeg, images/jpg, image/png',
			dictDefaultMessage : '<div class="img-icon d-flex justify-content-center align-items-center"><img src="'+baseUrl+'/assets/images/svgs/'+no_img+'.svg" alt="" /></div><b class="img-upload">أرفع صورة</b>',
			init: function() {
				if (path) {
					var thisDropzone = this;
					var mockFile = {};
					thisDropzone.emit("addedfile", mockFile);
					thisDropzone.emit("success", mockFile);
					thisDropzone.emit("thumbnail", mockFile, baseUrl+'/uploads/images/'+path);
					thisDropzone.emit("complete",mockFile);

				}
			}
		};

		return r;
	};



	$scope.prepareDzCallbacks = function(parent,model,extra_data){
		return {
			'success' : function(file,xhr){
				if (parent) {
					$scope[parent][model] = xhr.path;
				}else {
					$scope[model] = xhr.path;
				}
			},
			'sending' : function(file, xhr, formData){
				if (extra_data) {
					angular.forEach(extra_data,function(v,k){
						formData.append(k,v);
					});
				}
			}

		};
	};






	/* 3: Prepare datatable columns */
	switch($scope.parent_path){
		case 'surveys':

		$scope.survey = {
			add: function(){
				return surveyFactory.modalInfo('add',null,{view: 'datatable',dtInstance: $scope.dtInstance});
			},
			delete: function(id){
				return surveyFactory.delete(id,{view: 'datatable',dtInstance: $scope.dtInstance});
			},
			activation: function(id){
				var surveyActivationStatus = ($scope.surveys[id].is_active == '1') ? 0 : 1;
				$scope.surveys[id].is_active = surveyActivationStatus+'';
				return surveyFactory.activation(id,surveyActivationStatus);
			},
			edit: function(id){
				return surveyFactory.editModal(id,{view: 'datatable',dtInstance: $scope.dtInstance});
			}
		};

		$scope.rowClickHandler = function(f){
			$scope.survey.edit(f.id);
		};

		ordering_column = 2;

		$scope.surveys = {};
		/**
		* Prints datatable columns
		*/
		var columns_list = [
			DTColumnBuilder.newColumn('title').withTitle('العنوان').renderWith(function(d,t,f){
				return '<div class="widget-table-item-title">'+d+'</div>';
			}),
			DTColumnBuilder.newColumn('user_role_name').withTitle('نوع المستخدمين').renderWith(function(d,t,f){
				return d;
			}),
			DTColumnBuilder.newColumn('created_at').withTitle('تاريخ الإضافة').renderWith(function(d,t,f){
				return $filter('dateF')(d,'yyyy/MM/dd HH:mm');
			}).withOption('searchable', false),
			DTColumnBuilder.newColumn('actions').withClass('text-left').renderWith(function(d,t,f){
					$scope.surveys[f.id] = {is_active: f.is_active};
					var editBtn = '<a class="btn btn-light mr-1 btn-icon btn-sm" ng-click="survey.edit('+f.id+')"><i class="ic-edit"></i></a>',
					activationBtn = '<a class="btn btn-light mr-1 btn-icon btn-sm" tooltip-popup-delay="300" uib-tooltip="{{ (surveys['+f.id+'].is_active == \'0\') ? \'تفعيل الأستبانة للإجابة عليها\' : \'إلغاء تفعيل الأستبانة\' }}" ng-click="survey.activation('+f.id+')"><i ng-class="(surveys['+f.id+'].is_active == \'0\') ? \'ic-eye-hide\' : \'ic-eye\'"></i></a>',
					deleteBtn = '<a class="btn btn-light mr-1 btn-icon btn-sm" ng-click="survey.delete('+f.id+')"><i class="ic-delete"></i></a>';
					return activationBtn+editBtn+deleteBtn;
			}).withOption('searchable', false).notSortable()
		];
		$scope.Columns = columns_list;


			break;
		case 'users':
		ordering_column = 4;
		$scope.user = {
			add: function(){
				return userFactory.modal('add',null,{view: 'datatable',dtInstance: $scope.dtInstance});
			},
			delete: function(id){
				return userFactory.delete(id,{view: 'datatable',dtInstance: $scope.dtInstance});
			},
			edit: function(id){
				return userFactory.modal('edit',id,{view: 'datatable',dtInstance: $scope.dtInstance});
			}
		};

		$scope.rowClickHandler = function(f){
			$scope.user.edit(f.id);
		};

		ordering_column = 2;

		/**
		* Prints datatable columns
		*/
		var columns_list = [
			DTColumnBuilder.newColumn('username').withTitle('اسم المستخدم').renderWith(function(d,t,f){
				return '<div class="widget-table-item-title">'+d+'</div>';
			}),
			DTColumnBuilder.newColumn('email').withTitle('البريد الألكتروني').renderWith(function(d,t,f){
				return d;
			}),
			DTColumnBuilder.newColumn('user_role_name').withTitle('نوع المستخدم').renderWith(function(d,t,f){
				return d;
			}),
			DTColumnBuilder.newColumn('created_at').withTitle('تاريخ الإضافة').renderWith(function(d,t,f){
				return $filter('dateF')(d,'yyyy/MM/dd HH:mm');
			}).withOption('searchable', false),
			DTColumnBuilder.newColumn('actions').withClass('text-left').renderWith(function(d,t,f){
					var editBtn = '<a class="btn btn-light mr-1 btn-icon btn-sm" ng-click="user.edit('+f.id+')"><i class="ic-edit"></i></a>',
					deleteBtn = '<a class="btn btn-light mr-1 btn-icon btn-sm" ng-click="user.delete('+f.id+')"><i class="ic-delete"></i></a>';
					return editBtn+deleteBtn;
			}).withOption('searchable', false).notSortable()
		];
		$scope.Columns = columns_list;


			break;



	}

	/* Send search value to server */
	$scope.$watch('datatable_search', function(newValue) {
		if (!angular.isUndefined(newValue) && $scope.dtInstance.DataTable) {
			delay(function(){
				$scope.dtInstance.DataTable.search(newValue);
				$scope.dtInstance.DataTable.search(newValue).draw();
			},500);
		}
	});


	if($scope.Columns){
		//$scope.Columns.unshift(DTColumnBuilder.newColumn('id').withOption('searchable', false).notVisible());
		// تجهيز خيارات مكتبة datatable js
		$scope.setTableOptions = function(){
			return DTOptionsBuilder.newOptions()
			.withOption('ajax', {
				url: baseUrl+'/api/web/admin/dt/'+$scope.parent_path+(($scope.sub_path) ? '/'+$scope.sub_path : '')+(($scope.parent_path_id) ? '/'+$scope.parent_path_id : '')+(($scope.sub_path_id) ? '/'+$scope.sub_path_id : ''),
				type: 'POST',
				headers: {'X-CSRF-TOKEN': $('[name="csrf-token"]').attr('content')},
				dataType: 'JSON',
				data: function(d) {
					// send other filters
					angular.forEach($scope.filter_data, function(v, k) {
						d[k] = v;
					});
				}
			})
			.withDataProp('data')
			.withOption('processing', true)
			.withOption('serverSide', true)
			.withOption('order', [ordering_column, ordering_type])
			.withOption('createdRow', function(row, data, dataIndex) {
				$compile(angular.element(row).contents())($scope);

			})
			.withDisplayLength(20).withPaginationType('full_numbers').withOption('rowCallback', function(row,data) {
				$('td', row).unbind('click');
				$('td', row).bind('click', function($event) {
					var selection = window.getSelection();
					if(selection.toString().length === 0 && ['INPUT','LABEL','A','BUTTON','I'].indexOf($($event.target).get(0).tagName) < 0) {
						$scope.$apply(function() {
							$scope.rowClickHandler(data);
						});
					}
				});
				return row;
			}).withOption('headerCallback', function(header) {
				$compile(angular.element(header).contents())($scope);
				$scope.isDatatableLoading = false;

			}).withOption('drawCallback', function(settings) {
				if (settings.json.additional_data) {
					$scope.additional_data = settings.json.additional_data;
				}
				var tbr = '.table-responsive',dtp = '.dataTables_paginate',nop = 'no-pagination';
				if (settings._iRecordsTotal < 20) {
					$(dtp).hide();
					$(tbr).addClass(nop);
				}else {
					$(dtp).show().css('display', 'flex');
					$(tbr).removeClass(nop);
				}
				$scope.isDatatableLoading = false;
			}).withOption('initComplete', function(t,data,f) {
				$scope.isDatatableLoading = false;
			});
		};
	}

	$scope.headerFilters = function() {
		/* Reinit results */
		$scope.reInitResults = function() {
			$scope.dtInstance.reloadData();
		};

		switch ($scope.parent_path) {
			case 'surveys':
			$scope.filter_data.status = 'all';
			break;

		}

		/* Prepare filters date */
		$scope.prepareFilterDate = function(val) {
			// prepare range date
			if (val != 'custom') {
				// prepare dates like thisweek,thismonth
				var filter = $filter('prepareFilterDate')(val);
				$scope.filter_data['start_date'] = filter.start_date;
				$scope.filter_data['end_date'] = filter.end_date;
			}

		};

		/* Filter url params */
		if ($routeParams.id) {
			$scope.filter_data['id'] = $routeParams.id;
		}
		if ($routeParams.perm) {
			$scope.filter_data['perm'] = $routeParams.perm;
		}
		if ($routeParams.search) {
			$scope.datatable_search = $routeParams.search;
		}

		if ($routeParams.sdate || $routeParams.edate || $routeParams.date) {
			if ($routeParams.sdate) {
				$scope.c_start_date = new Date($routeParams.sdate);
				$scope.filter_data['start_date'] = $routeParams.sdate;
			}
			if ($routeParams.edate) {
				$scope.c_end_date = new Date($routeParams.edate);
				$scope.filter_data['end_date'] = $routeParams.edate;
			}
			if ($routeParams.date) {
				$scope.filter_data['date'] = $routeParams.date;
				$scope.prepareFilterDate($routeParams.date);
			} else {
				$scope.prepareFilterDate('custom');
			}
		} else {
			// default date
			if ($scope.filter_dates) {
				var default_date = 'all';
				if ($scope.parent_path == 'attendance') {
					default_date = 'today';
				}
				$scope.filter_data['date'] = default_date;
				$scope.prepareFilterDate(default_date);
			}
		}
		if (angular.isFunction($scope.setTableOptions)) {
			$scope.Options = $scope.setTableOptions();
		}

	}

	$scope.filterResults = function(key, val) {
		if (key == 'status') {
			$route.reload();
		}


		$scope.datatableFirstLoading = true;
		$scope.showSearch = false;
		$scope.start_date = '';
		$scope.end_date = '';
		switch (key) {
			case 'status':
			$scope.filter_data['status'] = val;
			$location.search('status', val);
			break;
			case 'date':

			$scope.open_date_filters = false;
			$scope.filter_data['date'] = val;
			$scope.is_custom_date = (val == 'custom');
			if ($scope.is_custom_date) {
				var sdate = $filter('dateF')($scope.c_start_date, 'yyyy-MM-dd'),
				edate = $filter('dateF')($scope.c_end_date, 'yyyy-MM-dd');
				$location.search('date', null);
				$location.search('sdate', sdate);
				$location.search('edate', edate);
				$scope.filter_data['date'] = '';
				$scope.filter_data['start_date'] = sdate;
				$scope.filter_data['end_date'] = edate;
			} else {
				$scope.c_start_date = '';
				$scope.c_end_date = '';
				$location.search('date', val);
				$location.search('sdate', null);
				$location.search('edate', null);
			}
			$scope.prepareFilterDate(val);
			break;
		}
	}

	$scope.headerFilters();

});
