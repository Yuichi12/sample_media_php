<header class="globalNav">
  <h3 class="header-logo"><a href="index.php">記事投稿サイト</a></h3>

  <div class="menu-group">
    <?php if (basename($_SERVER['PHP_SELF']) == 'index.php'){ ?>
    <form method="get" class="form-inline my-2 my-lg-0 mr-4 display-4">
      <input name="word" value="<?php echo sanitize(getFormData('word', true)); ?>" class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
      <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
    </form>
    <?php } ?>
    <ul class="menu">
      <?php if (empty($_SESSION['user_id'])){ ?>
      <li class="menu-item"><a href="signup.php" class="menu-link">サインイン</a></li>
      <li class="menu-item"><a href="login.php" class="menu-link">ログイン</a></li>
      <?php } else{ ?>
      <li class="menu-item"><a href="mypage.php" class="menu-link">マイページ</a></li>
      <li class="menu-item"><a href="logout.php" class="menu-link">ログアウト</a></li>
      <?php } ?>
    </ul>
  </div>
</header>
