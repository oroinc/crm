$(document).ready(function () {
    /* dinamic height for central column */
    function changeHeight() {
        var _chWindowHeight = $(window).height();
        var _chMyHeight = _chWindowHeight - $("header").outerHeight() - $("footer").outerHeight() - 3;
        $('div.layout-content').innerHeight(_chMyHeight);
    };
    /* changeHeight();
    $(window).resize(function() {
        changeHeight();
    }); */

    /* side bar functionality */
    $('div.side-nav').each(function () {
        var myParent = $(this);
        var myParentHolder = $(myParent).parent().height() -18;
        $(myParent).height(myParentHolder);
        /* open close bar */
        $(this).find("span.maximaze-bar").click(function () {
            if (($(myParent).hasClass("side-nav-open")) || ($(myParent).hasClass("side-nav-locked"))) {
                $(myParent).removeClass("side-nav-locked side-nav-open");
                if( $(myParent).hasClass('left-panel')){
                    $(myParent).parent('div.page-container').removeClass('left-locked');
                }else{
                    $(myParent).parent('div.page-container').removeClass('right-locked');
                }
                $(myParent).find('.bar-tools').css({
                    "height": "auto",
                    "overflow" : "visible"
                })
            } else {
                $(myParent).addClass("side-nav-open");
                var openBarHeight = $("div.page-container").height() - 20;
                /* minus top-padding and bottom-padding */
                $(myParent).height(openBarHeight);
                var testBarScroll = $(myParent).find('.bar-tools').height();
                if(openBarHeight < testBarScroll ){
                    $(myParent).find('.bar-tools').height((openBarHeight - 20)).css({
                        "overflow" : "auto"
                    })
                }
            }
        });

        /* lock&unlock bar */
        $(this).find("span.lock-bar").click(function () {
            if ($(this).hasClass("lock-bar-locked")) {
                $(myParent).addClass("side-nav-open")
                    .removeClass("side-nav-locked");
                if( $(myParent).hasClass('left-panel')){
                    $(myParent).parent('div.page-container').removeClass('left-locked');
                }else{
                    $(myParent).parent('div.page-container').removeClass('right-locked');
                }
            } else {
                $(myParent).addClass("side-nav-locked")
                    .removeClass("side-nav-open");
                if( $(myParent).hasClass('left-panel')){
                    $(myParent).parent('div.page-container').addClass('left-locked');
                }else{
                    $(myParent).parent('div.page-container').addClass('right-locked');
                }

            }
            $(this).toggleClass('lock-bar-locked');
        });

        /* open&close popup for bar items when bar is minimized. */
        $(this).find('.bar-tools li').each(function () {
            var myItem = $(this);
            $(myItem).find('.sn-opener').click(function () {
                $(myItem).find("div.nav-box").fadeToggle("slow");
                var overlayHeight = $('#page').height();
                var overlayWidth = $('#page > .wrapper').width();
                $('#bar-drop-overlay').width(overlayWidth).height(overlayHeight);
                $('#bar-drop-overlay').toggleClass('bar-open-overlay');
            });
            $(myItem).find("span.close").click(function () {
                $(myItem).find("div.nav-box").fadeToggle("slow");
                $('#bar-drop-overlay').toggleClass('bar-open-overlay');
            });
            $('#bar-drop-overlay').on({
                click:function () {
                    $(myItem).find("div.nav-box").animate({
                        opacity:0,
                        display:'none'
                    }, function () {
                        $(this).css({
                            opacity:1,
                            display:'none'
                        })
                    });
                    $('#bar-drop-overlay').removeClass('bar-open-overlay');
                }
            });
        });
        /* open content for open bar */
        $(myParent).find('ul.bar-tools > li').each(function(){
            var _barLi = $(this);
            $(_barLi).find('span.open-bar-item').click(function(){
                $(_barLi).find('div.nav-content').slideToggle();
                $(_barLi).toggleClass('open-item');
            });
        });
    })

/* ============================================================
 *Oro Dropdown close prevent
 * ============================================================ */
    var dropdownToggles = $('.oro-dropdown-toggle');
    dropdownToggles.click(function(e) {
        dropdownToggles.parent().toggleClass('open')
        e.stopPropagation();
    });

    $('html').click(function(e) {
        if (!$(e.target).closest('.dropdown-close-prevent').length) {
            dropdownToggles.parent().removeClass('open')
        }
    });
 });
