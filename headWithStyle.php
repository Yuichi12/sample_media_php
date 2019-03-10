<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width">
  <meta name="description" content="">
  <title>twitter-sample</title>
  <link rel="stylesheet" href="node_modules/@fortawesome/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="./dist/css/style.css">
  <style>
    .p-hero {
      background: url(<?php echo sanitize(showImg($userData['pic2']));
      ?>);
      background-position: 50% 50%;
      background-size: cover;
      height: 400px;
      width: 100%;
    }

  </style>
</head>
