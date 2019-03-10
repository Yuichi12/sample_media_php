<div class="p-hero">

</div>
<nav class="profBar navbar sticky-top navbar-light bg-light mb-5">
  <img class="profBar-img" src="<?php echo sanitize(showImg($userData['pic1'])); ?>" alt="">
  <h5 class="profBar-name">
    <?php echo sanitize($userData['user_name']); ?>
  </h5>
  <div class="profBar-favorite"><?php  ?></div>
</nav>