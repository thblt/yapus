<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" href="foundation.min.css" />

<style type="text/css">
body { padding-top : 1em;}
.alert-box a { color: white; text-decoration: underline;}
</style>

<title>Yapus</title>

</head>
<body>
    
<div class="row">
    <div class="twelve columns"><?php foreach($flashes as $flash): ?>
        <div data-alert class="alert-box <?= $flash[0] ?> radius">
            <?php print($flash[1]); ?>
        </div>    
    <?php endforeach; ?></div>
</div>