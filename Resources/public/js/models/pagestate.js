var Oro = Oro || {};

Oro.PageState = Oro.PageState || {};

Oro.PageState.Model = Backbone.Model.extend({
    defaults: {
        restore   : false,
        pagestate : {
            pageId : '',
            data   : {}
        }
    },

    initialize: function () {
        var self = this;

        if ($('form[data-collect=true]').length == 0) {
            return;
        }

        $.get(
            Routing.generate('oro_api_get_pagestate_checkid') + '?pageId=' + this.filterUrl(),
            function (data) {
                self.set({
                    id        : data.id,
                    pagestate : data.pagestate
                });

                if ( parseInt(data.id) > 0  && self.get('restore')) {
                    self.restore();
                }

                setInterval(function() {
                    self.collect();
                }, 2000);

                self.on('change:pagestate', function(model) {
                    self.save(self.get('pagestate'));
                });
            }
        )
    },

    collect: function() {
        var self = this;
        var data = {};

        $('form[data-collect=true]').each(function(index, el){
            data[index] = $(el)
                .find('input, textarea, select')
                .not(':input[type=button], :input[type=submit], :input[type=reset], :input[type=password], :input[type=file]')
                .serializeArray();
        });

        this.set({
            pagestate: {
                pageId : self.filterUrl(),
                data   : JSON.stringify(data)
            }
        });
    },

    restore: function() {
        $.each(JSON.parse(this.get('pagestate').data), function(index, el) {
            form = $('form[data-collect=true]').eq(index);
            form.find('option').prop('selected', false);

            $.each(el, function(i, input){
                element = form.find('[name="'+ input.name+'"]');
                switch (element.prop('type')) {
                    case 'checkbox':
                        element.filter('[value="'+ input.value +'"]').prop('checked', true);
                        break;
                    case 'select-multiple':
                        element.find('option[value="'+ input.value +'"]').prop('selected', true);
                        break;
                    default:
                        element.val(input.value);
                }
            });
        });
    },

    filterUrl: function() {
        self = this;

        params = window.location.search.replace('?', '').split('&');

        if (params.length == 1 && params[0].indexOf('restore') !== -1) {
            self.set('restore', true);
        } else {
            params = $.grep(params, function(el) {
                if (el.indexOf('restore') == -1) {
                    return true;
                } else {
                    self.set('restore', true);
                    return false;
                }
            })
        }

        return base64_encode(window.location.pathname + '?' + params.join('&'));
    },

    url: function(method) {
        return this.id
            ? Routing.generate('oro_api_put_pagestate', { id: this.id })
            : Routing.generate('oro_api_post_pagestate');
    }
});

$(function() {
    Oro.pagestate = new Oro.PageState.Model();
})
