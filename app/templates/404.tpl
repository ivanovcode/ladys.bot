<!DOCTYPE html>
<html class="no-js">

<head>
    <meta charset="utf-8">
    <title>404</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="app/assets/css/404.css" />
    <script src="app/assets/components/js/modernizr.js"></script>
</head>

<body>
<div id="container">
    <div class="overlay"></div>
    <div class="item-title">
        <div id="message"></div>
        <div class="link-bottom">
            <a class="link-icon" href="#"><i class="icon ion-ios-home"></i> HOME</a>
            <a class="link-icon" href="#"><i class="icon ion-ios-compose"></i> WRITE TO ME</a>
        </div>
    </div>
</div>
<script src="app/assets/components/js/jquery.min.js"></script>
<script src="app/assets/components/js/jquery.easings.min.js"></script>
<script src="app/assets/components/js/bootstrap.min.js"></script>
<script>
    var messages_ = ('{messages}'?JSON.parse('{messages}'):'');
</script>
<script src="app/assets/js/404.js"></script>
</body>

</html>