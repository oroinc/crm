<?php /*
  * this is first prototype without jQuery ui Dialog
  *
 */ ?>
<?php include 'includes/head2.php'; ?>
<script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
<script src="js/main.js" type="text/javascript"></script>
<script>
    $(function() {
        $( "#dialog" ).dialog({
            autoOpen: false,
            resizable: true,
            minHeight: 200,
            minWidth: 350,
            position : {
                my : "center center",
                at : "center-5 center-5"
            },
            buttons: {
                "Minimize  me": function() {
                    var myDialogZ =  $(this);
                    var TemmpMe = $(this).parent('.ui-dialog').find('.ui-dialog-title').html();
                    var contentTemp = '<div class="ui-dialog-titlebar ui-dialog-titlebar-min"><span class="re-min">remin</span><span class="close-min">close</span> ' + TemmpMe + '</div>';
                    $(contentTemp).appendTo('#window-holder');
                    jQuery('.ui-dialog-titlebar-min').on('click', function(){
                        myDialogZ.dialog( "open" );
                        $(this).remove();
                    });
                   $( this ).dialog( "close" );
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            },
            close: function() {
                allFields.val( "" ).removeClass( "ui-state-error" );
            }

            });
        $( "#dialog2" ).dialog({
            autoOpen: false,
            resizable: true,
            position : {
                my : "center center",
                at : "center+15 center+15"
            },
            buttons: {
                "Minimize  me": function() {
                    var myDialogZ =  $(this);
                    var TemmpMe = $(this).parent('.ui-dialog').find('.ui-dialog-title').html();
                    var contentTemp = '<div class="ui-dialog-titlebar ui-dialog-titlebar-min"><span class="re-min">remin</span><span class="close-min">close</span> ' + TemmpMe + '</div>';
                    $(contentTemp).appendTo('#window-holder');
                    jQuery('.ui-dialog-titlebar-min').on('click', function(){
                        myDialogZ.dialog( "open" );
                        $(this).remove();
                    });
                    $( this ).dialog( "close" );
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            },
            close: function() {
                allFields.val( "" ).removeClass( "ui-state-error" );
            }
        });
        $( "#dialog3" ).dialog({
            autoOpen: false,
            resizable: true,
            position : {
                my : "center center",
                at : "center+60 center+120"
            }
        });
        $( "#opener1" ).click(function() {
            $( "#dialog" ).dialog( "open" );
            return false;
        });
        $( "#opener2" ).click(function() {
            $( "#dialog2" ).dialog( "open" );
            return false;
        })
        $( "#opener3" ).click(function() {
            $( "#dialog3" ).dialog( "open" );
            return false;
        });
    });
</script>
</head>
<body>
<div id="page">

    <div id="top-page">
        <?php include 'includes/header.php'; ?>
        <div class="container-fluid">
            <div class="alert test-box">
                <div class="btn-toolbar">
                   <div class="btn-group">
                       <button class="btn" id="opener1">Open window 1</button>
                       <button class="btn" id="opener2">Open window 2</button>
                       <button class="btn" id="opener3">Open window 3</button>
                   </div>
                </div>
                <div id="dialog" title="Basic dialog">
                    <p>This is the default dialog which is useful for displaying information. The dialog window can be moved, resized and closed with the 'x' icon.</p>
                </div>
                <div id="dialog2" title="Basic dialog2">
                    <p>you can try it.</p>
                </div>
                <div id="dialog3" title="Basic dialog3">
                    <p>you can try it. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce eget nisl lorem. Aliquam erat volutpat. Donec vel erat non tellus varius tempus sed eget tellus. Donec pharetra, ipsum non scelerisque euismod, massa mi dignissim velit, at feugiat nibh leo id mauris. Pellentesque massa risus, luctus sed commodo tempor, scelerisque sit amet nibh. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Suspendisse potenti. Phasellus rutrum augue ac diam elementum non lobortis sem viverra. Nullam vulputate sem vel mi commodo malesuada. Donec vitae neque purus.

                        Nulla facilisi. Fusce pretium, tortor eget pretium semper, enim ante imperdiet dolor, eget iaculis dui neque in sem. Nam convallis, ipsum vel lacinia faucibus, elit lacus semper velit, vitae porta dui arcu quis ligula. Nulla adipiscing tellus sit amet velit ultrices eget viverra odio commodo. Pellentesque sit amet convallis quam. Maecenas malesuada tincidunt nibh, at tempor urna euismod id. Curabitur id sem et leo iaculis fringilla. Sed pulvinar tortor ac ante iaculis porta. Maecenas felis quam, sollicitudin non elementum nec, porttitor vel justo. Nullam ullamcorper erat ut dui congue eu fermentum ante pharetra. Nam non consectetur purus. Nulla facilisi. Sed bibendum, eros non pharetra condimentum, quam velit cursus enim, non hendrerit purus eros dapibus mauris. Nam sagittis augue id dolor pulvinar nec venenatis velit luctus. Praesent quis lacus orci. </p>
                </div>
            </div>
        </div>
    </div>
 <?php include 'includes/footer.php'; ?>
</div>
<div id="window-holder"></div>
</body>
</html>