/**
 * Allow to select on which tree to work and manage creation and deletion of trees
 * File: jstree.tree_selector.js
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT  MIT
 *
 * Right now needs the following configuration on the json_data plugin, in order
 * to have a working root node which text and id will be replaced by the data from
 * the selected tree.
 *  "json_data" : {
 *      "data" : [
 *          {
 *              "data": "Loading root...",
 *              "state": "closed",
 *              "attr" : { "id" : "node_1"}
 *          }
 *      ],
 *      "ajax" : {
 *          "url" : "children",
 *          "data" : function (node) {
 *              return {
 *                  "id" : node.attr("id").replace('node_','')
 *              };
 *          }
 *      }
 *  }
 */

/* Group: jstree tree_selector plugin */
(function ($) {
    var tree_select_id = "tree_select";

    $.jstree.plugin("tree_selector", {
        __init : function () {
            this.get_container()
                // Create the tree toolbar and load trees in tree selector
                .bind("init.jstree", $.proxy(function () {
                    var settings = this._get_settings().tree_selector;
                    this.data.tree_selector.ajax = settings.ajax;
                    this.data.tree_selector.data = settings.data;
                    this.data.tree_selector.auto_open_root = settings.auto_open_root;

                    var tree_toolbar = $('<div>', {
                        id: 'tree_toolbar',
                        class: 'jstree-tree-toolbar'
                    });
                    var tree_select = $('<select>', {
                        id: tree_select_id,
                        class: 'jstree-tree-select'
                    });

                    var this_jstree = this;
                    tree_select.bind('change', function() {
                        this_jstree.switch_tree();
                    });

                    tree_toolbar.html(tree_select);
                    this.get_container_ul().before(tree_toolbar);

                    this.load_trees();
                }, this))
                // Rewrite the root node to link it to the selected tree
                .bind("loaded.jstree", $.proxy(function () {
                    this.switch_tree();


                }, this))
                .bind('refresh.jstree', $.proxy(function (e, data) {
                    this.load_trees();
                    this.switch_tree();
                }, this));
        },
        defaults : {
            ajax : false,
            data : false,
            tree_selector_buttons : false
        },
        _fn : {
            refresh : function(obj) {
                this.switch_tree();

                return this.__call_old();
            },
            switch_tree : function () {
                root_node = this.get_container_ul().find('li')[0];

                // Apply new tree id and new tree text to the root node
                var selected_tree = this.get_tree_select().find(':selected');
                root_node.id = $(selected_tree).attr('id');
                this.set_text(root_node,selected_tree.text());

                // Cleanup the tree
                $(root_node).children('ul').remove();
                this.close_node(root_node);
                this.clean_node(root_node);

                // Make the node "openable" by switching back to initial state
                $(root_node).removeClass('jstree-leaf');
                $(root_node).addClass('jstree-close');
                $(root_node).addClass('jstree-closed');

                if (this.data.tree_selector.auto_open_root) {
                    this.open_node(root_node);
                }
            },
            get_tree_select : function () {
                return $("#" + tree_select_id);
            },
            load_trees: function () {
                var trees;

                if (this.data.tree_selector.data) {
                    trees = this._load_data_trees();
                } else if (this.data.tree_selector.ajax) {
                    trees = this._load_ajax_trees();
                } else {
                    throw "jquery.jstree.tree_selector : Neither data nor ajax settings supplied for trees.";
                }

                var default_selected = null;

                var this_jstree = this;
                $.each(trees, function (index, tree) {
                    var option = $('<option>', {
                        id: tree.id,
                        text: tree.title
                    });
                    if (index === 0) {
                        option.prop('defaultSelected', true);
                    }
                    this_jstree.get_tree_select().append(option);
                });

                this.get_container().trigger('trees_loaded.jstree', tree_select_id);
            },
            _load_data_trees: function () {
                var trees_data = this.data.tree_selector.data;

                return $.parseJSON(trees_data);
            },
            _load_ajax_trees: function () {
                var trees_url = this.data.tree_selector.ajax.url;
                var trees = [];

                $.ajax({
                    url: trees_url,
                    async: false,
                    dataType: 'json'
                }).done( function(ajax_trees) {
                    trees = ajax_trees;
                });

                return trees;

            },
            refresh_trees: function() {
                this.get_tree_select().empty();
                this.load_trees();
                this.switch_tree();
            },
            get_tree_id: function() {
                root_node = this.get_container_ul().find('li')[0];

                return $(root_node).attr('id');

            }
        }
    });
    // include the tree_selector plugin by default on available plugins list
    $.jstree.defaults.plugins.push("tree_selector");
})(jQuery);
