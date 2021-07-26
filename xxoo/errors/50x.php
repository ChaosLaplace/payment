<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache" />
    <meta http-equiv="Pragma" content="no-cache" />
	<title><?php echo $position ?></title>
    <style type="text/css">
        * { margin:0; padding:0; }
        body { text-align:center; line-height:30px; }
        .error-box { border:1px solid #cccccc; margin:30px; padding:20px; text-align:left; }
        .error-box .message { font-size:15px; color:#a00a00; }
        .error-box .position { font-size:14px; color:#000; padding:10px 0; }
    </style>
</head>
<body>
<div class="error-box">
    <p class="message"><?php echo nl2br($message); ?></p>
    <p class="position"><?php echo $position ?></p>
</div>
</body>
</html>
