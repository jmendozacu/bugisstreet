var bso = jQuery('#bso');
var overlay = jQuery('<div style="width:100%; height: 100%; background-color: rgba(0,0,0,.7); position: relative; position: absolute; top:0; left:0; z-index:30;"></div>');
var updatedWidth = jQuery(window).width();
var updatedHeight = jQuery(window).height();
var checkactive;

/*function closeMNav() {
    bso.removeClass('lToggled');
    bso.removeClass('rToggled');
    jQuery('.mobilenav').removeClass('toggled');
    jQuery('.mobilefilter').removeClass('toggled');
    jQuery('body').css({
        'position': 'relative'
    });
    overlay.remove();
}

function closeMenus() {
    jQuery('.filter-over').removeClass('toggled');
    jQuery('.filter-cat').removeClass('toggled');
    jQuery('.sort-over').removeClass('toggled');
    jQuery('.mobilecurrency').hide();
    jQuery('.search-nav').removeClass('active');
    jQuery('li a.search').removeClass('active');
    jQuery('.loginbox').removeClass('active');
    jQuery('.loginboxsuccess').removeClass('active');
    jQuery('li a.login').removeClass('active');
    jQuery('.wantlist-drop').removeClass('active');
    jQuery('li a.wantlist-top').removeClass('active');
    jQuery('.cart-drop').removeClass('active');
    jQuery('li a.cart').removeClass('active');
    jQuery('.social-drop').removeClass('active');
    jQuery('li a.social').removeClass('active');
    jQuery('.currency-content').removeClass('active');
    jQuery('li a.currency').removeClass('active');
}

function mlnavToggle() {
    closeMenus();
    jQuery('body').css({
        'position': 'fixed'
    });
    jQuery('.mobilenav').addClass('toggled');
    bso.addClass('lToggled');
    overlay.on({
        'click': function() {
            bso.removeClass('lToggled');
            jQuery('.mobilenav').removeClass('toggled');
            jQuery('body').css({
                'position': 'relative'
            });
            jQuery(this).remove();
        }
    });
    bso.prepend(overlay);
    jQuery('.nav-close').click(function() {
        closeMNav();
    });
}

function mrnavToggle() {
    closeMenus();
    jQuery('body').css({
        'overflow': 'hidden'
    });
    jQuery('.mobilefilter').addClass('toggled');
    bso.addClass('rToggled');
    overlay.on({
        'click': function() {
            bso.removeClass('rToggled');
            jQuery('.mobilefilter').removeClass('toggled');
            jQuery('body').css({
                'position': 'relative'
            });
            jQuery(this).remove();
        }
    });
    bso.prepend(overlay);
    jQuery('.nav-close').click(function() {
        closeMNav();
    });
}

function wantlistCNavToggle() {
    closeMenus();
    jQuery('.mywantlist-mobilecat').addClass('toggled');
    bso.addClass('rToggled');
    overlay.on({
        'click': function() {
            bso.removeClass('lToggled');
            jQuery('.mobilefilter').removeClass('toggled');
            jQuery('body').css({
                'position': 'relative'
            });
            jQuery(this).remove();
        }
    });
    bso.prepend(overlay);
    jQuery('.nav-close').click(function() {
        closeMNav();
    });
}

function wantlistFNavToggle() {
    closeMenus();
    jQuery('.mywantlist-mobilefilter').addClass('toggled');
    bso.addClass('rToggled');
    overlay.on({
        'click': function() {
            bso.removeClass('lToggled');
            jQuery('.mobilefilter').removeClass('toggled');
            jQuery('body').css({
                'position': 'relative'
            });
            jQuery(this).remove();
        }
    });
    bso.prepend(overlay);
    jQuery('.nav-close').click(function() {
        closeMNav();
    });
}*/

