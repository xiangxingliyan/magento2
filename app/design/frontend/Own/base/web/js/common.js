require(['jquery'], function($){
    window.showTopAd = function (imgUrl) {
        if (!imgUrl) {
            imgUrl = '/media/wysiwyg/product/top_ad_vacation.jpg';
        }

        var html = '<div class="top_ad_box"><img src="'+ imgUrl +'" alt="top ad image"></div>';

        $('.page-wrapper .page-header').prepend(html);
    };


    function resizeOverlay($overlay) {
        var $overlays = $overlay == undefined ? jQuery('.overlay') : $overlay;

        for (var i = 0; i < $overlays.length; i++) {
            var $row = $overlays.eq(0);

            if ($row.parent().length <= 0) {
                return;
            }

            if ($row.parent().prop('tagName').toLowerCase() == 'body') {
                $row.css({'height': jQuery(document).height(), 'width': jQuery(document).width()});
            } else {
                $row.css({'height': $row.parent().height(), 'width': $row.parent().width()});
            }
        }
    }


    $.extend({
        getOverlay: function (parent) {
            var $parent = parent ? $(parent) : $('body');
            var $overlay = $parent.find('> .overlay');

            if ($overlay.length == 0) {
                $overlay = $('<div class="overlay"/>');
                $overlay.css({width: $parent.innerWidth(), height: $parent.innerHeight()});
                $parent.append($overlay);
            }

            if ($parent.length > 0 && $parent.prop('tagName').toLowerCase() != 'body') {
                $overlay.css({'zIndex': 'initial'});
            }

            if (!window.handledResizeOverlay) {
                window.handledResizeOverlay = true;
                $(window).resize(function () {
                    jQuery.getOverlay().css({
                        position: 'fixed',
                        left: 0,
                        top: 0,
                        width: $('body').innerWidth(),
                        height: $('body').innerHeight()
                    });
                });
            }

            return $overlay;
        },

        getLoading: function (parent, selector,src, text) {
            var $parent = parent ? $(parent) : $('body');
            selector = selector ? selector : '> .loading';

            if(false !== text) {
                text = text ? text : '';
                text = (text === 0 || text != '') ? text : '';
            }

            var $loading = $parent.find(selector);

            if ($loading.length > 0) {
                var $p = $loading.find('p');

                if ($p.length <= 0) {
                    $p = $("<p/>");
                    $loading.append($p);
                }

                if(false !== text) {
                    $p.html(text);
                }

                $loading.find('.ave40_loading_animation').load();
            } else {
                $loading = $('<div class="loading" style="display:none;">' +
                    '<style>.loading{display:inline-block;position:fixed;z-index:10002;color: #dddddd;font-size: 1.1em;text-align: center;}' +
                    ' .ave40-loading p{padding-top:10px;}</style>' +
                    '</div>');
                var $img = $('<div class="loading_animation"></div>');

                if ($parent.length > 0 && $parent.prop('tagName').toLowerCase() != 'body') {
                    $loading.css({'position': 'absolute', 'zIndex': 1});
                }

                $img.load(function () {
                    var $p = $(this).parent().find('p');
                    var pwidth = $(this).parent().parent().innerWidth() - 2;
                    var pheight = $(this).parent().parent().innerHeight() - $p.outerHeight() - 2;
                    var w = $(this).width();
                    var h = $(this).height();

                    pwidth = pwidth > 75 ? 75 : pwidth;
                    pheight = pheight > 80 ? 80 : pheight;

                    if (pwidth < w && pheight < h) {
                        if (pwidth < pheight) {
                            $(this).css('width', pwidth);
                        } else {
                            $(this).css('height', pheight);
                        }
                    } else if (pwidth < w) {
                        $(this).css('width', pwidth);
                    } else if (pheight < h) {
                        $(this).css('height', pheight);
                    }

                    $(this).parent().letHVCenter();
                });

                $loading.append($img);

                if (String(text).length > 0) {
                    var $p = $('<p>' + text + '</p>');
                    $loading.append($p);
                }

                $parent.append($loading);
            }

            if (!$loading.setAlertText) {
                $loading.setAlertText = (function ($loading) {
                    return function (text) {
                        var $p = $loading.find('p');

                        if ($p.length <= 0) {
                            $p = $('<p/>');
                            $loading.append($p);
                        }

                        $p.html(text);
                        return $loading;
                    }
                })($loading);
            }

            return $loading;
        },

        showLoading: function (text) {
            var loading = jQuery.getLoading(null, null, null, text);

            if(loading[0].showCount) {
                loading[0].showCount ++;
            } else {
                loading[0].showCount = 1;
            }

            loading.showPopupBox();
        },

        closeLoading: function () {
            var loading = jQuery.getLoading(null, null, null, false);
            loading[0].showCount --;

            if(loading[0].showCount <= 0) {
                loading.closePopupBox();
                loading[0].showCount = 0;
            } else {
                jQuery.getOverlay().hideOverlay();
            }
        },

        messageBox:function (text) {
            var $content = $('<div class="message-box"></div>');
            $content.html(text);
            $('body').append($content);
            $content.showPopupBox();
            setTimeout(function () {
                $content.closePopupBox();
                $content.remove();
            }, 3000);
        }



    });


    $.fn.extend({

        /**
         *  倒计时
         *  data-start-time 开始时间
         *  data-end-time 结束时间
         */
        countDown: function() {
            var $this = $(this);
            var start_time = new Date($this.data('startTime')).getTime();
            var end_time = new Date($this.data('endTime')).getTime();
            var day_elem =  $this.find('.js-countdown-day');        //天
            var hour_elem =  $this.find('.js-countdown-hour');      //时
            var minute_elem =  $this.find('.js-countdown-minute');  //分
            var second_elem =  $this.find('.js-countdown-second');  //秒


            if (!start_time) {
                var localStartDate = new Date();
                start_time = localStartDate.getTime();
                var localStartOffset = localStartDate.getTimezoneOffset()*60*1000 + 8*60*60*1000;
                start_time = start_time + localStartOffset
            }else{
                start_time = new Date(start_time).getTime();
            }

            if (!end_time) {
                return ;
            }

            var stamp = (end_time - start_time) / 1000;

            var timer = setInterval(function () {
                if (stamp > 0) {
                    stamp -= 1;
                    var day = Math.floor((stamp / 3600) / 24);
                    var hour = Math.floor((stamp / 3600) % 24);
                    var minute = Math.floor((stamp / 60) % 60);
                    var second = Math.floor(stamp % 60);

                    day = (day > 9 ? day : (day > 0 ? ('0' + day ) : ''));
                    hour = (hour > 9 ? hour : '0' + hour);
                    minute = (minute > 9 ? minute : '0' + minute);
                    second = (second > 9 ? second : '0' + second);

                    //计算天
                    if (day_elem && day){
                        $(day_elem).text(day);
                    } else {
                        day_elem.addClass("hide");
                    }

                    //计算时、分、秒
                    $(hour_elem).text(hour);
                    $(minute_elem).text(minute);
                    $(second_elem).text(second);
                } else {
                    clearInterval(timer);
                }
            }, 1000);
        },


        /**
         * 让某个块在元素中居中
         */
        letHVCenter: function () {
            var display = $(this).css('display');
            var visibility = $(this).css('visibility');

            $(this).css({
                'diplay': 'block', 'visibility': 'hidden'
            })
                .css({
                    'left': '50%',
                    'top': '50%',
                    'marginTop': (-$(this).outerHeight() / 2) + 'px',
                    'marginLeft': (-$(this).outerWidth() / 2) + 'px'
                })
                .css({
                    'display': display,
                    'visibility': visibility
                });

            return this;
        },

        showOverlay: function (duration) {
            var $overlay = $(this);
            duration = 300;

            if ($overlay.prop('overlayIndex') > 0) {
                $overlay.prop('overlayIndex', $overlay.prop('overlayIndex') + 1);
                return $overlay;
            } else {
                $overlay.prop('overlayIndex', 1);
                $overlay.css({'opacity': 0}).show();
                resizeOverlay($overlay);
                $overlay.stop().animate({opacity: 0.4}, duration);
            }

        },

        /**
         * 元素以overlay形式隐藏
         * @param duration
         */
        hideOverlay: function (duration) {
            var $overlay = $(this);
            $overlay.prop('overlayIndex', $overlay.prop('overlayIndex') - 1);

            duration = 0.4;

            if ($overlay.prop('overlayIndex') > 0) {
                return;
            }

            $overlay.stop().animate({opacity: 0}, duration, null, function () {
                $overlay.hide();
            });
        },

        animateShow: function () {
            var $this = $(this);
            $this.show();
            var defaultScale = 1.1;
            var defaultOpacity = 0;
            var perspective = 0;
            var defaultTranslateY =  -$this.outerHeight() * (defaultScale - 1) / 2;
            var defaultRotateX = 0;
            var defaultTranslateX = 0;
            var defaultDuration = 300;

            $this.css('opacity', defaultOpacity).css('transform', 'perspective(' + perspective + 'px) ' +
                'rotateX(' + defaultRotateX + 'deg) ' +
                'translateY(' + defaultTranslateY + 'px)');

            $this.stop().animate({animateUnit: 1000}, {
                step: function (now) {
                    var percent = now / 1000;
                    var deg = defaultRotateX * (1 - percent);
                    var translateY = defaultTranslateY * (1 - percent);
                    var translateX = defaultTranslateX * (1 - percent);
                    var scale = defaultScale + percent * (1 - defaultScale);

                    $(this).css('opacity', (defaultOpacity + (percent * (1 - defaultOpacity))));
                    $(this).css('transform', 'perspective(' + perspective + 'px) ' +
                        'scale(' + scale + ') ' +
                        'rotateX(' + deg + 'deg) ' +
                        'translate(' + translateX + 'px, ' + translateY + 'px) ');
                },
                duration: defaultDuration
            });
        },

        animateHide: function() {
            var $this = $(this);
            var defaultScale = 1.1;
            var defaultOpacity = 0;
            var perspective = 0;
            var defaultTranslateY =  -$this.outerHeight() * (defaultScale - 1) / 2;
            var defaultRotateX = 0;
            var defaultTranslateX = 0;
            var defaultDuration = 300;

            $this.stop().show().animate({animateUnit: 0}, {
                step: function (now) {
                    var percent = now / 1000;
                    var deg = defaultRotateX * (1 - percent);
                    var translateY = defaultTranslateY * (1 - percent);
                    var translateX = defaultTranslateX * (1 - percent);
                    var scale = defaultScale + percent * (1 - defaultScale);

                    jQuery(this).css('opacity', (defaultOpacity + (percent * (1 - defaultOpacity))));
                    jQuery(this).css('transform', 'perspective(' + perspective + 'px) ' +
                        'scale(' + scale + ') ' +
                        'rotateX(' + deg + 'deg) ' +
                        'translate(' + translateX + 'px, ' + translateY + 'px) ');
                },
                duration: defaultDuration,
                complete: function () {
                    $this.hide();
                }
            });

        },

        showPopupBox: function ($overlay) {
            $(this).letHVCenter();
            $overlay = $overlay ? $overlay : true;

            if ($overlay) {
                if ($overlay === true) {
                    $overlay = jQuery.getOverlay();
                }
            }

            $(this).animateShow();

            if ($overlay) {
                $overlay.showOverlay();
            }
            return this;
        },

        closePopupBox: function ($overlay) {
            $overlay = $overlay == undefined ? true : $overlay;

            if ($overlay) {
                if ($overlay === true) {
                    $overlay = jQuery.getOverlay();
                }
            }

            $(this).animateHide();

            if ($overlay) {
                $overlay.hideOverlay();
            }
        },

        serializeObject: function() {
            var o = {};
            var a = this.serializeArray();
            $.each(a, function () {
                if (o[this.name]) {
                    if (!o[this.name].push) {
                        o[this.name] = [o[this.name]];
                    }
                    o[this.name].push(this.value || '');
                } else {
                    o[this.name] = this.value || '';
                }
            });
            return o;
        },

    });

    $(window).resize(function () {
        jQuery.getOverlay().css({
            position: 'fixed',
            left: 0,
            top: 0,
            width: $('body').innerWidth(),
            height: $('body').innerHeight()
        });
    });
});