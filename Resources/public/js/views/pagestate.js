var Oro = Oro || {};

Oro.PageState = Oro.PageState || {};

Oro.PageState.View = Backbone.View.extend({
    initialize: function () {
        var self = this;

        if (Backbone.$('form[data-collect=true]').length == 0) {
            return;
        }

        Backbone.$.get(
            Routing.generate('oro_api_get_pagestate_checkid') + '?pageId=' + this.filterUrl(),
            function (data) {
                self.model.set({
                    id        : data.id,
                    pagestate : data.pagestate
                });

                if ( parseInt(data.id) > 0  && self.model.get('restore')) {
                    self.restore();
                }

                setInterval(function() {
                    self.collect();
                }, 2000);

                self.model.on('change:pagestate', function(model) {
                    self.model.save(self.model.get('pagestate'));
                });
            }
        )
    },

    collect: function() {
        var self = this;
        var data = {};

        Backbone.$('form[data-collect=true]').each(function(index, el){
            data[index] = Backbone.$(el)
                .find('input, textarea, select')
                .not(':input[type=button], :input[type=submit], :input[type=reset], :input[type=password], :input[type=file]')
                .serializeArray();
        });

        this.model.set({
            pagestate: {
                pageId : self.filterUrl(),
                data   : JSON.stringify(data)
            }
        });
    },

    restore: function() {
        Backbone.$.each(JSON.parse(this.model.get('pagestate').data), function(index, el) {
            form = Backbone.$('form[data-collect=true]').eq(index);
            form.find('option').prop('selected', false);

            Backbone.$.each(el, function(i, input){
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
            self.model.set('restore', true);
        } else {
            params = Backbone.$.grep(params, function(el) {
                if (el.indexOf('restore') == -1) {
                    return true;
                } else {
                    self.model.set('restore', true);
                    return false;
                }
            })
        }

        return base64_encode(window.location.pathname + '?' + params.join('&'));
    }
});
