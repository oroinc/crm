/* dynamic height for central column */
$(document).ready(function () {
    var debugBar = $('.sf-toolbar');
    var content = $('#scrollable-container');
    if (!content.length) {
        content = $('#container');
    }
    content.css('overflow', 'auto');

    var anchor = $('#bottom-anchor');
    if (!anchor.length) {
        anchor = $('<div id="bottom-anchor"/>')
            .css({
                position: 'fixed',
                bottom: '0',
                left: '0',
                width: '1px',
                height: '1px'
            })
            .appendTo($(document.body));
    }

    var adjustHeight = function() {
        var debugBarHeight = 0;
        if (debugBar.length && debugBar.is(':visible')) {
            debugBarHeight = debugBar.height();
        }
        content.height(anchor.position().top - content.position().top - debugBarHeight);
    };

    var tries = 0;
    var waitForDebugBar = function()
    {
        if (debugBar.children().length) {
            adjustHeight();
        } else if (tries < 100) {
            tries++;
            window.setTimeout(waitForDebugBar, 500);
        }
    }

    if (debugBar.length) {
        waitForDebugBar();
    } else {
        adjustHeight();
    }

    $(window).on('resize', adjustHeight);
});
