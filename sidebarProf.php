<aside class="col-md-3 sidebar-prof">
  <div class="list-group">
  <p class="list-group-item">
  <?php debug('$dbFormDataの中身:' . print_r($userData, true)); ?>
  <?php echo (!empty($userData['profile'])) ? $userData['profile'] : ''; ?>
  </p>
  </div>
</aside>
