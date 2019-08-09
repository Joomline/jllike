(function(jQuery, undefined) {
	var
		$d = jq(document),
		$w = jq(window);
		
		
	var Request = new Object();

	Request = jq.extend(Request, {
		initialized: false,
		params: {},
		init: function() {
			var get = {}
			get.clean = (window.location.search).replace('?', '');
			get.parts = get.clean.split('&');
			for(var i in get.parts) {
				var matched = get.parts[i].match(/([^\s=]*)=([^\s]*)/);
				if(matched) {
					Request.params[matched[1]] = matched[2];
				}
			}
		},
		get: function(key) {
			return Request.params[key] ? Request.params[key] : null;
		}
	});

	Request.init();
	
	var Scroll = {
		
		config: {
			selectors: {
				eventContainer: '.event-container',
				visibleYears: '.year-hidden:has(.event-container:not(.event-hidden))',
				hiddenYears: 'tr:has(.event-container.event-hidden)',
				lastVisibleEvent: '.event-container:not(.event-hidden)'
			}
		},
		
		isBottom : function() {
			var pos = ($d.height() - $w.height()) - $w.scrollTop();
			
			if(pos < 150) {
				return true;
			}
			return false;
		},
		
		loadNext: function(n) {
			var
				last = this.lastVisible().index, 
				$e = this.eventsCache.slice(last, last + n + 1);
			
			$e
				.fadeIn(1000)
				.removeClass('event-hidden');
			
			this.showVisibleYears();
		},
		
		showVisibleYears: function() {
			var 
				$y = jq(this.config.selectors.visibleYears)
			
			$y
				.removeClass('year-hidden');
				
			$y.each(function(index, element) {
				jq(element)
					.next()
					.removeClass('year-spacer-hidden');
			});
		},
		
		loadUntil: function(id) {
			var
				until = -1,
				idVal = id.replace('#', '');
			
			if(!this.eventsCache) {
				this.preloadEvents();
			}
			
			this.eventsCache.each(function(index, element) {
				if(until < 0) {
					var $e = jq(element);
					if($e.attr('id') == idVal) {
						until = index;
					}
				}
			});
			
			var 
				context = this,
				$e = this.eventsCache.slice(0, until + 2),
				$scrollTo = $e.eq(until);
			
				$e
					.fadeIn(1000, function() {
						setTimeout(function() {
							context.showVisibleYears();
							if(!Scroll.scrolledTo) {
								jq('html,body')
									.animate({
										scrollTop: $scrollTo.offset().top,
										scrollLeft: $scrollTo.offset().left
									}, 1000);
							}
							Scroll.scrolledTo = true;
						}, 10);
					})
					.removeClass('event-hidden');				
		},
		
		eventsCache: null,
		
		preloadEvents: function() {
			this.eventsCache = jq(this.config.selectors.eventContainer);
			this.showVisibleYears();
			
		},
		
		lastVisible: function() {
			var 
				$elements = jq(this.config.selectors.lastVisibleEvent),
				$last = $elements.last();
				
			return {
				index: $elements.size() - 1,
				id: $last.attr('id')
			}
		}
		
	};
	
	jq.fn.pioneersScroll = function(portion) {
		Scroll.preloadEvents();
		
		$d.scroll(function(e) {
			
			if(Scroll.isBottom()) {
				Scroll.loadNext(portion || 1)
			}
			
		});
		
//		$d.bind('hashChange', function(e, newHash) {
//			Scroll.loadUntil(newHash);
//		});
		
		return this;
	};
	
	jq.fn.pioneersScrollUntil = function(id) {
		Scroll.loadUntil(id);
		
		return this;
	};
	
	$d.ready(function($) {
		jq().pioneersScroll();
		
		var hash = '#' + Request.get('social_hash');
		if(jq(hash).length > 0) {
			Scroll.loadUntil(hash)
		}
		
	});
	
})(jQuery);
