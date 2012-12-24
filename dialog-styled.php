<?php include 'includes/head2.php'; ?>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery.dialogextend.1_0_1.js"></script>
<body>
<?php include 'includes/header.php'; ?>
<div class="container-fluid">
    <div class="row-fluid">
        <div class="container-fluid">
            <button type="button" id="my-button" class="btn btn-large btn-primary">Large button</button>
            <div class="test-dialog-content" style="display: none;">
                <p>big button</p>
                <p> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque vitae eleifend magna. Mauris condimentum posuere nisi convallis faucibus. Nulla facilisi. Suspendisse ut suscipit lacus. Sed a libero ipsum, sit amet interdum lectus. Curabitur sit amet dolor at mi cursus scelerisque consequat viverra urna.</p>
                <p>. Aliquam nec nulla nunc, ac malesuada lectus. Aliquam aliquam, sapien eget bibendum ullamcorper, lectus elit pellentesque mauris, ac laoreet diam magna quis nisi. Praesent tincidunt euismod malesuada. Quisque nec lobortis leo. Aliquam id nisl lectus, eu rhoncus lorem.</p>
                <p>Aliquam a turpis at turpis bibendum vulputate eu ac erat. Sed id mauris ante. Proin eget elit lorem, eu elementum dui. Ut ac augue vel lectus tempus auctor at eget quam. Donec a tincidunt nisi. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. In facilisis, velit quis ornare luctus, tortor lectus ullamcorper ante, eu tincidunt leo lectus nec elit. Donec quis dolor a quam blandit adipiscing. </p>
            </div>
            <hr />
            <div class="btn-toolbar">
                <div class="btn-group">
                    <button id="opener1" class="btn">Open window 1</button>
                    <button id="opener2" class="btn">Open window 2</button>
                    <button id="opener3" class="btn">Open window 3</button>
                </div>
            </div>
            <div id="opener-content1" style="display: none;">
                <p>v quam eu pellentesque. Nam euismod, lectus sit amet tristique imperdiet, metus ligula gravida felis, non dapibus nulla sem a magna.</p>
            </div>
            <div id="opener-content2" style="display: none;">
                <p>Fusce hendrerit lacinia ligula.</p>
                <p>Donec a tincidunt nisi.</p>
                <p> Cras eu velit sed nibh feugiat congue lacinia ut urna. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos.</p>
            </div>
            <div id="opener-content3" style="display: none;">
                <p>v quam eu pellentesque. Nam euismod, lectus sit amet tristique imperdiet, metus ligula gravida felis, non dapibus nulla sem a magna.</p>
                <p>habitasse platea dictumst. Donec elementum vulputate imperdiet. Fusce sed lectus odio. Etiam cursus fermentum ornare. Phasellus facilisis t</p>
                <p> Quisque nec lobortis leo.</p>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        var positionNextWindow = 5;
        function newPositionWindow(){
            positionNextWindow = positionNextWindow +15;
            var needPosition = 'center+' + positionNextWindow + ' center+' + positionNextWindow;
            return needPosition;
        }
        var isOpen;
        $("#my-button").click(function(){
            try{
                isOpen = $('.test-dialog-content').dialog("isOpen");
            } catch(e) {
                isOpen = false;
            }
            if (isOpen === false ){
                var offsetwindow = newPositionWindow();
                var dialogOptions = {
                    title : "dialog window",
                    width : 400,
                    height : 300,
                    position: {
                        my: offsetwindow,
                        at: "center center",
                        of: $('body')
                    },
                    "close" : function(){ $(this).dialog( "destroy" ) }
                };
                var dialogExtendOptions = {
                    "maximize" : true,
                    "minimize" : true
                };

                $(".test-dialog-content").dialog(dialogOptions).dialogExtend(dialogExtendOptions);
            }
        });
        $("#opener1").click(function(){
            try{
                isOpen = $('#opener-content1').dialog("isOpen");
            } catch(e) {
                isOpen = false;
            }
            if (isOpen === false ){
                var offsetwindow = newPositionWindow();
                var dialogOptions = {
                    title : "dialog window",
                    width : 400,
                    height : 300,
                    position: {
                        my: offsetwindow,
                        at: "center center",
                        of: $('body')
                    },
                    "close" : function(){ $(this).dialog( "destroy" ) }
                };
                var dialogExtendOptions = {
                    "maximize" : true,
                    "minimize" : true
                };

                $("#opener-content1").dialog(dialogOptions).dialogExtend(dialogExtendOptions);
            }
        });
        $("#opener2").click(function(){
            try{
                isOpen = $('#opener-content2').dialog("isOpen");
            } catch(e) {
                isOpen = false;
            }
            if (isOpen === false ){
                var offsetwindow = newPositionWindow();
                var dialogOptions = {
                    title : "dialog window",
                    width : 400,
                    height : 300,
                    position: {
                        my: offsetwindow,
                        at: "center center",
                        of: $('body')
                    },
                    "close" : function(){ $(this).dialog( "destroy" ) }
                };
                var dialogExtendOptions = {
                    "maximize" : true,
                    "minimize" : true
                };

                $("#opener-content2").dialog(dialogOptions).dialogExtend(dialogExtendOptions);
            }
        });
        $("#opener3").click(function(){
            try{
                isOpen = $('#opener-content3').dialog("isOpen");
            } catch(e) {
                isOpen = false;
            }
            if (isOpen === false ){
                var offsetwindow = newPositionWindow();
                var dialogOptions = {
                    title : "dialog window",
                    width : 400,
                    height : 300,
                    position: {
                        my: offsetwindow,
                        at: "center center",
                        of: $('body')
                    },
                    "close" : function(){ $(this).dialog( "destroy" ) }
                };
                var dialogExtendOptions = {
                    "maximize" : true,
                    "minimize" : true
                };
                $("#opener-content3").dialog(dialogOptions).dialogExtend(dialogExtendOptions);
            }
        });
    });
</script>
<?php /* include 'includes/footer.php'; */ ?>
</body>
</html>