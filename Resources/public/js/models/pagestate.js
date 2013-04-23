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
                }, 5000);

                self.on('change:pagestate', function(model) {
                    self.save();
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

            $.each(el, function(i, input){
                form.find('[name="'+ input.name+'"]').val(input.value);
            });
        });
    },

    filterUrl: function() {
        var self = this;
        var href = document.location.href;
        var base = href.substr(0, location.href.indexOf('?'));
        var params = href.replace(base + '?', '');

        params = params.split('&');
        if (params.length == 1 && params[0].indexOf('restore') !== -1) {
            self.set('restore', true);
        } else {
            base += '?';
            $(params).each(function(index, el) {
                if (el.indexOf('restore') == -1) {
                    base += el;
                } else {
                    self.set('restore', true);
                }
            })
        }

        return base64_encode(base);
    },

    url: function() {
        //return '/app_dev.php/api/rest/latest/pagestate' + ((this.id) ? 's/'+this.id : '');
        return this.id
            ? Routing.generate('oro_api_get_pagestate', { id: this.id })
            : Routing.generate('oro_api_get_pagestates');
    }
});

Oro.pagestate = new Oro.PageState.Model();
