<div class="HGGu_login">
    <?php if (!is_user_logged_in()) { ?>
        <h1><?php _e('User Login', 'marketing'); ?></h1>
        <br>
        <form class="form_cla_login" id="frontendSignIn" method="post" action="<?= admin_url('admin-post.php'); ?>">
            <?php if (isset($_GET['fieldempty'])) { ?>
                <div class="error-msg">
                    <i class="fa fa-times-circle"></i>
                    <?php _e('Email or password field is empty.', 'marketing'); ?>
                </div>
            <?php } ?>
            <?php if (isset($_GET['loginerror'])) { ?>
                <div class="error-msg">
                    <i class="fa fa-times-circle"></i>
                    <?php _e('Wrong credentials.', 'marketing'); ?>
                </div>
            <?php } ?>
            <label><?php _e('Email', 'marketing'); ?></label>
            <input type="email" name="us_email">
            <label><?php _e('Password', 'marketing'); ?></label>
            <input type="password" name="us_password">
            <input type="hidden" name="us_return_url" value="<?= get_permalink(get_the_ID()); ?>">
            <input type="hidden" name="action" value="mlm_frontend_user_login">
            <input type="submit" name="submit" value="<?php _e('Submit', 'marketing'); ?>">
        </form>
    <?php } else { ?>
        <h5><?php _e('Welcome Back', 'marketing'); ?>, <?= wp_get_current_user()->display_name; ?></h5>
        <a href="<?= get_site_url() . '/profile/'; ?>" class="xhkR"><?php _e('Go To Profile', 'marketing'); ?></a>
    <?php } ?>

</div>

<script>
    jQuery(function () {
        jQuery("#frontendSignIn").validate({
            rules: {
                us_email: {
                    required: true,
                    email: true
                },
                // us_password: {
                //     required: true,
                //     minlength: 6
                // }
            },
            messages: {
                us_email: "<?php _e('Please enter a valid email address', 'marketing') ?>",
                us_password: {
                    required: "<?php _e('Please provide a password', 'marketing') ?>",
                    minlength: "<?php _e('Your password must be at least 5 characters long', 'marketing') ?>"
                }
            }
        });
    });
</script>