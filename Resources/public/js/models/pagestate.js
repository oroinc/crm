console.log('pagestate model loaded');

var Oro = Oro || {};

Oro.PageState = Oro.PageState || {};

Oro.PageState.Model = Backbone.Model.extend({
    defaults: {
        id: 4, // DB entityId
        //urlRoot: '/api/rest/latest/pagestate', // REST URI -> /api/rest/{version}/pagestates/{id}
        //userId: null, // not sure we'll really need this, but let it stay here for a while
        //pageId: '', // we should somehow identify current page
        formData: {},
        createdAt: new Date(),
        updatedAt: new Date()
    },

    initialize: function () {
        var self = this;

        console.log('Model init');

        var timer = setInterval(function () {
            self.collect();
        }, 5000);

        this.on("change:formData", function(model){
            console.log( '--- data has been changed ---' );
            console.log( this.get('formData') );

            console.log('--- and saved/updated ---');
            this.save();
        });
    },

    collect: function () {
        console.log('data collecting...'+ new Date());
        var data = {};
        //$('form[data-collect=true]').each(function(index, el){
        $('form').each(function(index, el){
            data[index] = $(el)
                .find('input, textarea, select')
                .not(':input[type=button], :input[type=submit], :input[type=reset]')
                .serializeArray();
        });

        this.set({
            formData: data,
            updatedAt: new Date()
        });
    },

    url: function() {
//        var base = _.result(this, 'urlRoot') || _.result(this.collection, 'url') || urlError();
//        base +=  (base.charAt(base.length - 1) === '/' ? '' : '/') + this.get('type');
//        if (this.isNew()) {
//            return base;
//        }
//        return base + (base.charAt(base.length - 1) === '/' ? '' : '/') + 'ids/' + encodeURIComponent(this.id);

        //return this.get('urlRoot')+'/'+this.get('id');
        return '/api/rest/latest/pagestate' + ((this.id) ? 's/id/'+this.id : '');
    }

//    sync: function () {
//        console.log('try sync');
//    }

});

var pageState = new Oro.PageState.Model();

// Or we can set the `id` of the model
// var pageState = new Oro.PageState.Model({id: 1});
