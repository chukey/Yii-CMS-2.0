<?php
$url = $this->createUrl('/main/helpAdmin/render');
$this->Widget('ext.jsTree.CjsTree', array(
    'id'        => 'firstTree',

    'json_data' => array(
        'ajax' => array(
            'method'=> 'GET',
            'url'   => $url,
            'data'  => 'js:function (n, a) {
                return {
                    "operation" : "get_children",
                    "id" : n != -1 ? n.attr("id").replace("node_","") : 0
                };
            }'
        ),
    ),
    'themes'    => array('theme'=> 'default'),
    'rules'     => array(
        'droppable' => "tree-drop",
        'multiple'  => true,
        'deletable' => "all",
        'draggable' => "all"
    ),
    'plugins'   => array(
        "themes", "json_data", "ui", "crrm", "dnd", "search", "types", "hotkeys", "contextmenu"
    ),
    "types" => array(
        // I set both options to -2, as I do not need depth and children count checking
        // Those two checks may slow jstree a lot, so use only when needed
        "max_depth" => -2,
        "max_children" => -2,
        // I want only `drive` nodes to be root nodes
        // This will prevent moving or creating any other type as a root node
        "valid_children" => array( "drive" ),
        "types" => array(
            // The default type
            "default" => array(
                // I want this type to have no children (so only leaf nodes)
                // In my case - those are files
                "valid_children" => "none",
                // If we specify an icon for the default type it WILL OVERRIDE the theme icons
                "icon" => array(
                    "image" => "/static/v.1.0pre/_demo/file.png"
                )
            ),
            // The `folder` type
            "folder" => array(
                // can have files and other folders inside of it, but NOT `drive` nodes
                "valid_children" => array( "default", "folder" ),
                "icon" => array(
                    "image" => "/static/v.1.0pre/_demo/folder.png"
                )
            ),
            // The `drive` nodes
            "drive" => array(
                // can have files and folders inside, but NOT other `drive` nodes
                "valid_children" => array ( "default", "folder" ),
                "icon" => array(
                    "image" => "/static/v.1.0pre/_demo/root.png"
                ),
                // those prevent the functions with the same name to be used on `drive` nodes
                // internally the `before` event is used
                "start_drag" => false,
                "move_node" => false,
                "delete_node" => false,
                "remove" => false
            )
        )
    ),
    "search"    => array(
        // As this has been a common question - async search
        // Same as above - the `ajax` config option is actually jQuery's AJAX object
        "ajax" => array(
            "url"  => $this->createUrl('/main/helpAdmin/render'),
            // You get the search string as a parameter
            "data" => 'js:function (str) {
                return {
                    "operation" : "search",
                    "search_str" : str
                };
            }'
        )
    ),
    'callback'  => array(
        'rename'    => 'js:function( e, data)  {
             $.get("' . $url . '", {
                    "operation" : "rename_node",
                    "id" : data.rslt.obj.attr("id").replace("node_",""),
                    "title" : data.rslt.new_name
                },
                function (r) {
                    if(!r.status) {
                        $.jstree.rollback(data.rlbk);
                    }
                }
            );
        }',
        'move_node' => 'function (e, data)
        {
            data.rslt.o.each(function (i) {
                $.get({
                    url: "' . $url . '",
                    data : {
                        "operation" : "move_node",
                        "id" : $(this).attr("id").replace("node_",""),
                        "ref" : data.rslt.cr === -1 ? 1 : data.rslt.np.attr("id").replace("node_",""),
                        "position" : data.rslt.cp + i,
                        "title" : data.rslt.name,
                        "copy" : data.rslt.cy ? 1 : 0
                    },
                    success : function (r)
                    {
                        if(!r.status)
                        {
                            $.jstree.rollback(data.rlbk);
                        }
                        else
                        {
                            $(data.rslt.oc).attr("id", "node_" + r.id);
                            if(data.rslt.cy && $(data.rslt.oc).children("UL").length) {
                                data.inst.refresh(data.inst._get_parent(data.rslt.oc));
                            }
                        }
                    }
                });
            });
        }',
        "create"    => 'function (e, data) {
            $.post(
                "' . $url . '",
                {
                    "operation" : "create_node",
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
        })',
        "remove"    => 'function (e, data) {
            data.rslt.obj.each(function () {
                $.get({
                    url: "' . $url . '",
                    data : {
                        "operation" : "remove_node",
                        "id" : this.id.replace("node_","")
                    },
                    success : function (r) {
                        if(!r.status) {
                            data.inst.refresh();
                        }
                    }
                });
            });
        })'
    ),
));
