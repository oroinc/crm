/**
 * Router for hash navigation
 *
 * @class   OroApp.hashNavigation
 * @extends OroApp.Router
 */
OroApp.hashNavigation = OroApp.Router.extend({
    /**
     * Selector for all links that will be processed by hash navigation
     *
     * @property {String}
     * */
    selector: 'a:not([href^=#],[href^=javascript]), .extralist span[data-url]',

    /** @property {String} */
    baseUrl: '',

    /** @property */
    encodedStateData: '',

    /** @property */
    routes: {
        "*page(&g/*encodedStateData)": "defaultAction"
    },

    /**
     * Routing default action
     *
     * @param {String} page
     * @param {String} encodedStateData
     */
    defaultAction: function (page, encodedStateData) {
        this.encodedStateData = encodedStateData;
        if (page) {
            this.loadPage(page);
        }
    },

    /**
     * Initialaize hash navigation
     *
     * @param options
     */
    initialize: function(options) {
        options = options || {};
        if (!options.baseUrl) {
            throw new TypeError("'baseUrl' is required");
        }

        this.baseUrl = options.baseUrl;

        this.init();

        OroApp.Router.prototype.initialize.apply(this, arguments);
    },

    /**
     * Set active menu class depending on url
     *
     * @param {String} url
     */
    setActiveMenu: function(url) {
        $('.application-menu a').parents('li').removeClass('active');
        var li = $('.application-menu a[href="' + url + '"]').parents('li');
        li.addClass('active');
        var tabId = li.parents('.tab-pane').attr('id');
        $('.application-menu a[href=#' + tabId + ']').tab('show');
        
    },

    /**
     * Ajax call for loading page content
     *
     * @param {String} page
     */
    loadPage: function (page) {
        var url = this.getHashUrl(page);
        if (url) {
            var pageUrl = this.baseUrl + url;
            this.setActiveMenu(url);
            $.ajax({
                url:pageUrl,

                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert('Error Message: '+textStatus);
                    alert('HTTP Error: '+errorThrown);
                },

                success: _.bind(function(data)  {
                    this.handleResponse(data);
                }, this)
            });
        }
    },

    /**
     *
     */
    init: function() {
        if (!Backbone.history.started) {
            Backbone.history.start();
        }
        /**
         * Processing all links
         */
        this.processClicks(this.selector);
        /**
         * Processing all links in grid after grid load
         */
        OroApp.Events.bind(
            "grid_load:complete",
            function() {this.processClicks('.grid-container ' + this.selector)},
            this
        );
    },

    /**
     * Handling ajax response data. Updating content area with new content, processing title and js
     *
     * @param {String} data
     */
    handleResponse: function(data)
    {
        /**
         * todo: check the bug in firefox with page freezing and remove
         */
        document.getElementById('container').innerHTML = '';
        $('#container').html($(data).filter('#content').html());
        var js = '';
        $(data).filter('#head').find('script:not([src])').each(function() {
            js = js + this.outerHTML;
        })
        $('#container').append(js);
        $('title').html($(data).filter('#head').find('title').html());
        this.processClicks('#container ' + this.selector);
        this.triggerCompleteEvent();
    },

    /**
     * Trigger hash navigation complete event
     */
    triggerCompleteEvent: function() {
        /**
         * Backbone event. Fired when hash navigation ajax request is complete
         * @event hash_navigation_request:complete
         */
        OroApp.Events.trigger("hash_navigation_request:complete", this);
    },

    /**
     * Processing all links in selector and setting necessary click handler
     *
     * @param {String} selector
     */
    processClicks: function(selector) {
        $(selector).not('.no-hash').on('click', _.bind(function(e) {
            if (e.shiftKey || e.ctrlKey || e.metaKey || e.which == 2) {
                return true;
            }
            var target = e.currentTarget;
            e.preventDefault();
            var link = '';
            if ($(target).is('a')) {
                link = $(target).attr('href');
            } else if ($(target).is('span')) {
                link = $(target).attr('data-url');
            }
            link = link.replace(this.baseUrl, '').replace(/^(#\!?|\.)/, '');
            if (link) {
                window.location.hash = '#url=' + link;
            }
        }, this))
    },

    /**
     * Returns url part from the hash
     * @param {String} url
     * @return {String}
     */
    getHashUrl: function(url) {
        if (!url) {
            url = Backbone.history.fragment;
        }
        return OroApp.unpackFromQueryString(url).url;
    },

    back: function() {
        
    }
});