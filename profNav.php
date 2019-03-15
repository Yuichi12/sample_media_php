<div class="p-hero">

</div>
<nav class="profBar navbar sticky-top navbar-light bg-light mb-5">
  <img class="profBar-img" src="<?php echo sanitize(showImg($userData['pic1'])); ?>" alt="">
  <h5 class="profBar-name">
    <?php echo sanitize($userData['user_name']); ?>
  </h5>
  <?php if ($_SESSION['user_id'] !== $u_id){ ?>
  <form action="" method="post" class="profNav-btn"><button type="submit" name="submit" value="submit">メッセージ</button></form>
  <?php } ?>
  <div class="profBar-favorite"><?php  ?></div>
</nav>