function calBoxCol() {
    var contentWidth = jQuery('.content').width();
    var boxCols = 0;
    if (contentWidth > 1900) {
        boxCols = 7;
    } else if (contentWidth < 1900 && contentWidth > 1400) {
        boxCols = 6
    } else if (contentWidth < 1400 && contentWidth > 1000) {
        boxCols = 5
    } else if (contentWidth < 1000 && contentWidth > 800) {
        boxCols = 4
    } else if (contentWidth < 800 && contentWidth > 640) {
        boxCols = 3
    } else if (contentWidth < 640 && contentWidth > 480) {
        boxCols = 2
    } else if (contentWidth < 480) {
        boxCols = 1
    }
    if (boxCols > 1) {
        jQuery('.index-carousel').css({
            'width': (contentWidth - boxCols) / (boxCols / 2) + 1,
            'height': (contentWidth - boxCols) / (boxCols / 2) + 1
        });
        if (jQuery('.content').hasClass('bso-index')) {
            jQuery('.individual-item').css({
                'width': ((contentWidth - boxCols) / boxCols) + 1,
                'height': ((contentWidth - boxCols) / boxCols) + 1
            });
        } else if (jQuery('.content').hasClass('bso-product-listing')) {
            jQuery('.individual-item').css({
                'width': ((contentWidth - boxCols) / boxCols),
                'height': (((contentWidth - boxCols) / boxCols)) + 90
            });
        } else {
            jQuery('.individual-item').css({
                'width': ((contentWidth - boxCols) / boxCols),
                'height': (((contentWidth - boxCols) / boxCols) - 1)
            });
        }
    } else {
        jQuery('.index-carousel').css({
            'width': ((contentWidth - boxCols) / boxCols),
            'height': ((contentWidth - boxCols) / boxCols)
        });
        if (jQuery('.content').hasClass('bso-index')) {
            jQuery('.individual-item').css({
                'width': ((contentWidth - boxCols) / boxCols) + 1,
                'height': ((contentWidth - boxCols) / boxCols) + 1
            });
        } else if (jQuery('.content').hasClass('bso-product-listing')) {
            jQuery('.individual-item').css({
                'width': ((contentWidth - boxCols) / boxCols),
                'height': (((contentWidth - boxCols) / boxCols)) + 90
            });
        } else {
            jQuery('.individual-item').css({
                'width': ((contentWidth - boxCols) / boxCols),
                'height': (((contentWidth - boxCols) / boxCols) - 1)
            });
        }
    }
    jQuery('.index-carousel .wrapper').css({
        'height': jQuery('.index-carousel').height()
    });
    jQuery('.index-slider li').css({
        'width': jQuery('.index-carousel').width()
    });
    if (jQuery('.content').hasClass('bso-index')) {
        jQuery('.individual-item .wrapper').css({
            'height': jQuery('.individual-item').height()
        });
    } else if (jQuery('.content').hasClass('bso-product-listing')) {
        jQuery('.individual-item .wrapper').css({
            'height': jQuery('.individual-item').height() - 90
        });
    } else if (jQuery('.content').hasClass('bso-mywantlist')) {
        jQuery('.wantlist-item .wrapper').css({
            'height': jQuery('.wantlist-item').height() - 20
        });
    } else {
        jQuery('.individual-item .wrapper').css({
            'height': jQuery('.individual-item').height()
        });
    }
}

