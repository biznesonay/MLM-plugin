<?php
$datatable = new Datatable_List();
$condition = "role = 'distributor' AND rank < 9";
$sponsors = $datatable->get_all_cond_data('mlm_users', $condition);$cities = $datatable->getCity();
?>
<style>
    .form-control {
        display: block;
        width: 100%;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    }
</style>

<div class="HGGu_login">
    <h1><?php _e('User Registration', 'marketing'); ?></h1>
    <br>
    <form class="form_cla_login" id="frontendSignUp" method="post" action="<?= admin_url('admin-post.php'); ?>">
        <?php if (strpos($_GET['page_id'], 'fieldempty')) { ?>
            <div class="error-msg">
                <i class="fa fa-times-circle"></i>
                <?php _e('All fields is requird.', 'marketing'); ?>
            </div>
        <?php } ?>
        <?php if (strpos($_GET['page_id'], 'registererror')) { ?>
            <div class="error-msg">
                <i class="fa fa-times-circle"></i>
                <?php _e('This email are already exits.', 'marketing'); ?>
            </div>
        <?php } ?>
        <?php if (strpos($_GET['page_id'], 'registersuccess')) { ?>
            <div class="success-msg">
                <i class="fa fa-check-circle"></i>
                <?php _e('New user successfully registered!.', 'marketing'); ?>
            </div>
        <?php } ?>

        <div class="form-group">
            <label><?php _e('Name', 'marketing'); ?></label>
            <input class="form-control" type="text" name="us_name">
        </div>

        <div class="form-group">
            <label><?php _e('Email', 'marketing'); ?></label>
            <input class="form-control" type="email" name="us_email">
        </div>

        <div class="form-group">
            <label for="distributor_phone"><?php _e('Phone', 'distributor-register'); ?></label>
            <input class="form-control" type="text" name="us_phone" id="distributor_phone" required value="+7 (___) ___-__-__">
        </div>

        <div class="form-group">
            <label><?php _e('Password', 'marketing'); ?></label>
            <input class="form-control" type="password" name="us_password">
        </div>

        <div>
            <label><?php _e('City ID', 'marketing'); ?></label>

            <select class="ui search dropdown" name="us_city_id">
                <option value=""><?php _e('Select City', 'marketing'); ?></option>
                <?php foreach ($cities as $city) { ?>
                    <option value="<?= $city['id']; ?>"><?= $city['name'] ?></option>
                <?php } ?>
            </select>
        </div>

        <div>
            <label><?php _e('Sponsor ID', 'marketing'); ?></label>

            <select class="ui search dropdown" name="us_sponsor_id">
                <option value=""><?php _e('Select Sponsor', 'marketing'); ?></option>
                <?php foreach ($sponsors as $sponsor) { ?>
                    <option value="<?= $sponsor->unique_id; ?>"><?= $sponsor->user_name . ' (' . $sponsor->unique_id . ')'; ?></option>
                <?php } ?>
            </select>
        </div>

        <input type="hidden" name="us_return_url" value="<?= get_permalink(get_the_ID()); ?>">
        <input type="hidden" name="action" value="mlm_frontend_user_register">
        <input type="submit" name="submit" value="<?php _e('Submit', 'marketing'); ?>">
    </form>
</div>
<script>
    jQuery(document).ready(function () {
        MaskedInput({
            elm: document.getElementById('distributor_phone'),
            format: '+7 (___) ___-__-__',
            separator: '+7 (   )-'
        });

        jQuery('.ui.dropdown').dropdown();
    });


    jQuery(function () {
        jQuery("#frontendSignUp").validate({
            rules: {
                us_name: "required",
                us_email: {
                    required: true,
                    email: true,
                },
                us_phone: {
                    required: true,
                },
                us_password: {
                    required: true,
                    minlength: 6
                },
                us_sponsor_id: "required"
            },
            messages: {
                us_name: "<?php _e('Please enter your name', 'marketing') ?>",
                us_email: "<?php _e('Please enter a valid email address', 'marketing') ?>",
                us_password: {
                    required: "<?php _e('Please provide a password', 'marketing') ?>",
                    minlength: "<?php _e('Your password must be at least 5 characters long', 'marketing') ?>"
                },
                us_sponsor_id: "<?php _e('Please choose your sponsor', 'marketing') ?>"
            }
        });
    });
</script>