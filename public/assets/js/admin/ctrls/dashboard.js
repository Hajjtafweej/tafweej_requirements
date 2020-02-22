App.controller('DashboardCtrl', function (surveyFactory, $rootScope, $http, $filter, $compile, DTDefaultOptions, DTOptionsBuilder, DTColumnBuilder, $scope, $location, Flash, $uibModal, $routeParams, API, Helpers, $filter, $timeout) {
    $scope.isLoading = true;
    $scope.open_start_date = false;
    $scope.open_end_date = false;
    /* 1: Prepare main variables */
    $scope.currentUserRole = null;
    $scope.prepareCurrentUserRole = function (id) {
        if ($rootScope.main_lists.users_roles) {
            if (id) {
                $scope.currentUserRole = $filter('filter')($rootScope.main_lists.users_roles, {
                    id: parseInt(id)
                }, true)[0];
            } else {
                $scope.currentUserRole = $rootScope.main_lists.users_roles[0];
            }
        }
    };



    $scope.lastAnswer = {
        export: function (item) {
            var exportLoadingMessage = 'جاري تصدير الإجابة';
            if (!item.isExportAnswersLoading) {
                item.isExportAnswersLoading = true;
                Helpers.pageWrapLoading(true, exportLoadingMessage);
                return surveyFactory.exportAnswers(item.survey_id, function () {
                    item.isExportAnswersLoading = false;
                    Helpers.pageWrapLoading(false, exportLoadingMessage);
                }, item.user.id);
            }
        }
    };

    $scope.surveyUsersCount = {
        onClick: function (item) {
            if (['started', 'completed'].indexOf(item.key) > -1) {
                return $location.url('admin/surveys/answers?survey_id=' + $scope.filter_data.survey_id + '&status=' + item.key + '&user_role_id=' + $scope.currentUserRole.id);
            } else {
                return $location.url('admin/users?survey_id=' + $scope.filter_data.survey_id + '&survey_answers_status=' + item.key + '&user_role_id=' + $scope.currentUserRole.id);
            }
        }
    };

    /* 2: Prepare Charts */
    $scope.chart_data = {};
    $scope.prepareCharts = function () {
        $scope.charts = {
            // 2-1: Top users survey completion
            top_users_survey_completion: {
                options: {
                    legend: {
                        orient: 'vertical',
                        left: 'right',
                        top: 30,
                        data: $scope.chart_data.top_users_survey_completion_legends
                    },
                    tooltip: {
                        position: ['-75%', '5%'],
                        trigger: 'item',
                        formatter: function (params, ticket, callback) {
                            return '<div class="mb-1 h5 font-weight-bold">' + params.data.details.name + '</div>' +
                                '<div class="row mt-2 align-items-center"><div class="col-6 font-weight-bold text-muted">معدل الإكمال</div><div class="col-6 font-weight-bold text-primary h4">' + params.data.details.completion_rate + '%</div></div><hr>' +
                                ((params.data.details.survey_log.started_at) ? '<div class="row mt-2"><div class="col-6 font-weight-bold text-muted">تاريخ البدأ</div><div class="col-6 font-weight-bold">' + $filter('dateF')(params.data.details.survey_log.started_at, 'yyyy/MM/dd HH:mm') + '</div></div>' : '') +
                                ((params.data.details.survey_log.last_answer_at) ? '<div class="row mt-2"><div class="col-6 font-weight-bold text-muted">تاريخ آخر إجابة</div><div class="col-6 font-weight-bold">' + $filter('dateF')(params.data.details.survey_log.last_answer_at, 'yyyy/MM/dd HH:mm') + '</div></div>' : '') +
                                ((params.data.details.survey_log.completed_at) ? '<div class="row mt-2"><div class="col-6 font-weight-bold text-muted">تاريخ إكمال الأستبانة</div><div class="col-6 font-weight-bold">' + $filter('dateF')(params.data.details.survey_log.completed_at, 'yyyy/MM/dd HH:mm') + '</div></div>' : '') +
                                '';
                        },
                        extraCssText: 'padding: 20px 25px 25px;box-shadow: 0 2px 9px 0 rgba(0, 0, 0, 0.3);min-width: 350px;',
                        backgroundColor: '#fff',
                        textStyle: {
                            fontFamily: 'Janna LT',
                            fontSize: '14px',
                            color: 'initial'
                        }
                    },
                    series: [{
                        name: 'معدل الإكمال',
                        cursor: 'pointer',
                        type: 'pie',
                        radius: ['58%', '80%'],
                        avoidLabelOverlap: false,
                        center: ['30%', '50%'],
                        data: $scope.chart_data.top_users_survey_completion_data,
                        itemStyle: {
                            normal: {
                                borderColor: '#ffffff',
                                borderWidth: 6,
                            },
                        },
                        label: {
                            normal: {
                                formatter: function (params) {
                                    return '{nameStyle|' + params.name + '}\n' + params.value + '%';
                                },
                                show: false,
                                position: 'center',
                                rich: {
                                    nameStyle: {
                                        height: 30
                                    }
                                }
                            },
                            emphasis: {
                                show: true,
                                textStyle: {
                                    fontSize: '18',
                                    fontWeight: 'bold'
                                }
                            }
                        },
                        labelLine: {
                            normal: {
                                show: false
                            }
                        }
                    }]
                },
                events: [{
                    type: 'click',
                    fn: function (d) {
                        var exportLoadingMessage = 'جاري تصدير الإجابة';
                        Helpers.pageWrapLoading(true, exportLoadingMessage);
                        return surveyFactory.exportAnswers(d.data.details.survey_log.survey_id, function () {
                            Helpers.pageWrapLoading(false, exportLoadingMessage);
                        }, d.data.details.survey_log.user_id);
                    }
                }]
            }
        };
    };
    /* 3: Get Data */
    $scope.getData = function () {
        $scope.isLoading = true;
        API.GET('dashboard/statistics', $scope.filter_data).then(function (d) {
            $scope.isLoading = false;
            $scope.statistics = d.data;
            // Append default survey to filters for the first time
            if (d.data.survey) {
                $scope.filter_data.survey_id = d.data.survey.id;
                $scope.filter.list.surveys.push(d.data.survey);
            }
            // 3-1: Charts data
            // 3-1-1: Prepare data of top users survey completion chart
            $scope.chart_data.top_users_survey_completion_legends = [];
            $scope.chart_data.top_users_survey_completion_data = [];
            if ($scope.statistics.top_users_survey_completion && $scope.statistics.top_users_survey_completion.length) {
                angular.forEach($scope.statistics.top_users_survey_completion, function (v, k) {
                    $scope.chart_data.top_users_survey_completion_legends.push(v.name);
                    $scope.chart_data.top_users_survey_completion_data.push({
                        name: v.name,
                        value: v.completion_rate,
                        details: v
                    });
                });
            }

            // 3-2: Execute charts
            $scope.prepareCharts();
        });
    };

    /* 4: Filter */
    $scope.filter = {
        list: {
            surveys: []
        },
        getSurveysList: function (q, isInit) {
            if ((isInit && !$scope.filter.isSurveysListInitiated) || (!isInit && q)) {
                $scope.filter.isSurveysListLoading = true;
                if (isInit) {
                    $scope.filter.list.surveys = [];
                    $scope.filter.isSurveysListInitiated = true;
                }
                API.GET('helpers/list/surveys', {
                    user_role_id: $scope.filter_data.user_role_id,
                    q: q
                }).then(function (d) {
                    $scope.filter.isSurveysListLoading = false;
                    $scope.filter.list.surveys = d.data;
                });
            }
        }
    };
    // 4-2: Url Filter Parameters
    $scope.urlFilterParameters = function () {
        /* Prepare filters date */
        $scope.prepareFilterDate = function (val) {
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

        if ($routeParams.survey_id) {
            $scope.filter_data['survey_id'] = $routeParams.survey_id;
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
        if ($scope.currentUserRole) {
            $scope.getData();
        }
    }
    $rootScope.$watch('main_lists', function (n, o) {
        if (n) {
            $scope.prepareCurrentUserRole((($routeParams.user_role_id) ? $routeParams.user_role_id : 0));
            $scope.filter_data = {
                user_role_id: ($scope.currentUserRole) ? $scope.currentUserRole.id : 0,
                date: 'all',
                start_date: '',
                end_date: ''
            };
            $scope.urlFilterParameters();
        }
    });
    // 4-2: Filter Results
    $scope.filterResults = function (key, val) {
        $scope.start_date = '';
        $scope.end_date = '';
        switch (key) {
            case 'user_role_id':
                $scope.filter_data['user_role_id'] = val;
                $location.search('user_role_id', val);
                $scope.filter_data['survey_id'] = null;
                $location.search('survey_id', null);
                break;
            case 'survey_id':
                $location.search('survey_id', $scope.filter_data.survey_id);
                break;
        }
    }

});
