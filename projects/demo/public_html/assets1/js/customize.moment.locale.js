(function($) {
	moment.updateLocale("ja", $.extend(moment.localeData(), {
		'week' : {
			dow : 1,
			doy : 0
		}
	}));
	moment.updateLocale("vi", $.extend(moment.localeData(), {
		'week' : {
			dow : 1,
			doy : 0
		}
	}));
	moment.updateLocale("en", $.extend(moment.localeData(), {
		'week' : {
			dow : 1,
			doy : 0
		}
	}));
})(jQuery);