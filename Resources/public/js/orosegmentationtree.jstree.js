$(function () {

$("#tree")
    .jstree({
        // List of active plugins
        "plugins" : [
            "themes","json_data","ui","crrm","cookies","dnd","search","types","hotkeys","contextmenu"
        ],
        "themes" : {
            "dots" : true,
            "icons" : true,
            "themes" : "bap",
            "url" : assetsPath + "/css/style.css"
        },
        "json_data" : {
            "ajax" : {
                "url" : "children",
                // the `data` function is executed in the instance's scope
                // the parameter is the node being loaded
                // (may be -1, 0, or undefined when loading the root nodes)
                "data" : function (n) {
                    // the result is fed to the AJAX request `data` option
                    return {
                        "id" : n.attr ? n.attr("id").replace("node_","") : null
                    };
                }
            }
        },
        "search" : {
            "ajax" : {
                "url" : "search",
                "data" : function (str) {
                    return {
                        "search_str" : str
                    };
                }
            }
        },
        "types" : {
            "max_depth" : -2,
            "max_children" : -2,
            "valid_children" : [ "folder" ],
            "types" : {
                "default" : {
                    "valid_children" : "folder",
                    "icon" : {
                        "image" : assetsPath + "images/folder.png"
                    }
                },
                "folder" : {
                    "icon" : {
                        "image" : assetsPath + "images/folder.png"
                    }
                }
            }
        }
    })
    .bind("create.jstree", function (e, data) {
        $.post(
            "create-node",
            {
                "id" : data.rslt.parent.attr("id").replace("node_",""),
                "position" : data.rslt.position,
                "title" : data.rslt.name,
                "type" : data.rslt.obj.attr("rel")
            },
            function (r) {
                if(r.status) {
                    $(data.rslt.obj).attr("id", "node_" + r.id);
                }
                else {
                    $.jstree.rollback(data.rlbk);
                }
            }
        );
    })
    .bind("remove.jstree", function (e, data) {
        data.rslt.obj.each(function () {
            $.ajax({
                async : false,
                type: 'POST',
                url: "remove-node",
                data : {
                    "id" : this.id.replace("node_","")
                },
                success : function (r) {
                    if(!r.status) {
                        data.inst.refresh();
                    }
                }
            });
        });
    })
    .bind("rename.jstree", function (e, data) {
        $.post(
            "rename-node",
            {
                "id" : data.rslt.obj.attr("id").replace("node_",""),
                "title" : data.rslt.new_name
            },
            function (r) {
                if(!r.status) {
                    $.jstree.rollback(data.rlbk);
                }
            }
        );
    })
    .bind("move_node.jstree", function (e, data) {
        data.rslt.o.each(function (i) {
            $.ajax({
                async : false,
                type: 'POST',
                url: "move-node",
                data : {
                    "id" : $(this).attr("id").replace("node_",""),
                    "ref" : data.rslt.cr === -1 ? 1 : data.rslt.np.attr("id").replace("node_",""),
                    "position" : data.rslt.cp + i,
                    "title" : data.rslt.name,
                    "copy" : data.rslt.cy ? 1 : 0
                },
                success : function (r) {
                    if(!r.status) {
                        $.jstree.rollback(data.rlbk);
                    }
                    else {
                        $(data.rslt.oc).attr("id", "node_" + r.id);
                        if(data.rslt.cy && $(data.rslt.oc).children("UL").length) {
                            data.inst.refresh(data.inst._get_parent(data.rslt.oc));
                        }
                    }
                }
            });
        });
    })
});
