<!DOCTYPE html>
<html lang="en" class="<?php echo $this->IEClass; ?>">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-siteapp">
  <title><?php echo CHtml::encode($this->pageTitle); ?></title>
  <link rel="icon" sizes="196x196" href="/f/images/favicon.png">
  <link rel="apple-touch-icon" href="/f/images/favicon.png">
  <link rel="apple-touch-icon-precomposed" sizes="128x128" href="/f/images/favicon.png">
  <link rel="alternate" type="application/rss+xml" title="RSS" href="<?php echo CHtml::normalizeUrl(array('/feed/index')); ?>">
  <meta name="theme-color" content="#6091ba">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <meta name="HandheldFriendly" content="True">
  <meta name="MobileOptimized" content="320">
  <meta name="description" content="<?php echo CHtml::encode($this->description); ?>">
  <meta name="keywords" content="<?php echo CHtml::encode($this->keywords); ?>">
  <meta name="author" content="<?php echo Yii::app()->params->author; ?>">
  <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]>
    <script src="/f/js/html5shiv.js"></script>
    <script src="/f/js/respond.min.js"></script>
  <![endif]-->
</head>
<body class="<?php echo $this->id; ?> <?php echo $this->id; ?>-<?php echo $this->action->id; ?>">
  <?php echo $content; ?>
</body>
</html>
