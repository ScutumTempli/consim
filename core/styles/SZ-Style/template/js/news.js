(function($) {
    $.fn.newsticker = function(opts) {
        // default configuration
        var config = $.extend({}, {
            height: 30,
            speed: 800,
            interval: 3000,
            vRefresh: 1,
        }, opts);
        // main function
        function init(obj) {
            var $newsticker = obj,
                $frame = $newsticker.find('.newsticker-list'),
                $item = $frame.find('.newsticker-item'),
                $next,
                startPos = 0,
                stop = false;

            function init(){
                var customizedHeight = parseInt($item.eq(0).css('height').split('px')[0]),
                    lineHeight = parseInt($newsticker.css('lineHeight').split('px')[0]);
                $newsticker.css('height', config.height); //set customized height
                startPos =  (config.height - lineHeight) / 2; //re-write start position;
                $frame.css('top', startPos);
                $item.eq(0).addClass('current'); //set start item
				$('.topic').html($item.eq(0).attr('data-topic')); // Set topic
                suspend();
               vRefresh();
            };

            function suspend(){
                $newsticker.on('mouseover mouseout', function(e) {
                    if (e.type == 'mouseover') {
                        stop = true;
                    } else { //mouseout
                        stop = false;
                    }
                }); 
            };

            function vRefresh(){
                if(config.vRefresh){
					$('.topic').css('display', 'inline');
                    setInterval(function(){
                        if (!stop){
                            var $current = $frame.find('.current');

                            $frame.animate({
                                top: '-=' + config.height + 'px'
                            }, config.speed, function(){
                                $next = $current.next();

								if($next.attr('data-topic') != $('.topic').html())
								{
									$('.topic').fadeOut(300, function() { 
										$('.topic').html($next.attr('data-topic')) 
									}).fadeIn(300);
								}
								
                                $next.addClass('current');
                                $current.removeClass('current');
                                $current.clone().appendTo($frame);
                                $current.remove();
                                $frame.css('top', startPos + 'px');
                            });
                        }
                    }, config.interval);
                }
                else{
					$frame.addClass('oneline');
					$frame.addClass('marquee');
                }
            };

            init();
        }
        // initialize every element
        this.each(function() {
            init($(this));
        });
        return this;
    };
	$('.newsticker').newsticker({
		height: 30, //default: 30
		vRefresh: 1
	});
})(jQuery);
