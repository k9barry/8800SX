<html>

<head>
    <link href="./jquery-upload-file/css/uploadfile.css" rel="stylesheet">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="./jquery-upload-file/js/jquery.uploadfile.min.js"></script>
</head>

<body>
    <div id="fileuploader">Upload</div>
    <script>
        $(document).ready(function() {
            $("#fileuploader").uploadFile({
                url: "./jquery-upload-file/php/upload.php",
                multiple: "true",
                returnType: "json",
                fileName: "myfile",
                upload_path: '../../uploads',
                allowedTypes: "txt",
                multiple:true,
                onLoad: function(obj) {
                    $("#eventsmessage").html($("#eventsmessage").html() + "<br/>Widget Loaded:");
                },
                onSubmit: function(files) {
                    $("#eventsmessage").html($("#eventsmessage").html() + "<br/>Submitting:" + JSON.stringify(files));
                    //return false;
                },
                onSuccess: function(files, data, xhr, pd) {
                    $("#eventsmessage").html($("#eventsmessage").html() + "<br/>Success for: " + JSON.stringify(data));
                },
                afterUploadAll: function(obj) {
                    $("#eventsmessage").html($("#eventsmessage").html() + "<br/>All files are uploaded");
                },
                onError: function(files, status, errMsg, pd) {
                    $("#eventsmessage").html($("#eventsmessage").html() + "<br/>Error for: " + JSON.stringify(files));
                },
                onCancel: function(files, pd) {
                    $("#eventsmessage").html($("#eventsmessage").html() + "<br/>Canceled  files: " + JSON.stringify(files));
                }
            });
        });
    </script>
</body>

</html>