<!DOCTYPE html>
<html lang="en">
<head>
        <title>Create xMatters User</title>
        <meta charset="utf-8" />
        <meta name="description" content="Create xMatters User" />
        <meta name="robots" content="all" />
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
        <!--[if lt IE 9]>
                <script src="html5shiv.js"></script>
                <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
        <![endif]-->
        <script type="text/javascript" src="http://www.google.com/jsapi"></script>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
        <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
        <script type="text/javascript" src="../../js/datepicker.js"></script>

<script>
function doQuery() {
  msg = "<br><center><img src='loading-1-1.gif'><br>";
  msg = msg + "Please wait for server to process...</center>";
  $('#reportInfo').html(msg);

  var enumber = $('#eNumber').val();
  var first = $('#first').val();
  var last = $('#last').val();
  var tz = $('#tz option:selected').val();

  var update = $.ajax({
    url: "doquery.php",
    type: "GET",
    cache: false,
    data: "enumber="+enumber+"&first="+first+"&last="+last+"&tz="+tz,
    dataType: "html"
  });

  update.done(function(msg) {
    $('#reportInfo').html(msg);
    $("html, body").scrollTop($('#reportInfo').offset().top);
  });
}

</script>

</head>

<body>


<div id="boxit">
<div><!-- <img src="path/to/your/logo.png" alt="Your Company Name" /> --></div>
</div>
<div id="content">
<div id="confirm">
        <b>Request New xMatters User</b><br><br>

        <p>
        <table><tr>
        <td>eNumber:</td>
        <!-- enumber is what short for employee number its the username you want to use in xMatters  -->
        <td><input type="text" name="eNumber" id="eNumber" size="12" maxlength="10"></td>
        </tr>
        <tr>
        <td>First Name:</td>
        <td><input type="text" name="first" id="first" size="20" maxlength="20"></td>
        </tr>
        <tr>
        <td>Last Name:</td>
        <td><input type="text" name="last" id="last" size="20" maxlength="20"></td>
        </tr>
        <tr>
        <td>Timezone:</td>
        <!-- timezone isn't currently used but is here for future use as needed  -->
        <td><select name="tz" id="tz">
            <option value="Eastern" selected>Eastern</option>
            <option value="Central">Central</option>
            <option value="Mountain">Mountain</option>
            <option value="Pacific">Pacific</option>
            </select>
        </td>
        </tr></table>
        </p>

        <input type="button" name="submit" value="Submit" class="empButton submit" onclick="doQuery();"></p>

<br>

<div id="reportInfo"></div>

<div id="rawData"></div>

</div>
</div>

</body>
</html>
