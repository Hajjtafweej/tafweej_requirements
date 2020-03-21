/*
DatatableCtrl
*/
App.controller('DatatableCtrl', function ($http, $httpParamSerializer, $filter, $rootScope, $compile, DTDefaultOptions, DTOptionsBuilder, DTColumnBuilder, $scope, $location, Flash, $uibModal, $routeParams, API, Helpers, $filter, $timeout, $route, surveyFactory, userFactory, participantFactory) {
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

    $scope.page = {
        sub_pages_list: [],
        setSubPage: function (page) {
            $location.url('admin/' + $scope.parent_path + '/' + page);
        },
        init: function () {
            $scope.page.currentSubPage = $scope.sub_path;
        }
    };
    $scope.page.init();

    $timeout(function () {
        if ($scope.dtInstance.DataTable && $scope.dtInstance.DataTable.page.info().recordsTotal >= 0) {
            $scope.isDatatableLoading = false;
        }
    }, 2000);
    DTDefaultOptions.setDOM('<"table-responsive" t>p');
    $scope.rowClickHandler = function (d) {};


    /* 2: Main Helpers */
    /* 2-1: Export data modal */
    $scope.Export = function (module, without_columns, export_sub_type_data) {
        // 2-1-1: Prepare export sub module data usually used in export data of row in datatable
        if (export_sub_type_data) {
            $scope.filter_data = angular.extend($scope.filter_data, export_sub_type_data);
        }

        var filter_export = angular.copy($scope.filter_data);
        filter_export = $httpParamSerializer(filter_export);
        var export_path = (module) ? module : $scope.parent_path + (($scope.sub_path) ? '/' + $scope.sub_path : '');
        window.location.href = baseUrl + '/api/web/admin/export/' + export_path + ((filter_export) ? '?' + filter_export : '');
        if (!without_columns) {
            $scope.export_modal.cancel();
        }

    };

    /* 2-2: Ready Dropzone Options */
    $scope.prepareDzOptions = function (no_img, path) {
        var r = {
            url: baseUrl + '/api/upload',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            maxFiles: 1,
            paramName: 'file',
            acceptedFiles: 'image/jpeg, images/jpg, image/png',
            dictDefaultMessage: '<div class="img-icon d-flex justify-content-center align-items-center"><img src="' + baseUrl + '/assets/images/svgs/' + no_img + '.svg" alt="" /></div><b class="img-upload">أرفع صورة</b>',
            init: function () {
                if (path) {
                    var thisDropzone = this;
                    var mockFile = {};
                    thisDropzone.emit("addedfile", mockFile);
                    thisDropzone.emit("success", mockFile);
                    thisDropzone.emit("thumbnail", mockFile, baseUrl + '/uploads/images/' + path);
                    thisDropzone.emit("complete", mockFile);

                }
            }
        };

        return r;
    };



    $scope.prepareDzCallbacks = function (parent, model, extra_data) {
        return {
            'success': function (file, xhr) {
                if (parent) {
                    $scope[parent][model] = xhr.path;
                } else {
                    $scope[model] = xhr.path;
                }
            },
            'sending': function (file, xhr, formData) {
                if (extra_data) {
                    angular.forEach(extra_data, function (v, k) {
                        formData.append(k, v);
                    });
                }
            }

        };
    };






    /* 3: Prepare datatable columns */
    switch ($scope.parent_path) {
        case 'requirements':
        // Add (All) option to filter lists
        $scope.filter_lists = {};
        $rootScope.$watch('main_lists', function (n) {
            angular.forEach($rootScope.main_lists, function (main_list_item, main_list_key) {
                if (['holy_places', 'subjects', 'sub_subjects','levels','geographical_scopes'].indexOf(main_list_key) > -1) {
                    $scope.filter_lists[main_list_key] = angular.copy(main_list_item);
                    $scope.filter_lists[main_list_key].unshift({id: 'all',name: 'الكل'});
                }
            });
        },true);


        $scope.participant = {
            details: {},
            addRequirement: function () {
                return participantFactory.saveRequirementModal('add', null, {
                    view: 'datatable',
                    dtInstance: $scope.dtInstance
                });
            },
            editRequirement: function (id) {
                return participantFactory.saveRequirementModal('edit', id, {
                    view: 'datatable',
                    dtInstance: $scope.dtInstance
                });
            },
            deleteRequirement: function (id) {
                return participantFactory.deleteRequirement(id, {
                    view: 'datatable',
                    dtInstance: $scope.dtInstance
                });
            }
        };
            $scope.rowClickHandler = function (f) {
                return $scope.participant.editRequirement(f.id);
            };
            ordering_column = 1;

            var columns_list = [
                DTColumnBuilder.newColumn('holy_place_name').withTitle('المرحلة').renderWith(function (d, t, f) {
                    return d;
                }),
                DTColumnBuilder.newColumn('subject_name').withTitle('الموضوع').renderWith(function (d, t, f) {
                    return d;
                }),
                DTColumnBuilder.newColumn('sub_subject_name').withTitle('الموضوع الفرعي').renderWith(function (d, t, f) {
                    return d;
                }),
                DTColumnBuilder.newColumn('level_name').withTitle('المستوى').renderWith(function (d, t, f) {
                    return '<div>' + ((d && d != 'null') ? d : '') + '</div>';
                }),
                DTColumnBuilder.newColumn('geographical_scope_name').withTitle('النطاق الجغرافي').renderWith(function (d, t, f) {
                    return '<div>' + ((d && d != 'null') ? d : '') + '</div>';
                }),
                DTColumnBuilder.newColumn('participants_count').withTitle('الجهات المشاركة').renderWith(function (d, t, f) {
                    return d;
                }).withOption('searchable', false),
                DTColumnBuilder.newColumn('actions').withClass('text-left').renderWith(function (d, t, f) {
                    var editBtn = '<a class="btn btn-light mr-1 btn-icon btn-sm" ng-click="participant.editRequirement(' + f.id + ')"><i class="ic-edit"></i></a>',
                        deleteBtn = '<a class="btn btn-light mr-1 btn-icon btn-sm" ng-click="participant.deleteRequirement(' + f.id + ')"><i class="ic-delete"></i></a>';
                    return editBtn + deleteBtn;
                }).withOption('searchable', false).notSortable()
            ];
            $scope.Columns = columns_list;




        break;
        case 'participants':
        $scope.participant = {
            details: {},
            show: function (id) {
                return participantFactory.showModal(id);
            },
            add: function () {
                return participantFactory.saveModal('add', null, {
                    view: 'datatable',
                    dtInstance: $scope.dtInstance
                });
            },
            delete: function (id) {
                return participantFactory.delete(id, {
                    view: 'datatable',
                    dtInstance: $scope.dtInstance
                });
            },
            edit: function (id) {
                return participantFactory.saveModal('edit', id, {
                    view: 'datatable',
                    dtInstance: $scope.dtInstance
                });
            }
        };
                /* Participants */
                $scope.rowClickHandler = function (f) {
                    return $scope.participant.show(f.id);
                };
                ordering_column = 1;

                var columns_list = [
                    DTColumnBuilder.newColumn('name').withTitle('الجهة المشاركة').renderWith(function (d, t, f) {
                        return '<div class="widget-table-item-title">' + d + '</div>';
                    }),
                    DTColumnBuilder.newColumn('requirements_count').withTitle('عدد المتطلبات').renderWith(function (d, t, f) {
                        return d;
                    }).withOption('searchable', false),
                    DTColumnBuilder.newColumn('actions').withClass('text-left').renderWith(function (d, t, f) {
                        var printBtn = '<a class="btn btn-light-dark btn-icon btn-sm" target="_blank" uib-tooltip="طباعة المتطلبات" href="' + $rootScope.baseUrl + '/print/participant/' + f.id + '"><i class="ic-print-f"></i></a>',
                            editBtn = '<a class="btn btn-light mr-1 btn-icon btn-sm" ng-click="participant.edit(' + f.id + ')"><i class="ic-edit"></i></a>',
                            deleteBtn = '<a class="btn btn-light mr-1 btn-icon btn-sm" ng-click="participant.delete(' + f.id + ')"><i class="ic-delete"></i></a>';
                        return printBtn + editBtn + deleteBtn;
                    }).withOption('searchable', false).notSortable()
                ];
                $scope.Columns = columns_list;

        break;

    }

    /* Send search value to server */
    $scope.$watch('datatable_search', function (newValue) {
        if (!angular.isUndefined(newValue) && $scope.dtInstance.DataTable) {
            delay(function () {
                $scope.dtInstance.DataTable.search(newValue);
                $scope.dtInstance.DataTable.search(newValue).draw();
            }, 500);
        }
    });


    if ($scope.Columns) {
        //$scope.Columns.unshift(DTColumnBuilder.newColumn('id').withOption('searchable', false).notVisible());
        // تجهيز خيارات مكتبة datatable js
        $scope.setTableOptions = function () {
            return DTOptionsBuilder.newOptions()
                .withOption('ajax', {
                    url: baseUrl + '/api/web/admin/dt/' + $scope.parent_path + (($scope.sub_path) ? '/' + $scope.sub_path : '') + (($scope.parent_path_id) ? '/' + $scope.parent_path_id : '') + (($scope.sub_path_id) ? '/' + $scope.sub_path_id : ''),
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('[name="csrf-token"]').attr('content')
                    },
                    dataType: 'JSON',
                    data: function (d) {
                        // send other filters
                        angular.forEach($scope.filter_data, function (v, k) {
                            d[k] = v;
                        });
                    }
                })
                .withDataProp('data')
                .withOption('processing', true)
                .withOption('serverSide', true)
                .withOption('order', [ordering_column, ordering_type])
                .withOption('createdRow', function (row, data, dataIndex) {
                    $compile(angular.element(row).contents())($scope);

                })
                .withDisplayLength(20).withPaginationType('full_numbers').withOption('rowCallback', function (row, data) {
                    $('td', row).unbind('click');
                    $('td', row).bind('click', function ($event) {
                        var selection = window.getSelection();
                        if (selection.toString().length === 0 && ['INPUT', 'LABEL', 'A', 'BUTTON', 'I'].indexOf($($event.target).get(0).tagName) < 0) {
                            $scope.$apply(function () {
                                $scope.rowClickHandler(data);
                            });
                        }
                    });
                    return row;
                }).withOption('headerCallback', function (header) {
                    $compile(angular.element(header).contents())($scope);
                    $scope.isDatatableLoading = false;
                    
                }).withOption('drawCallback', function (settings) {

                    if (settings.json.additional_data) {
                        $scope.additional_data = settings.json.additional_data;
                    }
                    $scope.totalRecords = settings._iRecordsTotal;
                    var tbr = '.table-responsive',
                        dtp = '.dataTables_paginate',
                        nop = 'no-pagination';
                    if (settings._iRecordsTotal < 20) {
                        $(dtp).hide();
                        $(tbr).addClass(nop);
                    } else {
                        $(dtp).show().css('display', 'flex');
                        $(tbr).removeClass(nop);
                    }
                    $scope.isDatatableLoading = false;
                }).withOption('initComplete', function (t, data, f) {
                    $scope.isDatatableLoading = false;
                });
        };
    }

    $scope.headerFilters = function () {
        /* Reinit results */
        $scope.reInitResults = function () {
            $scope.dtInstance.reloadData();
        };

        switch ($scope.parent_path) {
            case 'requirements':
                $scope.filter_data.holy_place_id = 'all';
                $scope.filter_data.subject_id = 'all';
                $scope.filter_data.sub_subject_id = 'all';
                $scope.filter_data.level_id = 'all';
                $scope.filter_data.geographical_scope_id = 'all';
            break;

        }

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

        /* Filter url params */
        if ($routeParams.id) {
            $scope.filter_data['id'] = $routeParams.id;
        }
        if ($routeParams.holy_place_id) {
            $scope.filter_data['holy_place_id'] = parseInt($routeParams.holy_place_id);
        }
        if ($routeParams.subject_id) {
            $scope.filter_data['subject_id'] = parseInt($routeParams.subject_id);
        }
        if ($routeParams.sub_subject_id) {
            $scope.filter_data['sub_subject_id'] = parseInt($routeParams.sub_subject_id);
        }
        if ($routeParams.level_id) {
            $scope.filter_data['level_id'] = parseInt($routeParams.level_id);
        }
        if ($routeParams.geographical_scope_id) {
            $scope.filter_data['geographical_scope_id'] = parseInt($routeParams.geographical_scope_id);
        }

        if ($routeParams.is_active) {
            $scope.filter_data['is_active'] = $routeParams.is_active;
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

    $scope.filterResults = function (key, val) {
        if (key == 'status') {
            $route.reload();
        }


        $scope.datatableFirstLoading = true;
        $scope.showSearch = false;
        $scope.start_date = '';
        $scope.end_date = '';
        switch (key) {
            case 'is_active':
                $scope.filter_data['is_active'] = val;
                $location.search('is_active', val);
                break;
            case 'holy_place_id':
                $scope.filter_data['holy_place_id'] = val;
                $location.search('holy_place_id', val);
                break;
            case 'level_id':
                $scope.filter_data['level_id'] = val;
                $location.search('level_id', val);
                break;
            case 'subject_id':
                $scope.filter_data['subject_id'] = val;
                $location.search('subject_id', val);
                break;
            case 'sub_subject_id':
                $scope.filter_data['sub_subject_id'] = val;
                $location.search('sub_subject_id', val);
                break;
            case 'geographical_scope_id':
                $scope.filter_data['geographical_scope_id'] = val;
                $location.search('geographical_scope_id', val);
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
