/*
all.js requires all angularjs controllers
DatatableCtrl
*/
App.controller('DatatableCtrl', function($http,$httpParamSerializer,$filter,$rootScope,$compile, DTDefaultOptions,DTOptionsBuilder, DTColumnBuilder,$scope,$location, Flash, $uibModal, $routeParams, API, Helpers,$filter,$timeout,$route) {
	/* 1: Prepare main variables */
	var ordering_column = 0;
	var ordering_type = 'DESC';
	$scope.parent_path = $location.path().split("/")[1];
	$scope.sub_path = $location.path().split("/")[2];
	$scope.parent_path_id = $location.path().split("/")[3];
	$scope.sub_path_id = $location.path().split("/")[4];
	$scope.dtInstance = {};
	$scope.isFirstLoading = true;

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
			$scope.isFirstLoading = false;
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
			url: baseUrl+'/api/web/upload',
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

	/* 2-2: Resident */
	$scope.resident = {
		/* a new request such as delete, instead lost card etc. */
		setRequest: function(module_type,module_id,type){
			if (confirm('هل انت متأكد من اجراء العملية هذه؟')) {
				API.POST('card/request/'+type+'/'+module_type.replace('_','-')+'/'+module_id).then(function(d){
					switch (d.data.message) {
						case 'this_request_already_exist':
							Flash.create('danger','لا يزال هناك طلب في إنتظار المراجعة');
							break;
						case 'card_not_printed_yet':
							Flash.create('danger','البطاقة لم تطبع بعد حتى تتمكن من أعطاء طلب بدل فاقد');
							break;
						default:
						if (type == 'instead-lost') {
							Flash.create('success','تم تصدير بطاقة جديدة وتحويلها الى قسم المراجعة');
						}else {
							Flash.create('success','تم تقديم طلب إلغاء البطاقة');
						}
							break;
					}
					$scope.dtInstance.reloadData();
				});
			}
		},
		modal: function(id,is_edit,is_card_id){
			$uibModal.open({
				backdrop: 'static',
				templateUrl: Helpers.getTemp('modals/resident-modal'),
				size: 'xl',
				scope: $scope,
				controller: function($uibModalInstance,$location,$scope,$http,$timeout,Flash,Helpers,$route,$window){
					$scope.resident_modal = {
						family_sa_id_already_exists: [],
						is_card: (is_card_id) ? true : false,
						card: {
							data: {},
							action_data: {},
							sendAction: function(type){
								API.PUT('card/'+type.replace('_','-')+((type == 'printed') ? '/'+$scope.resident_modal.card.data.type : '')+'/'+is_card_id,$scope.resident_modal.card.action_data).then(function(d){
									if (type == 'instead-lost') {
										Flash.create('success','تم تصدير بطاقة جديدة وتحويلها الى قسم المراجعة');
										$scope.resident_modal.card.data.status = $filter('filter')(Helpers.cardStatusList,{key: 'canceled'})[0].value;
									}else {
										if (type == 'community_represented') {
											$scope.resident_modal.card.data.status = 6;
										}else {
											$scope.resident_modal.card.data.status = $filter('filter')(Helpers.cardStatusList,{key: type})[0].value;
										}
										Flash.create('success','تمت العملية بنجاح');
									}
									$scope.dtInstance.reloadData();
									if (!window.auth.is_supervisor) {
										$scope.resident_modal.cancel();
									}
								});
							},
							setAction: function(type){
								if (type == 'rejected') {
									$uibModal.open({
										backdrop: 'static',
										templateUrl: Helpers.getTemp('modals/reject-card-reason-modal'),
										size: 'sm',
										scope: $scope,
										controller: function($uibModalInstance,$location,$scope,$http,$timeout,Flash,Helpers,$route,$window){
											$scope.reject_card_reason_modal = {
												data: {},
												cancel: function() {
													$uibModalInstance.close();
												},
												save: function(validity) {
													if (!Helpers.isValid(validity)) {
														Flash.create('danger','يرجى منك التحقق من المدخلات المطلوبة');
														return false;
													}
													$scope.resident_modal.card.sendAction(type);
													$scope.reject_card_reason_modal.cancel();
												}
											};
										}
									});

								}else {
									if (confirm('هل انت متأكد من اجراء العملية هذه؟')) {
										$scope.resident_modal.card.sendAction(type);
									}
								}
							},
							initCard: function(){
								$scope.resident_modal.card.isLoading = true;
								API.GET('card/show-summary/'+is_card_id).then(function(d){
									$scope.resident_modal.card.isLoading = false;
									$scope.resident_modal.card.data = d.data;
								});
							}
						},
						data: {building_realestate_no: 1,building_family_no: 1,expire_period: '6months',family_members: []},
						is_edit: (!id) ? true : false,
						type: (!id) ? 'add' : 'edit',
						prepareBirthday: function(parent,item){
							if (item) {
								var hijri_data = item.split('/');
								parent.birth_day_hijri = parseInt(hijri_data[2])+'';
								parent.birth_month_hijri = parseInt(hijri_data[1])+'';
								parent.birth_year_hijri = hijri_data[0];
							}
						},
						setEdit: function() {
							$scope.resident_modal.is_edit = true;
							$scope.resident_modal.data.expire_period = $scope.resident_modal.data.card.expire_period;
							$scope.resident_modal.getTownLists(true);
							$scope.resident_modal.prepareBirthday($scope.resident_modal.data,$scope.resident_modal.data.birthday_hijri);
							angular.forEach($scope.resident_modal.data.family_members,function(member,member_k){
								if (member.card){
									member.expire_period = member.card.expire_period;
								}
								$scope.resident_modal.family_member.setDzOptions(member);
							});
						},
						cancel: function() {
							$uibModalInstance.close();
						},
						isFormValid: function(){
							var r = true;
							/* Start validate images */
							angular.forEach($scope.resident_images_list,function(resident_image_data,key){
								// if (!$scope.resident_modal.data[resident_image_data.key] && (resident_image_data.prefix != 'FamilyCard' || $scope.resident_modal.data.family_members.length)) {
								if (!$scope.resident_modal.data[resident_image_data.key] && resident_image_data.prefix != 'FamilyCard') {
									r = false;
									return true;
								}
							});
							angular.forEach($scope.resident_modal.data.family_members,function(member,member_k){
								if (!member.image_of_birth_id || (!member.image_of_personal && member.is_has_card)) {
									r = false;
									return true;
								}
							});

							/* End validate images */

							return r;
						},
						save: function(validity) {
							$scope.resident_modal.is_send_clicked = true;
							$scope.resident_modal.family_sa_id_already_exists = [];
							if (!Helpers.isValid(validity)) {
								Flash.create('danger','يرجى منك التحقق من المدخلات المطلوبة');
								return false;
							}else if(!$scope.resident_modal.isFormValid()){
								Flash.create('danger','يرجى ملئ جميع المدخلات');
								return false;
							}
							var Call = (id) ? API.PUT('resident/update/'+id,$scope.resident_modal.data) : API.POST('resident/add',$scope.resident_modal.data);
							Call.then(function(d){
								if (d.data.message == 'success') {
									Flash.create('success','تم حفظ البيانات بنجاح');
									$scope.dtInstance.reloadData();
									$scope.resident_modal.cancel();
								}else if(d.data.message == 'sa_id_already_exists') {
									$scope.resident_modal.sa_id_already_exists = true;
									Flash.create('danger','رقم الهوية موجود مسبقاً');
								}else if(d.data.message == 'family_sa_id_duplicated') {
									Flash.create('danger','لديك ارقام هوية متكررة في قائمة التابعين');
								}else if(d.data.message == 'family_sa_id_already_exists') {
									$scope.resident_modal.family_sa_id_already_exists = d.data.family_sa_id_already_exists;
									Flash.create('danger','ارقام هوية موجودة مسبقاً في قائمة التابعين');
								}else {
									Flash.create('danger','حدث خطأ في ادخال البيانات');
								}
							});
						},
						show: function() {
							$scope.resident_modal.isLoading = true;
							API.GET('resident/show/'+id).then(function(d){
								$scope.resident_modal.isLoading = false;
								if (d.data && d.data.id) {
									$scope.resident_modal.data = d.data;
								}
								$scope.resident_modal.initResidentDz();
								if (is_edit) {
									$scope.resident_modal.setEdit();
								}
								if (is_card_id) {
									$scope.resident_modal.card.initCard();
								}
								angular.forEach($scope.resident_modal.data.family_members,function(member,member_k){
									if (member.card) {
										$scope.resident_modal.data.family_members[member_k].is_has_card = true;
										$scope.resident_modal.prepareBirthday($scope.resident_modal.data.family_members[member_k],$scope.resident_modal.data.family_members[member_k].birthday_hijri);
									}
								});
							});
						},
						getTownLists: function(is_init){
							if (!is_init) {
								$scope.resident_modal.data.village_id = null;
								$scope.resident_modal.data.building_id = null;
							}
							if ($scope.resident_modal.data.town_id) {
								$scope.resident_modal.isTownListsLoading = true;
								API.GET('helpers/list/town-lists',{town_id: $scope.resident_modal.data.town_id},true).then(function(d){
									$scope.resident_modal.isTownListsLoading = false;
									$scope.resident_modal.villages_list = d.data.villages;
									$scope.resident_modal.buildings_list = d.data.buildings;
								});
							}
						},
						cleanInputs: {
			    		family_no: function(){
								if ($scope.resident_modal.data['building_family_no'] > 8) {
									$scope.resident_modal.data['building_family_no'] = 8;
								}else if($scope.resident_modal.data['building_family_no'] < 0){
									$scope.resident_modal.data['building_family_no'] = 1;
								}else if(angular.isUndefined($scope.resident_modal.data['building_family_no'])){
									$scope.resident_modal.data['building_family_no'] = 1;
								}
			    		},
			    		sa_id: function(type,type_index){
								var item = (type == 'resident') ? $scope.resident_modal.data : $scope.resident_modal.data.family_members[type_index];
			    			item['sa_id'] = parseInt(item['sa_id']);
			    			if (['1','2','3','4'].indexOf(item['sa_id'].toString().charAt(0)) == -1) {
			    				item['sa_id'] = parseInt(item['sa_id'].toString().substr(1));
			    			}
			    		},
			    		phone: function(i){
								if ($scope.resident_modal.data[i]) {
									$scope.resident_modal.data[i] = $scope.resident_modal.data[i].replace(/\s/g, '');
									if ($scope.resident_modal.data[i].charAt(0) == '5') {
										$scope.resident_modal.data[i] = '05';
									}
									else if ($scope.resident_modal.data[i].charAt(1) && $scope.resident_modal.data[i].charAt(1) != '5') {
										$scope.resident_modal.data[i] = $scope.resident_modal.data[i].toString().substr(2);
									}
									else if ($scope.resident_modal.data[i].toString().charAt(0) != '0') {
										$scope.resident_modal.data[i] = $scope.resident_modal.data[i].toString().substr(1);
									}
								}
			    		}
			    	},
						initResidentDz: function(){
							$scope.resident_images_list = [{label: 'الصورة الشخصية',key: 'image_of_personal',prefix: 'Per',no_image: 'no-avatar'},{label: 'صورة الهوية',key: 'image_of_sa_id',prefix: 'SaId',no_image: 'no-id'},{label: 'صورة مشهد ساكن',key: 'image_of_lease',prefix: 'Lease',no_image: 'no-document'},{label: 'بيانات العقار (الحصر الاولي)',key: 'image_of_realestate_info',prefix: 'RealestateInfo',no_image: 'no-document'},{label: 'صورة وثيقة بيانات ساكن',key: 'image_of_resident_info',prefix: 'ResidentInfo',no_image: 'no-document'},{label: 'صورة من السند المالي',key: 'image_of_financial',prefix: 'Financial',no_image: 'no-document'},{label: 'صورة من بطاقة العائلة',key: 'image_of_family_card',prefix: 'FamilyCard',no_image: 'no-document'}];
							angular.forEach($scope.resident_images_list,function(resident_image_data,key){
								$scope.resident_modal['dz'+resident_image_data.prefix+'Methods'] = {};
								$scope.resident_modal['dz'+resident_image_data.prefix+'Callbacks'] = {
									'success' : function(file,xhr){
										$scope.resident_modal.data[resident_image_data.key] = xhr.path;
									}
								};
								$scope.resident_modal['dz'+resident_image_data.prefix+'Options'] = $scope.prepareDzOptions(resident_image_data.no_image,$scope.resident_modal.data[resident_image_data.key]);
							});
						},
						removeDzImage: function(resident_image){
							$scope.resident_modal.data[resident_image.key] = '';
							$scope.resident_modal.initResidentDz();
						},
						init: function(){
							if (id) {
								$scope.resident_modal.show();
							}else {
								$scope.resident_modal.initResidentDz();
							}
							/* Perpare Hijri Date */
							$scope.resident_modal.hijri = {
								days: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30],
								years: [],
								months: []
							};
							var hijriMonths = ['المحرّم', 'صفر', 'ربيع الأول', 'ربيع الثاني', 'جمادى الاول', 'جمادى الآخر', 'رجب', 'شعبان', 'رمضان', 'شوّال', 'ذو القعدة', 'ذو الحجة'];
							var curHijri = window.curHijri;
							for (var year = curHijri-120; year <= curHijri; year++) {
								$scope.resident_modal.hijri.years.push(year);
							}
							angular.forEach(hijriMonths,function(month_v,month_k){
								$scope.resident_modal.hijri.months.push({key: (month_k+1),label: month_v});
							});
							/* End Perpare Hijri Date */

						},
						family_member: {
							setCard: function(item){
								if (item.is_has_card_by_age && item.is_has_card) {
									Flash.create('danger','تصدير بطاقة منفصلة إجباري لإن التابع أكبر من 18 سنة');
								}else {
									if (item.id && item.is_has_card && !item.is_card_not_saved) {
										Flash.create('danger','عذراً لا يمكنك إلغاء تصدير البطاقة لإن تم اعطاء أمر التصدير');
									}else {
										item.is_has_card_by_age = false;
										item.expire_period = '6months';
										item.is_has_card = !item.is_has_card;
										item.is_card_not_saved = true;
										$timeout(function(){
											$('#family_members_table tbody:last-child .sa-id-input').focus();
										},1);
									}
								}
							},
							setDzOptions: function(item){
								if (!item.dzPerOptions) {
									item.dzPerOptions = $scope.prepareDzOptions('no-avatar',item.image_of_personal);
									item.dzBirthIdOptions = $scope.prepareDzOptions('no-document',item.image_of_birth_id);
									item.dzPerCallbacks = {
										'success' : function(file,xhr){
											item.image_of_personal = xhr.path;
										}
									};
									item.dzBirthIdCallbacks = {
										'success' : function(file,xhr){
											item.image_of_birth_id = xhr.path;
										}
									};
									item.dzPerMethods = {};
									item.dzBirthIdMethods = {};
								}
							},
							add: function(){
								$scope.resident_modal.data.family_members.push({expire_period: '6months'});
								$timeout(function(){
									$('#family_members_table tbody:last-child .name-input').focus();
									$scope.resident_modal.family_member.setDzOptions($scope.resident_modal.data.family_members[$scope.resident_modal.data.family_members.length-1]);
								},1);

							},
							checkAge: function(item){
								var year = parseInt(item.birth_year_hijri);
								if ((window.curHijri-year) >= 18) {
									item.is_has_card_by_age = true;
									item.is_has_card = true;
								}else {
									if (item.is_has_card_by_age) {
										item.is_has_card = false;
									}
								}
							},
							delete: function(item,index){
								if (item.id) {
									if (Helpers.confirmDelete()) {
										item.is_deleting = true;
										API.DELETE('resident/family-member/delete/'+item.id).then(function(){
											item.is_deleting = false;
											$scope.resident_modal.data.family_members.splice(index,1);
										});
									}
								}else {
									$scope.resident_modal.data.family_members.splice(index,1);
								}
							}
						}

					};
					$scope.resident_modal.init();

				}

			});


		},
		delete: function(id,method){
			if (Helpers.confirmDelete()) {
				API.DELETE('resident/delete/'+id).then(function(){
					if (method == 'datatable') {
						$scope.dtInstance.reloadData();
					}
				});
			}
		}
	};
	/* 2-3: Card */
	$scope.card = {
		getHistory: function(id){
			$uibModal.open({
				backdrop: 'static',
				templateUrl: Helpers.getTemp('modals/card-history-modal'),
				scope: $scope,
				controller: function($uibModalInstance,$location,$scope,$http,$timeout,Flash,Helpers,$route,$window){
					$scope.card.history_modal = {
						data: {},
						cancel: function() {
							$uibModalInstance.close();
						},
						init: function() {
							$scope.card.history_modal.isLoading = true;
							API.GET('card/show-summary/'+id,{with_history: true}).then(function(d){
								$scope.card.history_modal.isLoading = false;
								$scope.card.history_modal.data = d.data;
							});
						}
					};
					$scope.card.history_modal.init();
				}

			});


		},
		printAll: function(){
			$uibModal.open({
				backdrop: 'static',
				templateUrl: Helpers.getTemp('modals/print-all-card-modal'),
				size: 'sm',
				scope: $scope,
				controller: function($uibModalInstance,$location,$scope,$http,$timeout,Flash,Helpers,$route,$window){
					$scope.card.print_modal = {
						data: {},
						cancel: function() {
							$uibModalInstance.close();
						},
						send: function() {
							$scope.card.print_modal.isLoading = true;
							API.POST('card/print-all').then(function(d){
								$scope.card.print_modal.data = d.data;
								if (d.data.path) {
									$scope.card.print_modal.isLoading = false;
									$window.open($filter('global_asset')('cards/'+$scope.card.print_modal.data.path,'uploads'), '_blank');
									$window.open($filter('global_asset')('commitments/'+$scope.card.print_modal.data.commitment_path,'uploads'), '_blank');
									$scope.dtInstance.reloadData();
								}else if(d.data.message == 'no_cards'){
									Flash.create('danger','لا توجد بطائق بإنتظار الطباعة ليتم تصديرها');
									$scope.card.print_modal.cancel();
								}else {
									Flash.create('danger','حدث خطأ في التصدير يرجى المحاولة مرة أخرى');
									$scope.card.print_modal.cancel();
								}
							});
						},
						init: function(){
							$scope.card.print_modal.send();
						}
					};
					$scope.card.print_modal.init();

				}

			});


		},
		delete: function(id,method){
			if (Helpers.confirmDelete()) {
				API.DELETE('resident/delete/'+id).then(function(){
					if (method == 'datatable') {
						$scope.dtInstance.reloadData();
					}
				});
			}
		}
	};




	/* 5: Prepare datatable columns */
	switch($scope.parent_path){
		case 'prints':
		$scope.rowClickHandler = function(f){

		};

		ordering_column = 0;

		/**
		* Prints datatable columns
		*/
		var columns_list = [
			DTColumnBuilder.newColumn('created_at').withClass('ltr text-right').withTitle('تاريخ الطباعة').renderWith(function(d,t,f){
				return $filter('dateF')(d,'yyyy/MM/dd HH:mm');
			}).withOption('searchable', false),
			DTColumnBuilder.newColumn('count').withTitle('عدد البطائق').renderWith(function(d,t,f){
				return d;
			}).withOption('searchable', false),
			DTColumnBuilder.newColumn('created_by_name').withTitle('بواسطة').renderWith(function(d,t,f){
				return d;
			}),

			DTColumnBuilder.newColumn('actions').withClass('text-left').renderWith(function(d,t,f){
					var downloadCardsBtn = '<a class="btn btn-default mr-1 iconed btn-sm" href="'+$filter('global_asset')('cards/'+f.path,'uploads')+'" target="_blank"><i class="ic-export"></i>البطائق</a>',
							downloadCommitmentsBtn = '<a class="btn btn-default mr-1 iconed btn-sm" href="'+$filter('global_asset')('commitments/'+f.commitment_path,'uploads')+'" target="_blank"><i class="ic-export"></i>التعهدات</a>';
					return downloadCardsBtn+downloadCommitmentsBtn;
				}).withOption('searchable', false).notSortable()
		];
		$scope.Columns = columns_list;


			break;
	break;
		case 'cards':
		$scope.rowClickHandler = function(f){
			$scope.resident.modal(f.details_parent_resident_id,false,parseInt(f.id));
		};

		if (window.auth.is_supervisor) {
			$scope.status_list = [
				{label: 'الكل',value: 'all'},
				{label: 'بإنتظار ممثل الجالية',value: 0},
				{label: 'بإنتظار المراجعة',value: 6},
				{label: 'الطلب مرفوض',value: 1},
				{label: 'بإنتظار اعتماد المراجعة',value: 2},
				{label: 'بإنتظار الطباعة',value: 3},
				{label: 'تمت الطباعة',value: 4},
				{label: 'ملغاة',value: 5}
			];
		}
		ordering_column = 7;

		/**
		* Cards datatable columns
		*/
		$scope.cards = [];
		var columns_list = [
			DTColumnBuilder.newColumn('requested_type').withTitle('نوع الطلب').renderWith(function(d,t,f){
				return '<span class="label label-'+$filter('card_request_type')(d)['class']+'">'+$filter('card_request_type')(d)['label']+'</span>';
			}),
			DTColumnBuilder.newColumn('no').withTitle('رقم البطاقة - النسخة').renderWith(function(d,t,f){
				return '<span class="d-flex">'+d+'<span class="mx-1">-</span>'+f.version+'</span>';
			}),
			DTColumnBuilder.newColumn('details_sa_id').withTitle('رقم الهوية').renderWith(function(d,t,f){
				return d;
			}),
			DTColumnBuilder.newColumn('details_name').withTitle('الاسم').renderWith(function(d,t,f){
				return d;
			})
		];

		columns_list.push(DTColumnBuilder.newColumn('status').withTitle('حالة الطلب').renderWith(function(d,t,f){
			return '<span class="label label-'+$filter('card_status')(d)['class']+'">'+$filter('card_status')(d)['label']+'</span>';
		}));

		columns_list.push(DTColumnBuilder.newColumn('module').withTitle('نوع البطاقة').renderWith(function(d,t,f){
			return (d == 'resident') ? 'رب أسرة' : 'تابع';
		}).withOption('searchable',false));
		columns_list.push(DTColumnBuilder.newColumn('details_town').withTitle('الحي').renderWith(function(d,t,f){
			return d;
		}).withOption('searchable',false));
		columns_list.push(DTColumnBuilder.newColumn('requested_at').withTitle('تاريخ الطلب').renderWith(function(d){
			return $filter('dateF')(d);
		}).withOption('searchable', false));
		$scope.Columns = columns_list;


			break;
	break;
	case 'users':
	$scope.filter_perms = [
		{label: 'كل الصلاحيات',value: 'all'},
		{label: 'الطلبات',value: 'requester'},
		{label: 'ممثل الجالية',value: 'community_representative'},
		{label: 'المراجعة',value: 'reviewer'},
		{label: 'اعتماد المراجعة',value: 'approval'},
		{label: 'الطباعة',value: 'printer'},
		{label: 'التحكم بالمستخدمين',value: 'manage_users'}
	];

	if (window.auth.is_supervisor) {
		$scope.filter_perms.push({label: 'الادارة',value: 'supervisor'});
	}

	/**
	* User Modal
	* @param integer row_id
	*/
	$scope.userModal = function(row_id) {
		$uibModal.open({
			size: 'md',
			backdrop: 'static',
			templateUrl: Helpers.getTemp('modals/user-modal'),
			scope: $scope,
			controller: function($uibModalInstance, $rootScope, $location,$interval, $scope, $http,$filter,Helpers) {
				$scope.user_modal = {
					admin_user_perms: [],
					data: {
						admin_group: 'requester'
					},
					method: (row_id) ? 'edit' : 'add',
					save: function(validity) {
						$scope.user_modal.is_send_clicked = true;
						if (!Helpers.isValid(validity)) {
							return false;
						}
						$scope.user_modal.isLoading = true;
						$scope.user_modal.email_already_exists = false;


						var Call = (row_id) ? API.PUT('user/update/'+row_id,$scope.user_modal.data) : API.POST('user/add',$scope.user_modal.data);
						 Call.then(function(d){
							$scope.user_modal.isLoading = false;
							if (d.data.message != 'invalid_fields') {
								$scope.user_modal.cancel(true);
								Flash.create('success','تم حفظ بيانات المستخدم بنجاح');
								$scope.dtInstance.reloadData();
							}else if(d.data.message != 'email_already_exists'){
								$scope.user_modal.email_already_exists = true;
							}
						});
					},
					show: function() {
						$scope.user_modal.isLoading = true;
						API.GET('user/show/'+row_id).then(function(d){
							$scope.user_modal.data = d.data;
							$scope.user_modal.isLoading = false;
						});
					},
					cancel: function(without_confirm) {
						$uibModalInstance.close();
					},
					init: function(){
						if(!window.auth.is_supervisor){
							angular.forEach($scope.filter_perms,function(val,key){
								if(val.value != 'manage_users'){
									$scope.user_modal.admin_user_perms.push(val);
								}
							});
						}else {
							$scope.user_modal.admin_user_perms = $scope.filter_perms;
						}
						if (row_id) {
							$scope.user_modal.show();
						}
					}
				};
				$scope.user_modal.init();
			}
		});
	}

	/**
	* Delete User
	* @param integer id
	*/
	$scope.deleteUser = function(id) {
		if (Helpers.confirmDelete()) {
			API.DELETE('user/delete/'+id).then(function(){
				Flash.create('success','تم الحذف!');
				$scope.dtInstance.reloadData();
			});
		}
	};


	ordering_column = 5;
	/**
	* Users datatable columns
	*/
	$scope.Columns = [
		DTColumnBuilder.newColumn('name').withTitle('الأسم').renderWith(function(d,t,f){
			return '<b>'+d+'</b>';
		}),
		DTColumnBuilder.newColumn('email').withTitle('البريد الألكتروني').renderWith(function(d,t,f){
			return d;
		}),
		DTColumnBuilder.newColumn('sa_id').withTitle('رقم الهوية').renderWith(function(d,t,f){
			return d;
		}),
		DTColumnBuilder.newColumn('phone').withTitle('رقم الجوال').renderWith(function(d,t,f){
			return d;
		}),
		DTColumnBuilder.newColumn('admin_group').withTitle('الصلاحية').renderWith(function(d,t,f){
			return (f.is_supervisor) ? 'الادارة' : ($filter('filter')($scope.filter_perms,{value: f.admin_group})[0].label);
		}),
		DTColumnBuilder.newColumn('created_at').withTitle('تاريخ الاضافة').renderWith(function(d){
			return $filter('dateF')(d);
		}).withOption('searchable', false),

		DTColumnBuilder.newColumn('actions').withClass('text-left').renderWith(function(d,t,f){
			var deleteBtn = (f.id != window.auth.id) ? '<button class="btn btn-icon btn-default mr-1" ng-click="deleteUser('+f.id+')"><i class="ic-delete"></i></button>' : '';
				return '<button class="btn btn-icon btn-default mr-1" ng-click="userModal('+f.id+')"><i class="ic-edit"></i></button>'+deleteBtn;
			}).withOption('searchable', false).notSortable()
		];
		break;
		case 'residents':

		$scope.rowClickHandler = function(f){
			$scope.resident.modal(f.resident_id);
		};


		ordering_column = 5;
		/**
		* Residents datatable columns
		*/
		$scope.Columns = [
			DTColumnBuilder.newColumn('module_type').withTitle('نوع الساكن').renderWith(function(d,t,f){
				return (d == 'resident') ? 'رب أسرة' : 'تابع';
			}).withOption('searchable',false),
			DTColumnBuilder.newColumn('name').withTitle('الاسم').renderWith(function(d,t,f){
				return d;
			}),
			DTColumnBuilder.newColumn('sa_id').withTitle('رقم الهوية').renderWith(function(d,t,f){
				return d;
			}),
			DTColumnBuilder.newColumn('building_code').withTitle('المبنى').renderWith(function(d,t,f){
				return d;
			}).withOption('searchable',false),
			DTColumnBuilder.newColumn('town_name').withTitle('العنوان').renderWith(function(d,t,f){
				return d+' - '+f.village_name;
			}).withOption('searchable',false),
			DTColumnBuilder.newColumn('created_at').withTitle('تاريخ الاضافة').renderWith(function(d){
				return $filter('dateF')(d);
			}).withOption('searchable', false),

			DTColumnBuilder.newColumn('actions').withClass('text-left').renderWith(function(d,t,f){
				var dropdownList = '<li><a ng-click="resident.modal('+f.resident_id+',true)">طلب تعديل البيانات</a></li><li><a ng-click="resident.setRequest(\''+f.module_type+'\','+f.id+',\'instead-lost\')">طلب بدل فاقد للبطاقة</a></li><li><a ng-click="resident.setRequest(\''+f.module_type+'\','+f.id+',\'delete\')">طلب حذف البطاقة</a></li>'
					return '<div uib-dropdown dropdown-append-to-body="true"><button class="btn btn-default mr-1 iconed btn-sm" uib-dropdown-toggle><i class="ic-card"></i>طلب جديد</button><ul class="dropdown-menu md-size" uib-dropdown-menu>'+dropdownList+'</ul></div>';
				}).withOption('searchable', false).notSortable()
			];
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
				url: baseUrl+'/panel/dt/'+$scope.parent_path+(($scope.sub_path) ? '/'+$scope.sub_path : '')+(($scope.parent_path_id) ? '/'+$scope.parent_path_id : '')+(($scope.sub_path_id) ? '/'+$scope.sub_path_id : ''),
				type: 'POST',
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
				$scope.isFirstLoading = false;

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
				$scope.isFirstLoading = false;
			}).withOption('initComplete', function(t,data,f) {
				$scope.isFirstLoading = false;
			});
		};
	}

	$scope.headerFilters = function() {
		/* Reinit results */
		$scope.reInitResults = function() {
			$scope.dtInstance.reloadData();
		};

		switch ($scope.parent_path) {
			case 'cards':
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
		if ($routeParams.status >= 0) {
			$scope.filter_data['status'] = $routeParams.status;
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
			case 'perm':
			$scope.filter_data['perm'] = val;
			$location.search('perm', val);
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
