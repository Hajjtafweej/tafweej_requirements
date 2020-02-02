/*
  filters.js requires all filters
	prepareFilterDate
	dateF
	price
	capitalize
	trusted
	random
	filter_lists
	asset
	global_asset
	filesize
	lang
	lang_prop
*/
/* Prepare Filter Date */
App.filter('prepareFilterDate', function($filter) {
	return function(val) {
		var sdate = '',edate = '';
		var df = 'YYYY-MM-DD';
		switch (val) {
			case 'today':
				sdate = moment().format(df);
				edate = moment().format(df);
			break;
			case 'yesterday':
				sdate = moment().subtract(1, 'day').format(df);
				edate = moment().subtract(1, 'day').format(df);
			break;
			case 'tomorrow':
				sdate = moment().startOf('day').add(1, 'day').format(df);
				edate = moment().startOf('day').add(1, 'day').format(df);
			break;
			case 'thisweek':
			sdate = moment().startOf('isoWeek').format(df);
			edate = moment().endOf('isoWeek').format(df);
			break;
			case 'lastweek':
			sdate = moment().subtract(1, 'weeks').startOf('isoWeek').format(df);
			edate = moment().subtract(1, 'weeks').endOf('isoWeek').format(df);
			break;
			case 'thismonth':
			sdate = moment().startOf('month').format(df);
			edate = moment().endOf('month').format(df);
			break;
			case 'lastmonth':
			sdate = moment().subtract(1, 'months').startOf('month').format(df);
			edate = moment().subtract(1, 'months').endOf('month').format(df);
			break;
			case 'thisquarter':
			sdate = moment().startOf('quarter').format(df);
			edate = moment().endOf('quarter').format(df);
			break;
			case 'lastquarter':
			sdate = moment().subtract(1, 'quarters').startOf('quarter').format(df);
			edate = moment().subtract(1, 'quarters').endOf('quarter').format(df);
			break;
			case 'thisyear':
			sdate = moment().startOf('year').format(df);
			edate = moment().endOf('year').format(df);
			break;
			case 'lastyear':
			sdate = moment().subtract(1, 'years').startOf('year').format(df);
			edate = moment().subtract(1, 'years').endOf('year').format(df);
			break;
			case 'expired':
				sdate = '';
				edate = moment().subtract(1, 'days').format(df);
			break;
		}

		// prepare filter dates
		if (val == 'all') {
			return {start_date: '',end_date: ''};
		} else {
			return {start_date: sdate,end_date: edate}
		}
	}
});
App.filter('dateF', function($filter) {
	return function(dateSTR,v) {
		if(dateSTR){
			if(angular.isString(dateSTR) && dateSTR.length == 10){
				return $filter('date')(new Date(dateSTR),'yyyy/MM/dd');
			}else {
				var o = (angular.isString(dateSTR)) ? dateSTR.replace(/-/g, "/") : dateSTR;
				v = (angular.isUndefined(v)) ? 'yyyy/MM/dd' : v;
				return $filter('date')(Date.parse(o + " -0000"),v,'+0000');
			}
			}else {
			return '';
		}
	}
});
App.filter('price', function($filter) {
	return function(v,without_currency) {
		return $filter('currency')(v,'',0)+((without_currency) ? '' : ' ريال');
	}
});

App.filter('capitalize', function() {
    return function(input) {
      return (!!input) ? input.charAt(0).toUpperCase() + input.substr(1).toLowerCase() : '';
    }
});

App.filter('trusted', function($sce){
	return function(html){
		return $sce.trustAsHtml(html)
	}
});

App.filter('random', function(){
	return function(v){
		min = 11111111;
    max = 99999999;
    return Math.floor(Math.random() * (max - min + 1)) + max;
	}
});

App.filter('filter_lists', function($sce,$filter){
	return function(list_type){
		var l = [];
		switch (list_type) {
			case 'dates':
				l = [
					{
						key: 'all',
						label: $filter('lang')('all_times')
					}, {
						key: 'today',
						label: $filter('lang')('today')
					}, {
						key: 'yesterday',
						label: $filter('lang')('yesterday')
					}
				];
			break;
			case 'towns':
				l = window.towns_list;
			break;

		}
		return l;
	}
});

/**
	* Provide us a full url of assets path
	* so when we use it just by code like this 'example.png' | asset: 'image'
	* then the result with show us a full url for example 'domain.com/assets/app/images/example.png'
	* so shortly it helps us to don't write a full url in View Templates
*/
App.filter('asset', function(){
	return function(v,type){
		var r = v,
				prefix = 'assets/app/';
		switch (type) {
			case 'image':
				prefix += 'images/';
			break;
			case 'excel':
				prefix += 'files/excels/';
			break;
		}
		return baseUrl+'/'+prefix+r+'?v='+assets_ver;
	}
});

/**
	* Provide us a full url of assets path
	* so when we use it just by code like this 'example.png' | asset: 'image'
	* then the result with show us a full url for example 'domain.com/assets/images/example.png'
	* so shortly it helps us to don't write a full url in View Templates
*/
App.filter('global_asset', function(){
	return function(v,type){
		var r = v,
				prefix = 'assets/';
		switch (type) {
			case 'image':
				prefix += 'images/';
			break;
			case 'uploads':
				prefix = 'uploads/';
			break;
		}
		return baseUrl+'/'+prefix+r+((type != 'uploads') ? '?v='+assets_ver : '');
	}
});

/**
	* File size
*/
App.filter('filesize', function(Helpers){
	return function(v){
		return Helpers.formatFilesize(v);
	}
});

/**
	* Lang translation
*/
App.filter('lang', function() {
	return function(v) {
		var parts = v.split( "." ),
			length = parts.length,
			i,
			property = window.lang || this;
		for ( i = 0; i < length; i++ ) {
			property = property[parts[i]];
		}
		return property;
	}
});
