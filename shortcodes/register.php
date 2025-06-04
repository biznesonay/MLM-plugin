<?php
$datatable = new Datatable_List();
// Изменено условие: теперь показываем только дистрибьюторов с рангом 1 и выше
$condition = "role = 'distributor' AND rank > 0";
$sponsors = $datatable->get_all_cond_data('mlm_users', $condition);
$cities = $datatable->getCity();

// Получаем настройки reCAPTCHA
$recaptcha_enabled = get_option('mlm_recaptcha_enabled', 'no');
$recaptcha_site_key = get_option('mlm_recaptcha_site_key', '');
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
    
    .phone-error, .recaptcha-error {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: none;
    }
    
    .form-control.error {
        border-color: #dc3545;
    }
    
    .recaptcha-container {
        margin: 20px 0;
    }
</style>

<?php if ($recaptcha_enabled == 'yes' && !empty($recaptcha_site_key)): ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>

<div class="HGGu_login">
    <h1><?php _e('User Registration', 'marketing'); ?></h1>
    <br>
    <form class="form_cla_login" id="frontendSignUp" method="post" action="<?= admin_url('admin-post.php'); ?>">
        <?php if (isset($_GET['fieldempty'])) { ?>
            <div class="error-msg">
                <i class="fa fa-times-circle"></i>
                <?php _e('All fields is requird.', 'marketing'); ?>
            </div>
        <?php } ?>
        <?php if (isset($_GET['registererror'])) { ?>
            <div class="error-msg">
                <i class="fa fa-times-circle"></i>
                <?php _e('Данный номер уже зарегистрирован', 'marketing'); ?>
            </div>
        <?php } ?>
        <?php if (isset($_GET['recaptcha_error'])) { ?>
            <div class="error-msg">
                <i class="fa fa-times-circle"></i>
                <?php _e('Пожалуйста, подтвердите, что вы не робот', 'marketing'); ?>
            </div>
        <?php } ?>
        <?php if (isset($_GET['registration_limit'])) { ?>
            <div class="error-msg">
                <i class="fa fa-times-circle"></i>
                <?php _e('Слишком много попыток регистрации. Попробуйте позже.', 'marketing'); ?>
            </div>
        <?php } ?>
        <?php if (isset($_GET['registersuccess'])) { ?>
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
            <div class="phone-error" id="phone-error"><?php _e('Данный номер уже зарегистрирован', 'marketing'); ?></div>
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

        <?php if ($recaptcha_enabled == 'yes' && !empty($recaptcha_site_key)): ?>
        <div class="recaptcha-container">
            <div class="g-recaptcha" data-sitekey="<?= $recaptcha_site_key ?>"></div>
            <div class="recaptcha-error" id="recaptcha-error"><?php _e('Пожалуйста, подтвердите, что вы не робот', 'marketing'); ?></div>
        </div>
        <?php endif; ?>

        <input type="hidden" name="us_return_url" value="<?= get_permalink(get_the_ID()); ?>">
        <input type="hidden" name="action" value="mlm_frontend_user_register">
        <input type="submit" name="submit" value="<?php _e('Submit', 'marketing'); ?>" id="submit-btn">
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
        
        let phoneCheckTimer;
        let isPhoneValid = true;
        
        // Проверка номера телефона при вводе
        jQuery('#distributor_phone').on('input', function() {
            clearTimeout(phoneCheckTimer);
            const phone = jQuery(this).val();
            
            // Проверяем только если введен полный номер
            if (phone.replace(/[^0-9]/g, '').length >= 11) {
                phoneCheckTimer = setTimeout(function() {
                    checkPhoneExists(phone);
                }, 500); // Задержка 500мс после окончания ввода
            } else {
                jQuery('#phone-error').hide();
                jQuery('#distributor_phone').removeClass('error');
                isPhoneValid = true;
            }
        });
        
        function checkPhoneExists(phone) {
            jQuery.ajax({
                url: '<?= admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'check_phone_exists',
                    phone: phone
                },
                success: function(response) {
                    if (response.exists) {
                        jQuery('#phone-error').show();
                        jQuery('#distributor_phone').addClass('error');
                        isPhoneValid = false;
                    } else {
                        jQuery('#phone-error').hide();
                        jQuery('#distributor_phone').removeClass('error');
                        isPhoneValid = true;
                    }
                }
            });
        }
        
        // Предотвращаем отправку формы если номер уже существует или reCAPTCHA не пройдена
        jQuery("#frontendSignUp").on('submit', function(e) {
            let isValid = true;
            
            // Проверка телефона
            if (!isPhoneValid) {
                e.preventDefault();
                jQuery('#phone-error').show();
                jQuery('#distributor_phone').focus();
                isValid = false;
            }
            
            <?php if ($recaptcha_enabled == 'yes'): ?>
            // Проверка reCAPTCHA
            const recaptchaResponse = grecaptcha.getResponse();
            if (!recaptchaResponse) {
                e.preventDefault();
                jQuery('#recaptcha-error').show();
                isValid = false;
            } else {
                jQuery('#recaptcha-error').hide();
            }
            <?php endif; ?>
            
            return isValid;
        });
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
                us_sponsor_id: "required"
            },
            messages: {
                us_name: "<?php _e('Please enter your name', 'marketing') ?>",
                us_email: "<?php _e('Please enter a valid email address', 'marketing') ?>",
                us_phone: "<?php _e('Please enter phone number', 'marketing') ?>",
                us_sponsor_id: "<?php _e('Please choose your sponsor', 'marketing') ?>"
            }
        });
    });
</script>