(function($){
	$.fn.tmStickUp=function(options){

		var getOptions = {
			correctionSelector: $('.correctionSelector'),
			bottomCorrectionSelector: $('.correctionSelector')
		,	listenSelector: $('.listenSelector')
		,	active: false
		,	pseudo: true
		,	valign: 'top'
		}
		$.extend(getOptions, options);

		var
			_this = $(this)
		,	_window = $(window)
		,	_document = $(document)
		,	thisOffsetTop = 0
		,	thisOffsetBottom = 0
		,	thisOuterHeight = 0
		,	thisOuterWidth = 0
		,	thisMarginTop = 0
		,	thisPaddingTop = 0
		,	documentScroll = 0
		,	pseudoBlock
		,	lastScrollValue = 0
		,	scrollDir = ''
		,	tmpScrolled
		;

		if (_this.length != 0) {
			init();
		}

		function init(){
			thisOffsetTop = parseInt(_this.offset().top);
			thisOffsetBottom = parseInt(_this.offset().bottom);
			thisMarginTop = parseInt(_this.css("margin-top"));
			thisOuterHeight = parseInt(_this.outerHeight(true));
			thisOuterWidth = parseInt(_this.outerWidth(true));
			thisDocumentHeight = _document.height();

			if(getOptions.pseudo){
				$('<div class="pseudoStickyBlock"></div>').insertAfter(_this);
				pseudoBlock = $('.pseudoStickyBlock');
				pseudoBlock.css({"position":"relative", "display":"block"});
			}

			if(getOptions.active){
				addEventsFunction();
			}
		}//end init

		function addEventsFunction(){
			_document.on('scroll', function() {
				if (_this.hasClass('unfixed')){
					return;
				}
				tmpScrolled = $(this).scrollTop();
					if (tmpScrolled > lastScrollValue){
						scrollDir = 'down';
					} else {
						scrollDir = 'up';
					}
				lastScrollValue = tmpScrolled;

				if(getOptions.correctionSelector.length != 0){
					correctionValue = getOptions.correctionSelector.outerHeight(true);
					correctionOffestTop = getOptions.correctionSelector.offset().top;
				}else{
					correctionValue = 0;
					correctionOffestTop = 0;
				}
				if(getOptions.bottomCorrectionSelector.length != 0){
					bottomCorrectionValue = getOptions.bottomCorrectionSelector.outerHeight(true);
					bottomCorrectionOffestTop = getOptions.bottomCorrectionSelector.offset().top;
				} else {
					bottomCorrectionValue = 0;
					bottomCorrectionOffestTop = 0;
				}

				documentScroll = parseInt(_window.scrollTop());
				offsetTop = parseInt(_this.offset().top);
				thisHeight = parseInt(_this.height());
				windowHeight = $(window).height();
				valign = getOptions.valign;

				if (valign == 'bottom'){
					correctionViewportBottomPosition = documentScroll + windowHeight - bottomCorrectionOffestTop;
					if (thisHeight + correctionValue + Math.max(0, correctionViewportBottomPosition) < windowHeight)
					{
						valign = 'top';
					}
				}
				inverse = valign == 'bottom' ? 'top' : 'bottom';
				
				if((valign == 'top' && thisOffsetTop - correctionValue < documentScroll)
					|| (valign == 'bottom' && thisOffsetTop + thisHeight - windowHeight < documentScroll)){
					
					_this.addClass('isStuck');
					getOptions.listenSelector.addClass('isStuck');
					thisCorrectionValue = correctionValue;
					
					if (valign == 'bottom' && bottomCorrectionValue){
						
						if (correctionViewportBottomPosition > 0)
						{
							thisCorrectionValue = correctionViewportBottomPosition;
						} else {
							thisCorrectionValue = 0;
						}
					}
					
					if(getOptions.pseudo){
						_this.css({position:"fixed", g:correctionValue});
						pseudoBlock.css({"height":thisOuterHeight});
					}else{
						var styles = {position:"fixed", width: thisOuterWidth};
						styles[valign] = thisCorrectionValue;
						styles[inverse] = '';
						_this.css(styles);
					}
				}else{
					_this.removeClass('isStuck');
					getOptions.listenSelector.removeClass('isStuck');
					if(getOptions.pseudo){
						_this.css({position: '', top: '', bottom: ''});
						pseudoBlock.css({"height":0});
					}else{
						_this.css({position: '', top: '', bottom: '', width: ''});
						thisOuterWidth = parseInt(_this.outerWidth(true));
					}
				}
			}).trigger('scroll');

			_document.on("resize", function() {
				if (_this.hasClass('unfixed')){
					return;
				}
				thisDocumentHeight = _document.height();
				if(_this.hasClass('isStuck')){
					if( thisOffsetTop != parseInt(pseudoBlock.offset().top) ) thisOffsetTop = parseInt(pseudoBlock.offset().top);
				} else {
					thisOuterWidth = parseInt(_this.outerWidth(true));
					if( thisOffsetTop != parseInt(_this.offset().top) ) thisOffsetTop = parseInt(_this.offset().top);
				}
			})
		}
	}//end tmStickUp function
})(jQuery)