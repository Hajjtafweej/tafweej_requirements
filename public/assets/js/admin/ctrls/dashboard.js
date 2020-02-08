App.controller('DashboardCtrl', function($http,$filter,$compile, DTDefaultOptions,DTOptionsBuilder, DTColumnBuilder,$scope,$location, Flash, $uibModal, $routeParams, API, Helpers,$filter,$timeout) {
	$scope.isLoading = true;
	$scope.open_start_date = false;
	$scope.open_end_date = false;
	/* 1: Prepare main variables */
	$scope.filter_data = {
		date: 'all',
		start_date: '',
		end_date: ''
	};
	/* 2: Prepare Charts */
	$scope.chart_data = {};
	$scope.prepareCharts = function(){
		// 2-1 Repeated charts options
		$scope.goalVsActual = function(type){
			var series = [];
			if ($scope.filter_data.dashboard_type != 'interviews') {
				series.push({
					barMinWidth: '10px',
					barMaxWidth: '10px',
					cursor: 'normal',
					name: 'الهدف',
					type: 'bar',
					label: {
						normal: {
							show: true,
							position: 'right'
						}
					},
					data: $scope.chart_data['goal_vs_actual_recruitment_'+type+'_goals']
				});
			}
			series.push({
				barMinWidth: '10px',
				barMaxWidth: '10px',
				cursor: 'normal',
				name: 'المتحقق',
				type: 'bar',
				data: $scope.chart_data['goal_vs_actual_recruitment_'+type+'_actuals']
			});
			return {
				options: {
					color: [
						'#395465','#ddbc5d'
					],
					tooltip: {
						trigger: 'axis',
						axisPointer: {
							type: 'shadow'
						},
						formatter: function(params, ticket, callback) {
							var actual_i = ($scope.filter_data.dashboard_type == 'interviews') ? 0 : 1;
							var actual_rate = ($scope.filter_data.dashboard_type != 'interviews') ? '<span class="text-primary">(%'+$filter('number')(params[actual_i].data/params[0].data*100,2)+')</span>' : '';
							var goal_row = ($scope.filter_data.dashboard_type != 'interviews') ? '<div class="mt-2 row"><div class="col-6"><span class="d-inline-block ml-1">'+params[0].marker+'</span><b>الهدف</b></div><div class="col-6"><b class="h4">'+params[0].data+'</b></div></div>' : '';
							return '<div class="font-weight-bold" style="font-size: 15px;word-wrap: break-word;max-width: 100%;direction:ltr;text-align:right;">'+params[actual_i].name+'</div><div style="width: 240px;">'+goal_row+'<div class="mt-2 row"><div class="col-6"><span class="d-inline-block ml-1">'+params[actual_i].marker+'</span><b>المتحقق</b></div><div class="col-6"><b class="h4">'+params[actual_i].data+' '+actual_rate+'</b></div></div></div></div>';
						},
						extraCssText: 'padding: 15px;box-shadow: 0 2px 9px 0 rgba(0, 0, 0, 0.3);',
						backgroundColor: '#fff',
						textStyle: {
							fontFamily: 'Cairo',
							fontSize: '13px',
							color: 'initial'
						}
					},
					grid: {
						top: '3%',
						left: '33%',
						right: '4%',
						bottom: '3%'
					},
					xAxis: {
						type: 'value',
						splitNumber: 3,
						boundaryGap: [0, 0],
						axisLabel: {
							formatter: function(d) {
								return d;
							}
						}
					},
					yAxis: {
						type: 'category',
						data: $scope.chart_data['goal_vs_actual_recruitment_'+type+'_categories']
					},
					series: series
				},
				events: {}
			}
		};

		$scope.workShiftJobStatistics = function(){
			var series = [];
			if ($scope.statistics.jobs && $scope.statistics.jobs.length) {
				$scope.chart_data.job_categories = [];
				$scope.chart_data.total_staff = [];
				$scope.chart_data.total_attendance = [];
				$scope.chart_data.total_absence = [];
				$scope.chart_data.total_checkin = [];
				$scope.chart_data.total_checkout = [];
				$scope.chart_data.total_correct_attendance = [];
				$scope.chart_data.total_incorrect_attendance = [];
				angular.forEach($scope.statistics.jobs,function(v,k){
					if (parseInt(v.total_staff) > 0) {
						$scope.chart_data.job_categories.push(v.name);
						$scope.chart_data.total_staff.push(v.total_staff);
						$scope.chart_data.total_attendance.push(v.total_attendance);
						$scope.chart_data.total_absence.push(v.total_absence);
						$scope.chart_data.total_checkin.push(v.total_checkin);
						$scope.chart_data.total_checkout.push(v.total_checkout);
						$scope.chart_data.total_correct_attendance.push(v.total_correct_attendance);
						$scope.chart_data.total_incorrect_attendance.push(v.total_incorrect_attendance);
					}
				});
				series = [
					{
						barMinWidth: '5px',
						barMaxWidth: '5px',
						cursor: 'normal',
						name: 'العدد الكلي',
						type: 'bar',
						data: $scope.chart_data.total_staff
					},
					{
						barMinWidth: '5px',
						barMaxWidth: '5px',
						cursor: 'normal',
						name: 'عدد الحضور الكلي',
						type: 'bar',
						data: $scope.chart_data.total_attendance
					},
					{
						barMinWidth: '5px',
						barMaxWidth: '5px',
						cursor: 'normal',
						name: 'عدد الحضور الصحيح',
						type: 'bar',
						data: $scope.chart_data.total_correct_attendance
					},
					{
						barMinWidth: '5px',
						barMaxWidth: '5px',
						cursor: 'normal',
						name: 'عدد الحضور المخالف',
						type: 'bar',
						data: $scope.chart_data.total_incorrect_attendance
					}
					// ,{
					// 	barMinWidth: '5px',
					// 	barMaxWidth: '5px',
					// 	cursor: 'normal',
					// 	name: 'عدد الدخول',
					// 	type: 'bar',
					// 	data: $scope.chart_data.total_checkin
					// },
					// {
					// 	barMinWidth: '5px',
					// 	barMaxWidth: '5px',
					// 	cursor: 'normal',
					// 	name: 'عدد الخروج',
					// 	type: 'bar',
					// 	data: $scope.chart_data.total_checkout
					// }
				];
			}
			return {
				options: {
					color: [
						'#395465','#ddbc5d','#45B39D','#f36f4e','#45B39D','#f36f4e'
					],
					tooltip: {
						trigger: 'axis',
						axisPointer: {
							type: 'shadow'
						},
						formatter: function(params, ticket, callback) {
							var total_row = '<div class="mt-2 row"><div class="col-8"><span class="d-inline-block ml-1">'+params[0].marker+'</span><b>العدد الكلي</b></div><div class="col-4"><b class="h4">'+params[0].data+'</b></div></div>';
							var total_attendance_row = '<div class="mt-2 row"><div class="col-8"><span class="d-inline-block ml-1">'+params[1].marker+'</span><b>عدد الحضور</b></div><div class="col-4"><b class="h4">'+params[1].data+'</b></div></div>';
							var total_correct_attendance_row = '<div class="mt-2 row"><div class="col-8"><span class="d-inline-block ml-1">'+params[2].marker+'</span><b>عدد الحضور الصحيح</b></div><div class="col-4"><b class="h4">'+params[2].data+'</b></div></div>';
							var total_incorrect_attendance_row = '<div class="mt-2 row"><div class="col-8"><span class="d-inline-block ml-1">'+params[3].marker+'</span><b>عدد الحضور المخالف</b></div><div class="col-4"><b class="h4">'+params[3].data+'</b></div></div>';
							var total_checkin_row = '';
							var total_checkout_row = '';
							// var total_checkin_row = '<div class="mt-2 row"><div class="col-8"><span class="d-inline-block ml-1">'+params[4].marker+'</span><b>عدد الدخول</b></div><div class="col-4"><b class="h4">'+params[4].data+'</b></div></div>';
							// var total_checkout_row = '<div class="mt-2 row"><div class="col-8"><span class="d-inline-block ml-1">'+params[5].marker+'</span><b>عدد الخروج</b></div><div class="col-4"><b class="h4">'+params[5].data+'</b></div></div>';
							return '<div class="font-weight-bold" style="font-size: 15px;word-wrap: break-word;max-width: 100%;direction:ltr;text-align:right;">'+params[0].name+'</div><div style="width: 240px;">'+total_row+total_attendance_row+total_correct_attendance_row+total_incorrect_attendance_row+total_checkin_row+total_checkout_row+'</div></div>';
						},
						extraCssText: 'padding: 15px;box-shadow: 0 2px 9px 0 rgba(0, 0, 0, 0.3);',
						backgroundColor: '#fff',
						textStyle: {
							fontFamily: 'Cairo',
							fontSize: '13px',
							color: 'initial'
						}
					},
					grid: {
						top: '3%',
						left: '33%',
						right: '4%',
						bottom: '3%'
					},
					xAxis: {
						type: 'value',
						splitNumber: 3,
						boundaryGap: [0, 0],
						axisLabel: {
							formatter: function(d) {
								return d;
							}
						}
					},
					yAxis: {
						type: 'category',
						data: $scope.chart_data.job_categories
					},
					series: series
				},
				events: {}
			}
		};
		$scope.workShiftStationStatistics = function(){
			var station_series = [];
			if ($scope.statistics.stations && $scope.statistics.stations.length) {
				$scope.chart_data.station_categories = [];
				$scope.chart_data.station_total_staff = [];
				$scope.chart_data.station_total_attendance = [];
				$scope.chart_data.station_total_absence = [];
				$scope.chart_data.station_total_checkin = [];
				$scope.chart_data.station_total_checkout = [];
				$scope.chart_data.station_total_correct_attendance = [];
				$scope.chart_data.station_total_incorrect_attendance = [];
				angular.forEach($scope.statistics.stations,function(v,k){
					$scope.chart_data.station_categories.push(v.station);
					$scope.chart_data.station_total_staff.push(v.total);
					$scope.chart_data.station_total_attendance.push(v.attendance);
					$scope.chart_data.station_total_absence.push(v.absence);
					$scope.chart_data.station_total_checkin.push(v.checkin);
					$scope.chart_data.station_total_checkout.push(v.checkout);
					$scope.chart_data.station_total_correct_attendance.push(v.correct_attendance);
					$scope.chart_data.station_total_incorrect_attendance.push(v.incorrect_attendance);
				});
				station_series = [
					{
						barMinWidth: '5px',
						barMaxWidth: '5px',
						cursor: 'normal',
						name: 'العدد الكلي',
						type: 'bar',
						data: $scope.chart_data.station_total_staff
					},
					{
						barMinWidth: '5px',
						barMaxWidth: '5px',
						cursor: 'normal',
						name: 'عدد الحضور الكلي',
						type: 'bar',
						data: $scope.chart_data.station_total_attendance
					},
					{
						barMinWidth: '5px',
						barMaxWidth: '5px',
						cursor: 'normal',
						name: 'عدد الحضور الصحيح',
						type: 'bar',
						data: $scope.chart_data.station_total_correct_attendance
					},
					{
						barMinWidth: '5px',
						barMaxWidth: '5px',
						cursor: 'normal',
						name: 'عدد الحضور المخالف',
						type: 'bar',
						data: $scope.chart_data.station_total_incorrect_attendance
					}
					// ,{
					// 	barMinWidth: '5px',
					// 	barMaxWidth: '5px',
					// 	cursor: 'normal',
					// 	name: 'عدد الدخول',
					// 	type: 'bar',
					// 	data: $scope.chart_data.station_total_checkin
					// },
					// {
					// 	barMinWidth: '5px',
					// 	barMaxWidth: '5px',
					// 	cursor: 'normal',
					// 	name: 'عدد الخروج',
					// 	type: 'bar',
					// 	data: $scope.chart_data.station_total_checkout
					// }
				];
			}
			return {
				options: {
					color: [
						'#395465','#ddbc5d','#45B39D','#f36f4e','#45B39D','#f36f4e'
					],
					tooltip: {
						trigger: 'axis',
						axisPointer: {
							type: 'shadow'
						},
						formatter: function(params, ticket, callback) {
							var total_row = '<div class="mt-2 row"><div class="col-8"><span class="d-inline-block ml-1">'+params[0].marker+'</span><b>العدد الكلي</b></div><div class="col-4"><b class="h4">'+params[0].data+'</b></div></div>';
							var total_attendance_row = '<div class="mt-2 row"><div class="col-8"><span class="d-inline-block ml-1">'+params[1].marker+'</span><b>عدد الحضور</b></div><div class="col-4"><b class="h4">'+params[1].data+'</b></div></div>';
							var total_correct_attendance_row = '<div class="mt-2 row"><div class="col-8"><span class="d-inline-block ml-1">'+params[2].marker+'</span><b>عدد الحضور الصحيح</b></div><div class="col-4"><b class="h4">'+params[2].data+'</b></div></div>';
							var total_incorrect_attendance_row = '<div class="mt-2 row"><div class="col-8"><span class="d-inline-block ml-1">'+params[3].marker+'</span><b>عدد الحضور المخالف</b></div><div class="col-4"><b class="h4">'+params[3].data+'</b></div></div>';
							var total_checkin_row = '';
							var total_checkout_row = '';
							// var total_checkin_row = '<div class="mt-2 row"><div class="col-8"><span class="d-inline-block ml-1">'+params[4].marker+'</span><b>عدد الدخول</b></div><div class="col-4"><b class="h4">'+params[4].data+'</b></div></div>';
							// var total_checkout_row = '<div class="mt-2 row"><div class="col-8"><span class="d-inline-block ml-1">'+params[5].marker+'</span><b>عدد الخروج</b></div><div class="col-4"><b class="h4">'+params[5].data+'</b></div></div>';
							return '<div class="font-weight-bold" style="font-size: 15px;word-wrap: break-word;max-width: 100%;direction:ltr;text-align:right;">'+params[0].name+'</div><div style="width: 240px;">'+total_row+total_attendance_row+total_correct_attendance_row+total_incorrect_attendance_row+total_checkin_row+total_checkout_row+'</div></div>';
						},
						extraCssText: 'padding: 15px;box-shadow: 0 2px 9px 0 rgba(0, 0, 0, 0.3);',
						backgroundColor: '#fff',
						textStyle: {
							fontFamily: 'Cairo',
							fontSize: '13px',
							color: 'initial'
						}
					},
					grid: {
						top: '3%',
						left: '13%',
						right: '4%',
						bottom: '3%'
					},
					xAxis: {
						type: 'value',
						splitNumber: 3,
						boundaryGap: [0, 0],
						axisLabel: {
							formatter: function(d) {
								return d;
							}
						}
					},
					yAxis: {
						type: 'category',
						data: $scope.chart_data.station_categories
					},
					series: station_series
				},
				events: {}
			}
		};




		switch ($scope.filter_data.dashboard_type) {
			case 'workshifts':
			$scope.charts = {
				// Attendance correction chart 1
				attendance_correction_chart_1: {
					options: {
						legend: {
							orient: 'vertical',
							left: 'right'
						},
						tooltip: {
							trigger: 'item',
							formatter: function(params, ticket, callback) {
								return '<div style="margin-bottom: 10px;"><b>'+params.data.name+'</b></div> <b>'+params.value+'</b> موظف (%'+params.percent+')';
							},
							extraCssText: 'padding: 15px;box-shadow: 0 2px 9px 0 rgba(0, 0, 0, 0.3);',
							backgroundColor: '#fff',
							textStyle: {
								fontFamily: 'Cairo',
								fontSize: '13px',
								color: 'initial'
							}
						},
						series : [
							{
								name: 'نسبة الحضور',
								cursor: 'normal',
								type: 'pie',
								radius : '75%',
								avoidLabelOverlap: false,
								center: ['35%', '50%'],
								data: [{name: 'الحضور الصحيح',value: $scope.statistics.total_correct_attendance},{name: 'الحضور المخالف',value: $scope.statistics.total_incorrect_attendance}],
								label: {
									normal: {
										show: true,
										position: 'inside',
										formatter: '{d}%'
									}
								},
								labelLine: {
									normal: {
										show: false
									}
								}
							}
						]
					},
					events: {
						type: 'click',
						fn: function(d) {

						}
					}
				},
				// Attendance vs Absence chart 2
				attendance_absence_chart_2: {
					options: {
						legend: {
							orient: 'vertical',
							left: 'right'
						},
						tooltip: {
							trigger: 'item',
							formatter: function(params, ticket, callback) {
								return '<div style="margin-bottom: 10px;"><b>'+params.data.name+'</b></div> <b>'+params.value+'</b> موظف (%'+params.percent+')';
							},
							extraCssText: 'padding: 15px;box-shadow: 0 2px 9px 0 rgba(0, 0, 0, 0.3);',
							backgroundColor: '#fff',
							textStyle: {
								fontFamily: 'Cairo',
								fontSize: '13px',
								color: 'initial'
							}
						},
						series : [
							{
								name: 'نسبة الحضور والغياب',
								cursor: 'normal',
								type: 'pie',
								radius : '75%',
								avoidLabelOverlap: false,
								center: ['35%', '50%'],
								data: [{name: 'الحضور',value: $scope.statistics.total_attendance},{name: 'الغياب',value: $scope.statistics.total_absence}],
								label: {
									normal: {
										show: true,
										position: 'inside',
										formatter: '{d}%'
									}
								},
								labelLine: {
									normal: {
										show: false
									}
								}
							}
						]
					},
					events: {
						type: 'click',
						fn: function(d) {

						}
					}
				},
				// Jobs Chart
				jobs_chart_2: $scope.workShiftJobStatistics(),
				// Stations Chart
				stations_chart_3: $scope.workShiftStationStatistics(),
			};
			break;
			case 'contracts':
			$scope.charts = {
				// Contracts chart 1
				contracts_chart_1: {
					options: {
						legend: {
							orient: 'vertical',
							left: 'right'
						},
						tooltip: {
							trigger: 'item',
							formatter: function(params, ticket, callback) {
								return '<div style="margin-bottom: 10px;"><b>'+params.data.name+'</b></div> <b>'+params.value+'</b> مرشح (%'+params.percent+')';
							},
							extraCssText: 'padding: 15px;box-shadow: 0 2px 9px 0 rgba(0, 0, 0, 0.3);',
							backgroundColor: '#fff',
							textStyle: {
								fontFamily: 'Cairo',
								fontSize: '13px',
								color: 'initial'
							}
						},
						series : [
							{
								name: 'نسبة التوقيع',
								cursor: 'normal',
								type: 'pie',
								radius : '75%',
								avoidLabelOverlap: false,
								center: ['35%', '50%'],
								data: [{name: 'وقعوا عقود',value: $scope.statistics.count_signed},{name: 'لم يوقعوا عقود',value: $scope.statistics.count_not_signed}],
								label: {
									normal: {
										show: true,
										position: 'inside',
										formatter: '{d}%'
									}
								},
								labelLine: {
									normal: {
										show: false
									}
								}
							}
						]
					},
					events: {
						type: 'click',
						fn: function(d) {

						}
					}
				},
				// 2-8: Goal vs Actual recruitment
				// 2-8-1: Waiting area
				goal_vs_actual_recruitment_waiting_area: $scope.goalVsActual('waiting_area'),
				// 2-8-2: Access control
				goal_vs_actual_recruitment_access_control: $scope.goalVsActual('access_control'),
				// 2-8-3: Station
				goal_vs_actual_recruitment_station: $scope.goalVsActual('station')
			};
				break;
			case 'training':
			$scope.charts = {
				// 2-8: Training charts
				training_chart_1: {
					options: {
						legend: {
							orient: 'vertical',
							left: 'right'
						},
						tooltip: {
							trigger: 'item',
							formatter: function(params, ticket, callback) {
								return '<div style="margin-bottom: 10px;"><b>'+params.data.name+'</b></div> <b>'+params.value+'</b> متقدم (%'+params.percent+')';
							},
							extraCssText: 'padding: 15px;box-shadow: 0 2px 9px 0 rgba(0, 0, 0, 0.3);',
							backgroundColor: '#fff',
							textStyle: {
								fontFamily: 'Cairo',
								fontSize: '13px',
								color: 'initial'
							}
						},
						series : [
							{
								name: 'نسبة الإنجاز',
								cursor: 'normal',
								type: 'pie',
								radius : '75%',
								avoidLabelOverlap: false,
								center: ['35%', '50%'],
								data: [{name: 'المجتازين',value: $scope.statistics.count_practical},{name: 'الغير مجتازين',value: $scope.statistics.count_attendance-$scope.statistics.count_practical}],
								label: {
									normal: {
										show: true,
										position: 'inside',
										formatter: '{d}%'
									}
								},
								labelLine: {
									normal: {
										show: false
									}
								}
							}
						]
					},
					events: {
						type: 'click',
						fn: function(d) {

						}
					}
				},
				// 2-8: Goal vs Actual recruitment
				// 2-8-1: Waiting area
				goal_vs_actual_recruitment_waiting_area: $scope.goalVsActual('waiting_area'),
				// 2-8-2: Access control
				goal_vs_actual_recruitment_access_control: $scope.goalVsActual('access_control'),
				// 2-8-3: Station
				goal_vs_actual_recruitment_station: $scope.goalVsActual('station')
			};
				break;
			case 'practical-training':
			$scope.charts = {
				// 2-8: Training charts
				training_chart_1: {
					options: {
						legend: {
							orient: 'vertical',
							left: 'right'
						},
						tooltip: {
							trigger: 'item',
							formatter: function(params, ticket, callback) {
								return '<div style="margin-bottom: 10px;"><b>'+params.data.name+'</b></div> <b>'+params.value+'</b> متقدم (%'+params.percent+')';
							},
							extraCssText: 'padding: 15px;box-shadow: 0 2px 9px 0 rgba(0, 0, 0, 0.3);',
							backgroundColor: '#fff',
							textStyle: {
								fontFamily: 'Cairo',
								fontSize: '13px',
								color: 'initial'
							}
						},
						series : [
							{
								name: 'نسبة الحضور',
								cursor: 'normal',
								type: 'pie',
								radius : '75%',
								avoidLabelOverlap: false,
								center: ['35%', '50%'],
								data: [{name: 'الحضور',value: $scope.statistics.count_attendance},{name: 'الغياب',value: $scope.statistics.count_absence}],
								label: {
									normal: {
										show: true,
										position: 'inside',
										formatter: '{d}%'
									}
								},
								labelLine: {
									normal: {
										show: false
									}
								}
							}
						]
					},
					events: {
						type: 'click',
						fn: function(d) {

						}
					}
				},
				// 2-8: Goal vs Actual recruitment
				// 2-8-1: Waiting area
				goal_vs_actual_recruitment_waiting_area: $scope.goalVsActual('waiting_area'),
				// 2-8-2: Access control
				goal_vs_actual_recruitment_access_control: $scope.goalVsActual('access_control'),
				// 2-8-3: Station
				goal_vs_actual_recruitment_station: $scope.goalVsActual('station')
			};
				break;
			default:
			$scope.charts = {
				// 2-2: New old staff chart
				new_old_staff: {
					options: {
						legend: {
							orient: 'vertical',
							left: 'right',
							data: ['القدامى','الجدد']
						},
						tooltip: {
							trigger: 'item',
							formatter: function(params, ticket, callback) {
								return '<div style="margin-bottom: 10px;"><b>'+params.data.name+'</b></div><b>'+params.value+'</b> متقدم (%'+params.percent+')';
							},
							extraCssText: 'padding: 15px;box-shadow: 0 2px 9px 0 rgba(0, 0, 0, 0.3);',
							backgroundColor: '#fff',
							textStyle: {
								fontFamily: 'Cairo',
								fontSize: '13px',
								color: 'initial'
							}
						},
						series : [
							{
								name: 'مجموع المتقدمين',
								cursor: 'normal',
								type: 'pie',
								radius : '75%',
								avoidLabelOverlap: false,
								center: ['35%', '50%'],
								data: [{name: 'القدامى',value: $scope.statistics.old_new.old},{name: 'الجدد',value: $scope.statistics.old_new.new}],
								label: {
									normal: {
										show: true,
										position: 'inside',
										formatter: '{c} ({d}%)'
									}
								},
								labelLine: {
									normal: {
										show: false
									}
								}
							}
						]
					},
					events: {}
				},
				// 2-3: Saudi and Foreign
				saudi_foreign: {
					options: {
						legend: {
							orient: 'vertical',
							left: 'right',
							data: ['سعودي','أجنبي']
						},
						tooltip: {
							trigger: 'item',
							formatter: function(params, ticket, callback) {
								return '<div style="margin-bottom: 10px;"><b>'+params.data.name+'</b></div><b>'+params.value+'</b> متقدم (%'+params.percent+')';
							},
							extraCssText: 'padding: 15px;box-shadow: 0 2px 9px 0 rgba(0, 0, 0, 0.3);',
							backgroundColor: '#fff',
							textStyle: {
								fontFamily: 'Cairo',
								fontSize: '13px',
								color: 'initial'
							}
						},
						series : [
							{
								name: 'مجموع المتقدمين',
								cursor: 'normal',
								type: 'pie',
								radius : '75%',
								avoidLabelOverlap: false,
								center: ['35%', '50%'],
								data: [{name: 'سعودي',value: $scope.statistics.saudi_foreign.saudi},{name: 'أجنبي',value: $scope.statistics.saudi_foreign.foreign}],
								label: {
									normal: {
										show: true,
										position: 'inside',
										formatter: '{c} ({d}%)'
									}
								},
								labelLine: {
									normal: {
										show: false
									}
								}
							}
						]
					},
					events: {
						type: 'click',
						fn: function(d) {

							// var lp = productivity_types[d.seriesIndex];
							// var lpu = '?nationality='+$scope.filter_data.nationality+'&sdate='+$scope.filter_data['start_date']+'&edate='+$scope.filter_data['end_date'];
							// var module_url = 'activities';
							// if (lp.parent == 'transactions') {
								// 	module_url = (lp.key == 'order') ? 'orders' : 'offers';
								// }else {
									// 	lpu += '&status=completed&type='+lp.key;
									// }
									// return $location.url('/'+module_url+lpu);
								}
					}
				},
				// 2-4: Interviews
				interviews: {
					options: {
						tooltip: {
							trigger: 'axis',
							axisPointer: {
								type: 'shadow'
							},
							formatter: function(params, ticket, callback) {
								return '<div class="h4 font-weight-bold">'+params[0].name+'</div><div class="mt-2"><b>'+params[0].data+'</b> متقدم</div>';
							},
							extraCssText: 'padding: 15px;box-shadow: 0 2px 9px 0 rgba(0, 0, 0, 0.3);width: 160px;',
							backgroundColor: '#fff',
							textStyle: {
								fontFamily: 'Cairo',
								fontSize: '13px',
								color: 'initial'
							}
						},
						grid: {
							top: '3%',
							left: '3%',
							right: '4%',
							bottom: '3%',
							containLabel: true
						},
						xAxis: {
							type: 'value',
							splitNumber: 3,
							boundaryGap: [0, 0.1],
							axisLabel: {
								formatter: function(d) {
									return d;
								}
							}
						},
						yAxis: {
							type: 'category',
							data: $scope.chart_data.interviews_categories
						},
						series: [
							{
								barMaxWidth: '30px',
								cursor: 'normal',
								name: 'المقابلات',
								type: 'bar',
								data: $scope.chart_data.interviews_data
							}
						]
					},
					events: {}
				},
				// 2-5: By city
				cities: {
					options: {
						legend: {
							orient: 'vertical',
							left: 'right',
							data: ['من داخل مكة','من خارج مكة']
						},
						tooltip: {
							trigger: 'item',
							formatter: function(params, ticket, callback) {
								return '<div style="margin-bottom: 10px;"><b>'+params.data.name+'</b></div><b>'+params.value+'</b> متقدم (%'+params.percent+')';
							},
							extraCssText: 'padding: 15px;box-shadow: 0 2px 9px 0 rgba(0, 0, 0, 0.3);',
							backgroundColor: '#fff',
							textStyle: {
								fontFamily: 'Cairo',
								fontSize: '13px',
								color: 'initial'
							}
						},
						series : [
							{
								name: 'مجموع المتقدمين',
								cursor: 'normal',
								type: 'pie',
								radius : '65%',
								avoidLabelOverlap: false,
								center: ['35%', '50%'],
								data: [{name: 'من داخل مكة',value: $scope.statistics.city.makkah},{name: 'من خارج مكة',value: $scope.statistics.city.other}],
								label: {
									normal: {
										show: true,
										position: 'inside',
										formatter: '{c} ({d}%)'
									}
								},
								labelLine: {
									normal: {
										show: false
									}
								}
							}
						]
					},
					events: {}
				},
				// 2-6: Ages
				ages: {
					options: {
						tooltip: {
							trigger: 'axis',
							axisPointer: {
								type: 'shadow'
							},
							formatter: function(params, ticket, callback) {
								return '<div class="h4 font-weight-bold">'+params[0].name+' سنة</div><div class="mt-2"><b>'+params[0].data+'</b> متقدم</div>';
							},
							extraCssText: 'padding: 15px;box-shadow: 0 2px 9px 0 rgba(0, 0, 0, 0.3);width: 160px;',
							backgroundColor: '#fff',
							textStyle: {
								fontFamily: 'Cairo',
								fontSize: '13px',
								color: 'initial'
							}
						},
						grid: {
							top: '3%',
							left: '3%',
							right: '4%',
							bottom: '3%',
							containLabel: true
						},
						yAxis: {
							type: 'value',
							splitNumber: 3,
							boundaryGap: [0, 0.1],
							axisLabel: {
								formatter: function(d) {
									return d;
								}
							}
						},
						xAxis: {
							type: 'category',
							data: $scope.chart_data.ages_categories
						},
						series: [
							{
								barMaxWidth: '30px',
								cursor: 'normal',
								name: 'المتقدمين',
								type: 'bar',
								data: $scope.chart_data.ages_data
							}
						]
					},
					events: {}
				},
				// 2-7: Languages
				languages: {
					options: {
						tooltip: {
							trigger: 'axis',
							axisPointer: {
								type: 'shadow'
							},
							formatter: function(params, ticket, callback) {
								return '<div class="h4 font-weight-bold">'+params[0].name+'</div><div class="mt-2"><b>'+params[0].data+'</b> متقدم</div>';
							},
							extraCssText: 'padding: 15px;box-shadow: 0 2px 9px 0 rgba(0, 0, 0, 0.3);width: 160px;',
							backgroundColor: '#fff',
							textStyle: {
								fontFamily: 'Cairo',
								fontSize: '13px',
								color: 'initial'
							}
						},
						grid: {
							top: '3%',
							left: '3%',
							right: '4%',
							bottom: '3%',
							containLabel: true
						},
						xAxis: {
							type: 'value',
							splitNumber: 3,
							boundaryGap: [0, 0.1],
							axisLabel: {
								formatter: function(d) {
									return d;
								}
							}
						},
						yAxis: {
							type: 'category',
							data: $scope.chart_data.languages_categories
						},
						series: [
							{
								barMaxWidth: '30px',
								cursor: 'normal',
								name: 'المتقدمين',
								type: 'bar',
								data: $scope.chart_data.languages_data
							}
						]
					},
					events: {}
				},
				// 2-8: Goal vs Actual recruitment
				// 2-8-1: Waiting area
				goal_vs_actual_recruitment_waiting_area: $scope.goalVsActual('waiting_area'),
				// 2-8-2: Access control
				goal_vs_actual_recruitment_access_control: $scope.goalVsActual('access_control'),
				// 2-8-3: Station
				goal_vs_actual_recruitment_station: $scope.goalVsActual('station'),
				// 2-8: Training charts
				training_chart_1: {
					options: {
						legend: {
							orient: 'vertical',
							left: 'right'
						},
						tooltip: {
							trigger: 'item',
							formatter: function(params, ticket, callback) {
								return '<div style="margin-bottom: 10px;"><b>'+params.data.name+'</b></div><b>'+params.value+'</b> متقدم (%'+params.percent+')';
							},
							extraCssText: 'padding: 15px;box-shadow: 0 2px 9px 0 rgba(0, 0, 0, 0.3);',
							backgroundColor: '#fff',
							textStyle: {
								fontFamily: 'Cairo',
								fontSize: '13px',
								color: 'initial'
							}
						},
						series : [
							{
								name: 'مجموع المرشحين للعمل',
								cursor: 'normal',
								type: 'pie',
								radius : '75%',
								avoidLabelOverlap: false,
								center: ['35%', '50%'],
								data: [{name: 'الحضور الفعلي',value: $scope.statistics.count_attendance},{name: 'المرشحين للعمل',value: $scope.statistics.count_practical}],
								label: {
									normal: {
										show: true,
										position: 'inside',
										formatter: '{c} ({d}%)'
									}
								},
								labelLine: {
									normal: {
										show: false
									}
								}
							}
						]
					},
					events: {
						type: 'click',
						fn: function(d) {

								}
					}
				},
			};
			break;
		}
	};
	/* 3: Get Data */
	$scope.getData = function(){
		API.GET('dashboard/statistics',$scope.filter_data).then(function(d){
			$scope.isLoading = false;
			$scope.statistics = d.data;
			// 3-1: Charts data
			// 3-1-1: Prepare data of average age chart
			$scope.chart_data.ages_categories = [];
			$scope.chart_data.ages_data = [];
			if ($scope.statistics.ages && $scope.statistics.ages.length) {
				angular.forEach($scope.statistics.ages,function(v,k){
					$scope.chart_data.ages_categories.push(v.age);
					$scope.chart_data.ages_data.push(v.count);
				});
			}
			// 3-1-2: Prepare data of goal vs actual recruitment
			$scope.chart_data.goal_vs_actual_recruitment_waiting_area_categories = [];
			$scope.chart_data.goal_vs_actual_recruitment_waiting_area_goals = [];
			$scope.chart_data.goal_vs_actual_recruitment_waiting_area_actuals = [];
			$scope.chart_data.goal_vs_actual_recruitment_access_control_categories = [];
			$scope.chart_data.goal_vs_actual_recruitment_access_control_goals = [];
			$scope.chart_data.goal_vs_actual_recruitment_access_control_actuals = [];
			$scope.chart_data.goal_vs_actual_recruitment_station_categories = [];
			$scope.chart_data.goal_vs_actual_recruitment_station_goals = [];
			$scope.chart_data.goal_vs_actual_recruitment_station_actuals = [];
			$scope.chart_data.goal_vs_actual_recruitment_emergency_categories = [];
			$scope.chart_data.goal_vs_actual_recruitment_emergency_goals = [];
			$scope.chart_data.goal_vs_actual_recruitment_emergency_actuals = [];

			if ($scope.statistics.count_recuirtment && $scope.statistics.count_recuirtment.length) {
				angular.forEach($scope.statistics.count_recuirtment,function(v,k){
					$scope.chart_data['goal_vs_actual_recruitment_'+v.group+'_categories'].push(v.name);
					$scope.chart_data['goal_vs_actual_recruitment_'+v.group+'_goals'].push(v.goal);
					$scope.chart_data['goal_vs_actual_recruitment_'+v.group+'_actuals'].push(v.actual);
				});
			}
			switch ($scope.filter_data.dashboard_type) {
				case 'training': case 'practical-training':

					break;
				case 'contracts':

					break;
				case 'workshifts':

					break;
				default:
				// 3-1-3: Prepare data of languages chart
				$scope.chart_data.languages_categories = [];
				$scope.chart_data.languages_data = [];
				angular.forEach($scope.statistics.languages,function(v,k){
					$scope.chart_data.languages_data.push(v);
					$scope.chart_data.languages_categories.push($filter('filter')($filter('filter_lists')('languages'),{key: k})[0].label);
				});
				// 3-1-4: Prepare data of interviews chart
				var ac = $scope.statistics.attended_accepted;
				$scope.chart_data.interviews_data = [ac.attended,ac.accepted,ac.rejected];
				$scope.chart_data.interviews_categories = ['المقابلات','المقبولين','المرفوضين'];
				break;
			}

			// 3-2: Execute charts
			$scope.prepareCharts();
		});
	};

	/* 4: Filter */
	// 4-1: Url Filter Parameters
	$scope.urlFilterParameters = function() {
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


		if ($routeParams.user_role_id) {
			$scope.filter_data['user_role_id'] = $routeParams.user_role_id;
		}

		if ($routeParams.user_id) {
			$scope.filter_data['user_id'] = $routeParams.user_id;
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
		$scope.getData();
	}
	$scope.urlFilterParameters();
	// 4-2: Filter Results
	$scope.filterResults = function(key, val) {
		$scope.start_date = '';
		$scope.end_date = '';
		switch (key) {
			case 'dashboard_type':
			$scope.filter_data['dashboard_type'] = val;
			$location.search('dashboard_type', val);
			break;
			case 'day':
			$scope.filter_data['day'] = val;
			$location.search('day', val);
			break;
			case 'station':
			$scope.filter_data['station'] = val;
			$location.search('station', val);
			break;
			case 'station_shift':
			$scope.filter_data['station_shift'] = val;
			$location.search('station_shift', val);
			break;
			case 'nationality':
			$scope.filter_data['nationality'] = val;
			$location.search('nationality', val);
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

});
