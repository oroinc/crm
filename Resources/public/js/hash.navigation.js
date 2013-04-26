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
    selector: 'a:not([href^=#],[href^=javascript]),span[data-url]',

    /**
     * Selector for forms
     *
     * @property {String}
     * */
    formSelector: "form",

    formData: "",
    target: "",

    method: "get",

    /** @property {String} */
    baseUrl: '',

    /**
     * State data for grids
     *
     * @property
     */
    encodedStateData: '',

    /**
     * Url part
     *
     * @property
     */
    url: '',

    /** @property {OroApp.DatagridRouter} */
    gridRoute: '',

    /** @property */
    routes: {
        "url=*page(|g/*encodedStateData)": "defaultAction",
        "g/*encodedStateData": "gridChangeStateAction"
    },

    /**
     * Routing default action
     *
     * @param {String} page
     * @param {String} encodedStateData
     */
    defaultAction: function (page, encodedStateData) {
        this.encodedStateData = encodedStateData;
        this.url = page;
        this.loadPage(this.url);
    },

    /**
     * Routing grid state changed action
     *
     * @param encodedStateData
     */
    gridChangeStateAction: function (encodedStateData) {
        this.encodedStateData = encodedStateData;
    },

    /**
     *  Changing state for grid
     */
    gridChangeState: function () {
        if (this.gridRoute) {
            this.gridRoute.changeState(this.encodedStateData);
        }
    },

    /**
     * Initialaize hash navigation
     *
     * @param options
     */
    initialize: function (options) {
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
    setActiveMenu: function (url) {
        $('.application-menu a').parents('li').removeClass('active');
        var li = $('.application-menu a[href="' + url + '"]').parents('li');
        li.addClass('active');
        var tabId = li.parents('.tab-pane').attr('id');
        $('.application-menu a[href=#' + tabId + ']').tab('show');
    },

    /**
     * Ajax call for loading page content
     */
    loadPage: function () {
        if (this.url) {
            this.gridRoute = ''; //clearing grid router
            var pageUrl = this.baseUrl + this.url;
            if (this.method == 'get') {
                $.ajax({
                    type: this.method,
                    url: pageUrl,
                    data: this.formData,
                    headers: { 'x-oro-hash-navigation': true },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        alert('Error Message: ' + textStatus);
                        alert('HTTP Error: ' + errorThrown);
                    },

                    success: _.bind(function (data) {
                        this.handleResponse(data);
                        this.setActiveMenu(this.url);
                    }, this)
                });
            } else {
                $.ajaxSettings.beforeSend = function (xhr) {
                    xhr.setRequestHeader('x-oro-hash-navigation', {toString: function () {
                        return true;
                    }});
                };
                $(this.target).ajaxSubmit({
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        alert('Error Message: ' + textStatus);
                        alert('HTTP Error: ' + errorThrown);
                    },
                    success: _.bind(function (data) {
                        this.handleResponse(data);
                        this.setActiveMenu(this.url);
                    }, this)
                });
            }

            $.ajaxSettings.beforeSend = function (xhr) {
                xhr.setRequestHeader('X-Requested-With', {toString: function () {
                    return 'XMLHttpRequest';
                }});
            };
        }
    },

    /**
     *
     */
    init: function () {
        /**
         * Processing all links
         */
        this.processClicks(this.selector);
        /**
         * Processing all links in grid after grid load
         */
        OroApp.Events.bind(
            "grid_load:complete",
            function () {
                this.processClicks('.grid-container ' + this.selector)
            },
            this
        );
        /**
         * Checking for grid route and updating it's state
         */
        OroApp.Events.bind(
            "grid_route:loaded",
            function (route) {
                this.gridRoute = route;
                this.gridChangeState();
            },
            this
        );
        /**
         * Processing links in 3 dots menu after item is added (e.g. favourites)
         */
        OroApp.Events.bind(
            "navigaion_item:added",
            function (item) {
                this.processClicks(item.find(this.selector));
            },
            this
        );
        this.processSend(this.formSelector);
    },

    /**
     * Handling ajax response data. Updating content area with new content, processing title and js
     *
     * @param {String} data
     */
    handleResponse: function (data) {
        /**
         * Clearing content area with native js, prevents freezing of firefox with firebug enabled
         */
        document.getElementById('container').innerHTML = '';
        var redirectUrl = $(data).filter('#redirect').html();
        if (redirectUrl) {
            this.method = 'get';
            this.formData = '';
            redirectUrl = redirectUrl.replace(this.baseUrl, '').replace(/^(#\!?|\.)/, '');
            urlParts = redirectUrl.split('url=');
            if (urlParts[1]) {
                redirectUrl = urlParts[1];
            }
            this.setLocation(redirectUrl);
        } else {
            $('#container').html($(data).filter('#content').html());
            var js = '';
            $(data).filter('#head').find('script:not([src])').each(function () {
                js = js + this.outerHTML;
            })
            $('#container').append(js);

            /**
             * Setting page title
             */
            $('title').html($(data).filter('#head').find('title').html());
            /**
             * Setting serialized titles for pinbar and favourites buttons
             */
            var titleSerialized = $(data).filter('#head').find('#title-serialized').html();
            titleSerialized = $.parseJSON(titleSerialized);
            $('.top-action-box .btn').filter('.minimize-button, .favorite-button').data('title', titleSerialized);


            this.processClicks('#container ' + this.selector);
            this.processSend('#container ' + this.formSelector);
            this.updateMenuTabs(data);
            this.updateMessages(data);
            this.triggerCompleteEvent();
        }
    },

    updateMessages: function(data) {
        $('#flash-messages').html($(data).filter('#messages').html());
    },

    /**
     * Update History and Most Viewed menu tabs
     *
     * @param data
     */
    updateMenuTabs: function (data) {
        $('#history-content').html($(data).filter('#history-content').html());
        $('#most-viewed-content').html($(data).filter('#most-viewed-content').html());
        /**
         * Processing links for history and most viewed tabs
         */
        this.processClicks('#history-content ' + this.selector + ', #most-viewed-content ' + this.selector);
    },

    /**
     * Trigger hash navigation complete event
     */
    triggerCompleteEvent: function () {
        /**
         * Backbone event. Fired when hash navigation ajax request is complete
         * @event hash_navigation_request:complete
         */
        OroApp.Events.trigger("hash_navigation_request:complete", this);
    },

    /**
     * Processing all links in selector and setting necessary click handler
     * links with "no-hash" class are not processed
     *
     * @param {String} selector
     */
    processClicks: function (selector) {
        $(selector).not('.no-hash').on('click', _.bind(function (e) {
            if (e.shiftKey || e.ctrlKey || e.metaKey || e.which == 2) {
                return true;
            }
            var target = e.currentTarget;
            e.preventDefault();
            var link = '';
            if ($(target).is('a')) {
                link = $(target).attr('href');
                if ($(target).hasClass('back')) {
                    //if back link is found, go back and don't do further processing
                    if (this.back()) {
                        return false;
                    }
                }
            } else if ($(target).is('span')) {
                link = $(target).attr('data-url');
            }
            link = link.replace(this.baseUrl, '').replace(/^(#\!?|\.)/, '');
            if (link) {
                this.setLocation(link);
            }
            return false;
        }, this));
    },

    /**
     * Processing all links in selector and setting necessary click handler
     *
     * @param {String} selector
     */
    processSend: function (selector) {
        $(selector).on('submit', _.bind(function (e) {
            var target = e.currentTarget;
            e.preventDefault();
            var link = '';

            link = $(target).attr('action');

            link = link.replace(this.baseUrl, '').replace(/^(#\!?|\.)/, '');

            this.method = $(target).attr('method');
            if (link) {
                url = link;
                var data = $(target).serialize();
                if (this.method == 'get') {
                    url += '?' + data;
                    this.target = '';
                } else {
                    this.formData = data;
                    this.target = target;
                }
                window.location.hash = '#url=' + url;
            }
            $.ajaxSettings.beforeSend = function (xhr) {
                xhr.setRequestHeader('X-Requested-With', {toString: function () {
                    return '';
                }});
            };

            return false;
        }, this))
    },

    /**
     * Returns real url part from the hash
     * @return {String}
     */
    getHashUrl: function () {
        var url = this.url;
        if (!url) {
            /**
             * Get real url part from the hash without grid state
             */
            url = Backbone.history.fragment.split('|g/')[0].replace('url=', '');
            if (!url) {
                url = window.location.pathname + window.location.search;
            }
        }
        return url;
    },

    /**
     * Change location hash with new url
     *
     * @param {String} url
     */
    setLocation: function (url) {
        window.location.hash = '#url=' + url;
    },

    /**
     * Processing back clicks. If we have back attribute in url, use it, otherwise using browser back
     *
     * @return {Boolean}
     */
    back: function () {
        var backFound = false;
        var url = new Url(this.getHashUrl());
        if (url.query.back) {
            window.location = url.query.back;
            backFound = true;
        } else {
            window.history.back();
            backFound = true;
        }
        return backFound;
    }
});