function calBoxCol_wantlist() {
    var contentWidth = jQuery('.mywantlist').width();
    var boxCols = 0;
    if (contentWidth > 1900) {
        boxCols = 7;
    } else if (contentWidth < 1900 && contentWidth > 1400) {
        boxCols = 6
    } else if (contentWidth < 1400 && contentWidth > 1000) {
        boxCols = 5
    } else if (contentWidth < 1000 && contentWidth > 800) {
        boxCols = 4
    } else if (contentWidth < 800 && contentWidth > 640) {
        boxCols = 3
    } else if (contentWidth < 640 && contentWidth > 480) {
        boxCols = 2
    } else if (contentWidth < 480) {
        boxCols = 1
    }
    var boxwidth = Math.floor(contentWidth / boxCols);
    jQuery("#bso .rightwrap .content.bso-mywantlist .box").css("width", boxwidth);
    jQuery("#bso .rightwrap .content.bso-mywantlist .box .wrapper").css("height", boxwidth);
}
jQuery(document).ready(function() {
    jQuery('#searchinput').data('holder', jQuery('#searchinput').attr('placeholder'));
    jQuery('#searchinput-mobile').data('holder', jQuery('#searchinput-mobile').attr('placeholder'));
    jQuery('#searchinput').focusin(function() {
        jQuery(this).attr('placeholder', '');
    });
    jQuery('#searchinput-mobile').focusin(function() {
        jQuery(this).attr('placeholder', '');
    });
    jQuery('#searchinput').focusout(function() {
        jQuery(this).attr('placeholder', jQuery(this).data('holder'));
    });
    jQuery('#searchinput-mobile').focusout(function() {
        jQuery(this).attr('placeholder', jQuery(this).data('holder'));
    });
    jQuery(".qtyfield").keydown(function(e) {
        if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 || (e.keyCode == 65 && e.ctrlKey === true) || (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        if (jQuery(this).val().length > 1 || (e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
});
jQuery(window).resize(function() {
    if (jQuery(document.activeElement).attr('type') === 'text') {
        calBoxCol();
        calBoxCol_wantlist();
        updatedWidth = jQuery(window).width();
        updatedHeight = jQuery(window).height();
        closeMenus();
        jQuery('.extra.thank-you').css({
            'min-height': (updatedHeight - 3)
        });
    } else {
        calBoxCol();
        calBoxCol_wantlist();
        updatedWidth = jQuery(window).width();
        updatedHeight = jQuery(window).height();
        closeMNav();
        closeMenus();
        jQuery('.extra.thank-you').css({
            'min-height': (updatedHeight - 3)
        });
    }
    detailSliderControls();
});

function refresh_open_fancy(width) {
    jQuery('.open-fancy').click(function(e) {

        e.preventDefault();
        var current = jQuery(this).attr('data-seq');
        var _link = jQuery(this).attr('href');
        var fullimg = jQuery(this).next('.imgwrap').children('img').attr('data-zoom-image');
        var thumbcount = jQuery('.slideitem:not(.bx-clone)').children('.imgwrap').children('img').size();
        jQuery.fancybox({
            type: 'inline',
            maxWidth: 730,
            maxHeight: 800,
            href: _link,
            beforeShow: function() {
                jQuery('body').css({
                    'overflow-y': 'hidden'
                });
                jQuery('.fullImg').append('<img class="galleryimg" src="' + fullimg + '" />');
                jQuery('.fancybox-inner').css({'min-width': width});
                jQuery('.fancybox-wrap').css({'min-width': width});
            },
            afterClose: function() {
                jQuery('body').css({
                    'overflow-y': 'visible'
                });
                jQuery('.fullImg').empty();
                jQuery('.thmimgwrapper').empty();
            }
        });
        jQuery('.fullImg').css({
            'height': jQuery('#gallery').height()
        });
        var divHeight, wrapHeight = jQuery('.fancybox-inner').height();
        divHeight = jQuery('.fullImg').children('img').height();
        var heightPercent = (divHeight - wrapHeight) / wrapHeight;
        jQuery('.fancybox-wrap').mousemove(function(e) {
            var y = e.pageY - ((jQuery(window).height()) / 10);
            y = y * heightPercent;
            jQuery('.fullImg').scrollTop(y);
        });
        for (var i = 1; i <= thumbcount; i++) {
            jQuery('.thmimgwrapper').append('<div class="thumbwrap"></div>');
        }
        jQuery('.thumbwrap').each(function(i) {
            jQuery(this).addClass('thumb' + (i + 1));
        });
        jQuery('.' + current).addClass('active');
        jQuery('.slideitem:not(.bx-clone)').children('.imgwrap').children('img').each(function(i) {
            var imagesrc = jQuery(this).attr('data-zoom-image');
            jQuery('.thumbwrap').eq(i).append('<img src="' + imagesrc + '" />');
        });
        jQuery('.thumbwrap').click(function() {
            jQuery('.thumbwrap').removeClass('active');
            jQuery(this).addClass('active');
            var tempsrc = jQuery(this).children('img').attr('src');
            jQuery('.fullImg').children('img').attr('src', tempsrc);
        });

        jQuery('.galleryClose').click(function() {
            jQuery.fancybox.close();
            jQuery('body').css({
                'overflow-y': 'visible'
            });
            jQuery('.fullImg').empty();
            jQuery('.thmimgwrapper').empty();
        });
    });
}
var product_slider;

function detailSliderControls() {
    if (jQuery('.content').hasClass('bso-product-detail') && jQuery('.detail-slider').length > 0) {
        var tslideitems = jQuery('.bxslider-detail li').size();
        var tcloneitems = jQuery('.bxslider-detail li.bx-clone').size();
        var trealitems = tslideitems - tcloneitems;
        if (jQuery('.content').width() > (trealitems * 400)) {
            jQuery('.bxslider-prev').hide();
            jQuery('.bxslider-next').hide();
            jQuery('.bxslider-pager').hide();
        } else if (jQuery('.content').width() < (trealitems * 400)) {
            jQuery('.bxslider-prev').show();
            jQuery('.bxslider-next').show();
            jQuery('.bxslider-pager').show();
        }
    }
}
jQuery(window).load(function() {
    var thumbcount = jQuery('.slideitem:not(.bx-clone)').children('.imgwrap').children('img').size();
    var width = 0;
    for(h=0; h<thumbcount; h++){
        var text = "#image-" + h;
        //var img = jQuery(text); // Get my img elem
        var screenImage = jQuery(text);
        var theImage = new Image();
        theImage.src = screenImage.attr("data-zoom-image");
        var imageWidth = theImage.width;
        //var imageHeight = theImage.height;
        if(width<imageWidth){
            width = imageWidth;
        }
    }
    if(width > 730){
        width=730;
    }
    calBoxCol();
    calBoxCol_wantlist();
    jQuery('.indexslider').bxSlider({
        nextSelector: '#index-carousel-next',
        prevSelector: '#index-carousel-prev',
        touchEnabled: true,
        nextText: ' ',
        prevText: ' ',
        useCSS: false,
        pager: false,
        onSliderLoad: function() {
            jQuery('.indexslider li').fadeIn();
        }
    });
    jQuery('.bso-index .box').fadeIn();
    jQuery('.indexslider img').fadeIn();
    jQuery('.box img').fadeIn();
    jQuery('.zg-left').click(function() {
        var prev = jQuery('.thmimgwrapper').children('.active').removeClass('active').prev();
        if (prev.length === 0) {
            prev = jQuery('.thmimgwrapper .thumbwrap').last();
        }
        prev.addClass('active');
        var tempsrc = jQuery('.active').children('img').attr('src');
        jQuery('.fullImg').children('img').attr('src', tempsrc);
    });
    jQuery('.zg-right').click(function() {
        var next = jQuery('.thumbImg .thmimgwrapper').children('.active').removeClass('active').next();
        if (next.length === 0) {
            next = jQuery('.thmimgwrapper .thumbwrap').first();
        }
        next.addClass('active');
        var tempsrc = jQuery('.active').children('img').attr('src');
        jQuery('.fullImg').children('img').attr('src', tempsrc);
    });
    if (updatedWidth < 769) {}
    jQuery('.leftnav-menutrigger').click(function(e) {
        e.preventDefault();
        mlnavToggle();
    });
    jQuery(".btn_quickview").click(function(e) {
        e.stopPropagation();
        e.preventDefault();
        var _link = jQuery(this).attr('href');
        jQuery.fancybox({
            type: 'ajax',
            maxWidth: 800,
            href: _link,
            closeBtn: false,
            padding: 0
        });
    });
    jQuery('.filter-filters').click(function(e) {
        e.preventDefault();
        if (updatedWidth < 769) {
            jQuery('.sort-over').removeClass('toggled');
            if (jQuery('.content').hasClass('bso-mywantlist')) {
                wantlistFNavToggle();
            } else {
                mrnavToggle();
                jQuery('.mobilefilter.toggled').css({
                    'position': 'fixed'
                });
            }
        } else {
            if (jQuery('.sort-over').hasClass('toggled')) {
                jQuery('.sort-over').animate({
                    opacity: 0
                }, 100);
                jQuery('.sort-over').removeClass('toggled');
                jQuery('.filter-over').animate({
                    opacity: 1
                }, 100);
                jQuery('.filter-over').addClass('toggled');
                jQuery('#bso').click(function() {
                    jQuery('.filter-over').animate({
                        opacity: 0
                    }, 100);
                    jQuery('.filter-over').removeClass('toggled');
                });
                jQuery('.filter-over').click(function(e) {
                    e.stopPropagation();
                });
            } else if (jQuery('.filter-over').hasClass('toggled')) {
                jQuery('.filter-over').animate({
                    opacity: 0
                }, 100);
                jQuery('.filter-over').removeClass('toggled');
                jQuery('body').css({
                    'position': 'relative'
                });
            } else if (jQuery('.filter-cat').hasClass('toggled')) {
                jQuery('.filter-cat').animate({
                    opacity: 0
                }, 100);
                jQuery('.filter-cat').removeClass('toggled');
            } else {
                jQuery('.filter-over').animate({
                    opacity: 1
                }, 100);
                jQuery('.filter-over').addClass('toggled');
                jQuery('#bso').click(function() {
                    jQuery('.filter-over').animate({
                        opacity: 0
                    }, 100);
                    jQuery('.filter-over').removeClass('toggled');
                    jQuery('body').css({
                        'position': 'relative'
                    });
                });
                jQuery('.filter-over').click(function(e) {
                    e.stopPropagation();
                });
            }
            e.stopPropagation();
        }
    });
    jQuery('.filter-sort').click(function(e) {
        if (jQuery('.filter-over').hasClass('toggled')) {
            jQuery('.filter-over').animate({
                opacity: 0
            }, 100);
            jQuery('.filter-over').removeClass('toggled');
            jQuery('.sort-over').animate({
                opacity: 1
            }, 100);
            jQuery('.sort-over').addClass('toggled');
            jQuery('#bso').click(function() {
                jQuery('.sort-over').animate({
                    opacity: 0
                }, 100);
                jQuery('.sort-over').removeClass('toggled');
            });
            jQuery('.sort-over').click(function(e) {
                e.stopPropagation();
            });
        } else if (jQuery('.sort-over').hasClass('toggled')) {
            jQuery('.sort-over').animate({
                opacity: 0
            }, 100);
            jQuery('.sort-over').removeClass('toggled');
        } else if (jQuery('.filter-cat').hasClass('toggled')) {
            jQuery('.filter-cat').animate({
                opacity: 0
            }, 100);
            jQuery('.filter-cat').removeClass('toggled');
        } else {
            jQuery('.sort-over').animate({
                opacity: 1
            }, 100);
            jQuery('.sort-over').addClass('toggled');
            jQuery('#bso').click(function() {
                jQuery('.sort-over').animate({
                    opacity: 0
                }, 100);
                jQuery('.sort-over').removeClass('toggled');
            });
            jQuery('.sort-over').click(function(e) {
                e.stopPropagation();
            });
        }
        e.stopPropagation();
    });
    jQuery('.filter-category').click(function(e) {
        if (updatedWidth < 769) {
            jQuery('.filter-cat').removeClass('toggled');
            wantlistCNavToggle();
        } else {
            if (jQuery('.sort-over').hasClass('toggled')) {
                jQuery('.sort-over').animate({
                    opacity: 0
                }, 100);
                jQuery('.sort-over').removeClass('toggled');
                jQuery('.filter-cat').animate({
                    opacity: 1
                }, 100);
                jQuery('.filter-cat').addClass('toggled');
                jQuery('#bso').click(function() {
                    jQuery('.filter-cat').animate({
                        opacity: 0
                    }, 100);
                    jQuery('.filter-cat').removeClass('toggled');
                });
                jQuery('.filter-cat').click(function(e) {
                    e.stopPropagation();
                });
            } else if (jQuery('.filter-over').hasClass('toggled')) {
                jQuery('.filter-over').animate({
                    opacity: 0
                }, 100);
                jQuery('.filter-over').removeClass('toggled');
                jQuery('.filter-cat').animate({
                    opacity: 1
                }, 100);
                jQuery('.filter-cat').addClass('toggled');
                jQuery('#bso').click(function() {
                    jQuery('.filter-cat').animate({
                        opacity: 0
                    }, 100);
                    jQuery('.filter-cat').removeClass('toggled');
                });
                jQuery('.filter-cat').click(function(e) {
                    e.stopPropagation();
                });
            } else if (jQuery('.filter-cat').hasClass('toggled')) {
                jQuery('.filter-cat').animate({
                    opacity: 0
                }, 100);
                jQuery('.filter-cat').removeClass('toggled');
            } else {
                jQuery('.filter-cat').animate({
                    opacity: 1
                }, 100);
                jQuery('.filter-cat').addClass('toggled');
                jQuery('#bso').click(function() {
                    jQuery('.filter-cat').animate({
                        opacity: 0
                    }, 100);
                    jQuery('.filter-cat').removeClass('toggled');
                });
                jQuery('.filter-cat').click(function(e) {
                    e.stopPropagation();
                });
            }
            e.stopPropagation();
        }
    });
    jQuery('.swatch-color').click(function() {
        if (jQuery(this).hasClass('active')) {
            jQuery(this).removeClass('active');
            checkactive = jQuery(this).siblings('.active').length;
            jQuery(this).parent('.filter-wrap').prev('span').children('.filter_count.checkcount').html(checkactive);
        } else {
            jQuery(this).addClass('active');
            checkactive = jQuery(this).siblings('.active').length;
            checkactive = jQuery(this).siblings('.active').andSelf().length;
            jQuery(this).parent('.filter-wrap').prev('span').children('.filter_count.checkcount').html(checkactive);
        }
    });
    jQuery('.size-box').click(function() {
        if (jQuery(this).hasClass('active')) {
            jQuery('[data-value=' + jQuery(this).data('value') + ']').removeClass('active');
            checkactive = jQuery(this).siblings('.active').length;
            jQuery(this).parent('.filter-wrap').prev('span').children('.filter_count.checkcount').html(checkactive);
        } else {
            jQuery('[data-value=' + jQuery(this).data('value') + ']').addClass('active');
            checkactive = jQuery(this).siblings('.active').andSelf().length;
            jQuery(this).parent('.filter-wrap').prev('span').children('.filter_count.checkcount').html(checkactive);
        }
    });
    jQuery('.filtercb').children('input').click(function() {
        jQuery('input[value=' + jQuery(this).val() + ']').prop('checked', jQuery(this).prop('checked'));
        var checkchecked = jQuery('input:checkbox:checked').length / 2;
        jQuery(this).parent('.filtercb').parent('.filter-wrap').prev('span').children('.filter_count.checkcount').html(checkchecked);
    });
    var price_from = 0;
    var price_to = 150;
    if (typeof(filter_price_from) !== "undefined") {
        price_from = filter_price_from;
    }
    if (typeof(filter_price_to) !== "undefined") {
        price_to = filter_price_to;
    }
    jQuery('.price-slider').slider({
        range: true,
        step: 9,
        min: 0,
        max: 150,
        values: [price_from, price_to],
        slide: function(event, ui) {
            jQuery('.amount').html('$' + ui.values[0] + ' - $' + ui.values[1]);
        }
    });
    jQuery('.amount').html('$' + jQuery('.price-slider').slider('values', 0) + ' - $' + jQuery('.price-slider').slider('values', 1));
    jQuery('.clear-results').click(function() {
        jQuery('.swatch-color').removeClass('active');
        jQuery('.size-box').removeClass('active');
        jQuery('.filtercb').children('input:checkbox').removeAttr('checked');
        jQuery('.filter_count.checkcount').html('0');
        jQuery('.price-slider').slider("option", "values", [0, 150]);
        jQuery('.amount').html('$' + jQuery('.price-slider').slider('values', 0) + ' - $' + jQuery('.price-slider').slider('values', 1));
    });
    product_slider = jQuery('.bxslider-detail').bxSlider({
        slideWidth: 400,
        minSlides: 1,
        maxSlides: 2,
        moveSlides: 1,
        slideMargin: 0,
        responsive: true,

        /*startSlide: 0,
        touchEnabled: true,
        maxSlides: 6,
        moveSlides: 1,
        slideWidth: 400,
        slideMargin: 0,*/

        useCSS: true,
        infiniteLoop: true,
        pagerSelector: jQuery('.bxslider-pager'),
        slideSelector: jQuery('li.slideitem'),
        nextText: '',
        prevText: '',
        onSliderLoad: function() {
            jQuery('.imgwrap img').show();
            jQuery('.bxslider-detail .slideitem').hover(function() {
                jQuery('.open-fancy').hide();
                jQuery(this).children('.open-fancy').show();
            }, function() {
                jQuery(this).children('.open-fancy').hide();
            });
            if (jQuery('.swatch-color-singleonly').length > 0) {
                jQuery('.swatch-color-singleonly').first().trigger('click');
            }
        }
    });
    jQuery('.bxslider-next').click(function() {
        product_slider.goToNextSlide();
    });
    jQuery('.bxslider-prev').click(function() {
        product_slider.goToPrevSlide();
    });
    refresh_open_fancy(width);
    jQuery("#reviewtab").tabs();
    jQuery('.bxslider-detail li').hover(function() {
        jQuery('.open-fancy').hide();
        jQuery(this).children('.open-fancy').show();
    }, function() {
        jQuery(this).children('.open-fancy').hide();
    });
    jQuery('.r-reviews').click(function() {
        var reviewsHolder = jQuery(this).parent().parent().next('.reviews-extension');
        jQuery('.reviews-extension').hide();
        reviewsHolder.show();
        jQuery('html, body').animate({
            scrollTop: jQuery(jQuery.attr(this, 'href')).offset().top - 50
        }, 300);
        return false;
    });
    jQuery('.swatch-color-singleonly').hover(function() {
        jQuery(this).next('.magnifywrap').fadeIn();
    }, function() {
        jQuery('.magnifywrap').hide();
    });
    jQuery('.swatch-color-singleonly').click(function() {
        jQuery('.swatch-color-singleonly').removeClass('active');
        jQuery(this).addClass('active');
    });
    jQuery('.size-box-singleonly').click(function() {
        jQuery('.size-box-singleonly').removeClass('active');
        jQuery(this).addClass('active');
    });
    jQuery('.reviews-close').click(function() {
        jQuery('.reviews-extension').hide();
    });
    jQuery('.buythis').click(function() {
        return true;
    });
    jQuery(".sizeguidefb").click(function(e) {
        e.stopPropagation();
        e.preventDefault();
        _link = jQuery(this).attr('href');
        jQuery.fancybox({
            type: 'ajax',
            maxWidth: 800,
            href: _link
        });
    });
    jQuery(".edit-address-ajax-link").click(function(e) {
        e.stopPropagation();
        e.preventDefault();
        _link = jQuery(this).attr('href');
        jQuery.fancybox({
            type: 'ajax',
            maxWidth: 800,
            href: _link
        });
    });
    jQuery('.increment').click(function() {
        var maxQuantity = 99;
        var currentInput = jQuery(this).prev('input');
        currentInput.val(parseInt(currentInput.val()) + 1);
        if (currentInput.val() >= maxQuantity) {
            currentInput.val(maxQuantity);
        }
        return false;
    });
    jQuery('.decrement').click(function() {
        var minQuantity = 0;
        var currentInput = jQuery(this).next('input');
        currentInput.val(parseInt(currentInput.val()) - 1);
        if (currentInput.val() < minQuantity) {
            currentInput.val(minQuantity);
        }
        return false;
    });
    if (jQuery.fn.validationEngine) {
        jQuery("#contactform").validationEngine({
            promptPosition: "topRight:-150,5",
            autoPositionUpdate: true,
            'custom_error_messages': {
                '#contact_number': {
                    'required': {
                        'message': "Uh-oh. Did you type in the wrong phone number?"
                    }
                },
                '#subject': {
                    'required': {
                        'message': "Please select a subject."
                    }
                },
                '#email': {
                    'required': {
                        'message': "Uh-oh. Did you type in the wrong email address?"
                    }
                },
                '#first_name': {
                    'required': {
                        'message': "Whoops, please fill this out!"
                    }
                },
                '#last_name': {
                    'required': {
                        'message': "Whoops, please fill this out!"
                    }
                },
                '#form-validation-field-0': {
                    'required': {
                        'message': "Please leave a message."
                    }
                }
            }
        });
        jQuery(".contact_form_class").validationEngine({
            promptPosition: "topRight:-150,5",
            autoPositionUpdate: true,
            'custom_error_messages': {
                '#ContactNumber': {
                    'required': {
                        'message': "Uh-oh. Did you type in the wrong phone number?"
                    }
                },
                '#E-Mail': {
                    'custom': {
                        'message': "Uh-oh. Did you type in the wrong email address?"
                    }
                },
                '#FirstName': {
                    'required': {
                        'message': "Whoops, please fill this out!"
                    }
                },
                '#LastName': {
                    'required': {
                        'message': "Whoops, please fill this out!"
                    }
                },
                '#Message': {
                    'required': {
                        'message': "Please leave a message."
                    }
                }
            }
        });
        jQuery("#personalDetailF").validationEngine({
            promptPosition: "topRight:-150,5",
            autoPositionUpdate: true,
            'custom_error_messages': {
                '#contact_number': {
                    'required': {
                        'message': "Please fill in contact number."
                    }
                },
                '#first_name': {
                    'required': {
                        'message': "Whoops, please fill this out!"
                    }
                },
                '#last_name': {
                    'required': {
                        'message': "Whoops, please fill this out!"
                    }
                },
                '#img_url': {
                    'required': {
                        'message': "Uh-oh. That URL doesnâ€™t look right."
                    }
                },
                '#email': {
                    'required': {
                        'message': "Uh-oh. Did you type in the wrong email address?"
                    }
                }
            }
        });
        jQuery("#loginDetailF").validationEngine({
            promptPosition: "topRight:-150,5",
            autoPositionUpdate: true,
            'custom_error_messages': {
                '#email': {
                    'required': {
                        'message': "Whoops, it looks like you forgot to key in your email address!"
                    },
                    'custom': {
                        'message': "Uh-oh. Did you type in the wrong email address?"
                    }
                },
                '#password': {
                    'required': {
                        'message': "Whoops, please key in your password!"
                    }
                }
            }
        });
        jQuery("#loginDetailC").validationEngine({
            promptPosition: "topRight:-150,5",
            autoPositionUpdate: true
        });
        jQuery("#forgetpasswordF").validationEngine({
            promptPosition: "topRight:-150,5",
            autoPositionUpdate: true
        });
        jQuery("#checkout-shipping").validationEngine({
            promptPosition: "topRight:-150,5",
            autoPositionUpdate: true
        });
        jQuery("#checkout-billing").validationEngine({
            promptPosition: "topRight:-150,5",
            autoPositionUpdate: true
        });
        jQuery("#checkout-courier").validationEngine({
            promptPosition: "topRight:-150,5",
            autoPositionUpdate: true
        });
        jQuery('#one-step-checkout-form').validationEngine({
            promptPosition: "topRight:-150,5",
            autoPositionUpdate: true,
            'custom_error_messages': {
                '.validate[required]': {
                    'required': {
                        'message': "Whoops, please fill this out!"
                    }
                }
            }
        });
        jQuery('.account-forms').validationEngine({
            promptPosition: "topRight:-150,5",
            autoPositionUpdate: true
        });
        jQuery('#form-validate').validationEngine({
            promptPosition: "topRight:-150,5",
            autoPositionUpdate: true,
            'custom_error_messages': {
                '#email_address': {
                    'required': {
                        'message': "Uh-oh. Did you type in the wrong email address?"
                    }
                },
                '#firstname': {
                    'required': {
                        'message': "Whoops, please fill this out!"
                    }
                },
                '#lastname': {
                    'required': {
                        'message': "Whoops, please fill this out!"
                    }
                },
                '#txtpassword': {
                    'required': {
                        'message': "Whoops, please fill this out!"
                    }
                },
                '#confirmation': {
                    'required': {
                        'message': "Oops. Your passwords do not match. Try again?"
                    }
                }
            }
        });
    }
    jQuery('.checkbox-styled').click(function() {
        if (jQuery(this).is(':checked')) {
            jQuery(this).next().removeClass('ui-checkbox-off');
            jQuery(this).next().addClass('ui-checkbox-on');
        } else {
            jQuery(this).next().removeClass('ui-checkbox-on');
            jQuery(this).next().addClass('ui-checkbox-off');
        }
    });
    if (jQuery('.content').hasClass('bso-product-detail') && jQuery('.detail-slider').length > 0) {
        tslideitems = jQuery('.bxslider-detail li').size();
        tcloneitems = jQuery('.bxslider-detail li.bx-clone').size();
        trealitems = tslideitems - tcloneitems;
        if (((trealitems) < 3) && (jQuery('.content').width() > (trealitems * 400))) {
            jQuery('.bxslider-prev').hide();
            jQuery('.bxslider-next').hide();
        } else if (jQuery('.content').width() < (trealitems * 400)) {
            jQuery('.bxslider-prev').show();
            jQuery('.bxslider-next').show();
        }
    }
    jQuery('.showHideThumbs').click(function() {
        jQuery('.thumbImg').toggle('fast', function() {
            jQuery('.showHideThumbs').toggleClass('bottomPos');
        });
    });
    jQuery('.search-filters li').click(function() {
        jQuery('.search-filters li').removeClass('active');
        jQuery(this).addClass('active');
    });
});
jQuery(".inline-lbtrigger").fancybox({
    maxWidth: 730,
    maxHeight: 600,
    fitToView: false,
    width: '70%',
    autoSize: true,
    closeClick: false,
    openEffect: 'none',
    closeEffect: 'none'
});
jQuery(function() {
    var img = jQuery("#bso .rightwrap .content.bso-index .individual-item.productitems .wrapper a .imgwrap img, #bso .rightwrap .content.product-columns .individual-item.productitems .wrapper").each(function(index, value) {
        jQuery(window).load(function() {
            value.style.background = "none";
        });
    });
});