<?php

define("UPLOAD_ERR_BADXML", 42220);

class HistXML {
    public $msgCount = 0;
    public $fileName = "";
    public $contents = "";

    public function __construct($xml, $fileName)
    {
        $this->fileName = $fileName;
        $this->contents = $this->XML2HTML($xml);
    }

    public function XML2HTML($xml)
    {
        $xml = @simplexml_load_string($xml);
        if( $xml === false ) return false;

        $html = "<dl class='dl-horizontal'>";

        $firstDate = true;
        $lastDate  = null;
        $lastTime  = null;
        $lastFrom  = null;

        try { foreach($xml->Message as $msg)
        {
            // Get variables
            $from = $msg->From->children()->User["FriendlyName"]->__toString();
            $date = $msg->attributes()["Date"]->__toString();
            $time = $msg->attributes()["Time"]->__toString();
            $message   = $msg->Text->__toString();
            $from = htmlspecialchars($from);
            $date = htmlspecialchars($date);
            $time = htmlspecialchars($time);
            $message = htmlspecialchars($message);

            $fontStyle = "font-family: Arial, sans-serif; font-size: 10pt; color: black";
            if( isset($msg->Text->attributes()["Style"]) )
                $fontStyle = $msg->Text->attributes()["Style"]->__toString();

            // Remove seconds from time
            $time = preg_replace("/:\d\d$/", "", $time);

            // Make glorious first date title
            if( $firstDate ) {
                $html .= "<h1 class='first-date'>$date</h1>";
                $lastDate = $date;
            }

            // Print date
            if( $date != $lastDate ) {
                $html .= "<dt class='date'>$date</dt>";
            }

            // Print user name
            if( $from != $lastFrom || $time != $lastTime || $date != $lastDate ) {
                $html .= "<dd class='username'>";
                $html .= "$from says ($time):";
                $html .= "</dd>";
            }

            // Print message
            $html .= "<dd class='message' style='$fontStyle'>";
            $html .= $message;
            $html .= "</dd>";

            // Increment msgCount
            $this->msgCount++;
            $firstDate = false;
            $lastDate = $date;
            $lastTime = $time;
            $lastFrom = $from;
        }}
        catch(Exception $err) {
            return false;
        }

        $html .= "</dl>";
        return $html;
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
    if( $histXML->contents === false )
    {
        define("FILE_ERROR", UPLOAD_ERR_BADXML);
    }
}}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>MSN Messenger XML2HTML</title>
        <link href="bootstrap.min.css" rel="stylesheet" type="text/css">
        <style>
        .input-form {
            margin-top: 20px;
        }
        .conversation {
            word-break: break-all;
        }
        .conversation .first-date {
            text-align: center;
            font-size: 7em;
            margin: 0.95em 0;
        }
        .conversation .username, .conversation .date {
            color: #B1B1B1;
        }
        .conversation .date {
            font-weight: normal;
        }
        .conversation .message {
            padding-left: 20px;
            position: relative;
        }
        .conversation .message:before {
            content: '';
            position: absolute;
            width: 3px; height: 3px;
            border-radius: 100%;
            background-color: #B1B1B1;
            top: 9px; left: 7px;
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
            <div class="title col-md-4 col-md-offset-2">
                <h1>MSN Messenger XML2HTML</h1>
                <p>Read your old Messenger conversation history in XML and
                remember the good old times!</p>
            </div>
            <div class="input-form col-md-4">
                <form class="form-horizontal" role="form" action="."
                        method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="file-input" class="col-sm-4 control-label">
                        Load XML</label>
                        <div class="col-sm-8">
                            <input type="file" style="width: 100%"
                                    id="file-input" name="xml">
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
            <div class="col-md-8 col-md-offset-2">
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
case UPLOAD_ERR_BADXML:
    echo "Bad XML format!";
    break;
default:
    echo "There was an error in the uploaded file.";
}

?>
                </div>
<?php } else if( !$noInput ) {
    $fileSize = round($_FILES["xml"]["size"]);
    echo "<h4 class='text-center'>Remembering $histXML->fileName - " .
         "$histXML->msgCount messages of memories!</h4>";
    echo "<hr>";
    echo "<div class='conversation'>$histXML->contents</div>";
} ?>
            </div>
        </div>
    </body>
</html>
