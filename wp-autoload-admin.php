<div class="wrap">
  <h2>WP Autoload</h2>

  <form action="options.php" method="post">
    <?php settings_fields('wp_autoload_options') ?>
    <?php do_settings_sections('wp_autoload') ?>

    <p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>"  /></p>
  </form>
</div>
