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
                autoSubmit: false,
                multiple: true,
                returnType: "json",
                fileName: "myfile",
                upload_path: '../../uploads',
                allowedTypes: "txt",
                multiple: true,
                onLoad: function(obj) {
                    $.ajax({
                        cache: false,
                        url: "./jquery-upload-file/php/load.php",
                        dataType: "json",
                        success: function(data) {
                            for (var i = 0; i < data.length; i++) {
                                obj.createProgress(data[i]);
                            }
                        }
                    });
                },
                onSelect: function(files) {
                    uploadObj.startUpload();
                    return true; //to allow file submission.
                },
                /*onSubmit: function(files) {
                    //files : List of files to be uploaded
                    //return false;   to stop upload
                    var_dump(files);
                    uploadObj.startUpload();
                    return true;
                },*/
                onSuccess: function(files, data, xhr, pd) {
                    $("#fileuploader").html($("#fileuploader").html() + "<br/>Success for: " + JSON.stringify(data));
                },
                afterUploadAll: function(obj) {
                    $("#fileuploader").html($("#fileuploader").html() + "<br/>All files are uploaded");
                    //function to check of file exists in db filename and file into database.
                },
                onError: function(files, status, errMsg, pd) {
                    $("#fileuploader").html($("#fileuploader").html() + "<br/>Error for: " + JSON.stringify(files));
                },
                onCancel: function(files, pd) {
                    $("#fileuploader").html($("#fileuploader").html() + "<br/>Canceled  files: " + JSON.stringify(files));
                }
            });
        });
    </script>
</body>

</html>