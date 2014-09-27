<?php

class HistXML {
    public $bytes = 0;
    public $fileName = "";
    public $contents = "";

    public function __construct($xml, $fileName)
    {
        $this->fileName = $fileName;
        $this->contents = $xml;
    }
}

// Check for file input
$noInput = !isset($_FILES["xml"]);

if( !$noInput ) {
if( $_FILES["xml"]["error"] )
{
    define("FILE_ERROR", $_FILES["xml"]["error"]);
}
else
{
    $histXML = new HistXML(file_get_contents($_FILES["xml"]["tmp_name"]),
                                             $_FILES["xml"]["name"]);
}}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Messenger XML2HTML</title>
        <link href="bootstrap.min.css" rel="stylesheet" type="text/css">
        <style>
        .input-form {
            margin-top: 20px;
        }
        .conversation {
            overflow: auto;
        }
        .no-input {
            margin-top: 18em;
        }
        .no-input .title, .no-input .input-form {
            text-align: center;
            margin: 50px auto;
            display: block;
            float: none;
        }
        .no-input .input-form .submit-col {
            text-align: left;
        }
        </style>
    </head>
    <body<?php if( $noInput ) echo " class='no-input'"; ?>>
        <div class="content">
            <div class="title col-md-6">
                <h1>Messenger XML2HTML</h1>
                <p>Read your old Messenger conversation history in XML and
                remember the good old times!</p>
            </div>
            <div class="input-form col-md-6">
                <form class="form-horizontal" role="form" action="." method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="file-input" class="col-sm-4 control-label">
                        Load XML</label>
                        <div class="col-sm-8">
                            <input type="file" style="width: 100%" id="file-input" name="xml">
                        </div>
                        <div class="col-sm-offset-4 col-sm-8 submit-col">
                            <input type="submit" class="btn btn-default">
                        </div>
                    </div>
                </form>
            </div>
            <div class='clearfix'></div>
        </div>
        <div class="content">
            <div class="col-md-12">
<?php if( defined("FILE_ERROR") ) { ?>
                <div class="alert alert-danger" role="alert">
                    <strong>Woops!</strong>
<?php

switch( FILE_ERROR ) {
case UPLOAD_ERR_INI_SIZE:
case UPLOAD_ERR_FORM_SIZE:
    echo "The file size is too big!";
    break;
case UPLOAD_ERR_PARTIAL:
    echo "The file wasn't uploaded completely!";
    break;
case UPLOAD_ERR_NO_FILE:
    echo "No file was uploaded!";
    break;
default:
    echo "There was an error in the uploaded file.";
}

?>
                </div>
<?php } else if( !$noInput ) {
    $fileSize = round($_FILES["xml"]["size"]);
    echo "<h4 class='text-center'>Remembering $histXML->fileName - " .
         "$histXML->bytes bytes of history!</h4>";
    echo "<hr>";
    echo "<div class='conversation'>$histXML->contents</div>";
} ?>
            </div>
        </div>
    </body>
</html>
