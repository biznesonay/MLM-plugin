<?php if (is_user_logged_in()) {
    $datatable = new Datatable_List();
    $condition = "user_id = '" . get_current_user_id() . "'";
    $user = $datatable->get_all_cond_data('mlm_users', $condition);
    $condition2 = "mlm_user_id='" . $user[0]->unique_id . "'";
    $reword = $datatable->get_all_cond_data('mlm_rewards', $condition2);
    $condition3 = "tran_user_id='" . $user[0]->unique_id . "'";
    $transactions = $datatable->get_all_cond_data('mlm_transactions', $condition3);
    $rewardsHistory = $datatable->getAllRewardsHistoryByUser($user[0]->unique_id);
    
    // Устанавливаем часовой пояс Almaty
    $timezone = new DateTimeZone('Asia/Almaty');
    ?>
    <div>
        <div>
            <div class="prespinner"></div>
            <div class="HGGu_login">
                <h1><?php _e('User Profile', 'marketing') ?></h1>
                <form class="form_cla_login">
                    <div class="dbhd">
                        <label><?php _e('Name', 'marketing') ?></label>
                        <input type="text" name="us_user_name" value="<?= $user[0]->user_name; ?>" readonly>
                        <span>
						<i class="fa fa-pencil-square-o edtshowName"></i>
						<i class="fa fa-check disnon chkhidName"></i>
					</span>
                    </div\> <div class="dbhd">
                        <label><?php _e('User id', 'marketing') ?></label>
                        <input type="text" name="us_user_name" value="<?= $user[0]->unique_id; ?>" readonly>
                        <span>
					</span>
                    </div>
                    <div class="dbhd">
                        <label><?php _e('Phone', 'marketing') ?></label>
                        <input type="email" name="us_user_phone" value="<?= $user[0]->user_phone; ?>" readonly>
                        <span>
					</span>
                    </div>
                    <input type="hidden" name="login_user_id" value="<?= get_current_user_id(); ?>">
                    <?php if (wp_get_current_user()->roles[0] = 'administrator') { ?>
                        <label><?php _e('Rank', 'marketing') ?></label>
                        <a class="disable"><?= $user[0]->rank; ?></a>
                        <label><?php _e('PCC', 'marketing') ?></label>
                        <a class="disable"><?= $reword[0]->pcc; ?></a>
                        <label><?php _e('SCC', 'marketing') ?></label>
                        <a class="disable"><?= $reword[0]->scc; ?></a>
                        <label><?php _e('Direct Reward', 'marketing') ?> </label>
                        <a class="disable"><?= $reword[0]->dr; ?></a>
                        <label><?php _e('Structural Reward', 'marketing') ?> </label>
                        <a class="disable"><?= $reword[0]->sr; ?></a>
                        <label><?php _e('Management Reward', 'marketing') ?> </label>
                        <a class="disable"><?= $reword[0]->mr; ?></a>
                        <label><?php _e('All Rewards', 'marketing') ?> </label>
                        <a class="disable"><?= (float)$reword[0]->dr + (float)$reword[0]->sr + (float)$reword[0]->mr; ?></a>
                    <?php } ?>
                </form>
            </div>
        </div>

        <div>
            <h4><?php _e('Personal Transaction Table', 'marketing') ?></h4>
            <table id="transaction" class="ui celled table" style="width:100%">
                <thead>
                <tr>
                    <th><?php _e('Sl no.', 'marketing') ?></th>
                    <th><?php _e('Amount', 'marketing') ?></th>
                    <th><?php _e('Transaction Date', 'marketing') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php $i = 1;
                foreach ($transactions as $transaction) { 
                    // Конвертируем Unix timestamp в DateTime с часовым поясом Almaty
                    $date = new DateTime();
                    $date->setTimestamp($transaction->date);
                    $date->setTimezone($timezone);
                    ?>
                    <tr>
                        <td><?= $i; ?></td>
                        <td><?= $transaction->amount . ' тенге'; ?></td>
                        <td><?= $date->format('F j, Y'); ?></td>
                    </tr>
                    <?php $i++;
                } ?>
                </tbody>
                <tfoot>
                <tr>
                    <th><?php _e('Sl no.', 'marketing') ?></th>
                    <th><?php _e('Amount', 'marketing') ?></th>
                    <th><?php _e('Transaction Date', 'marketing') ?></th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <h3><?php _e('Rewards history', 'marketing') ?></h3>

    <div class="eendtree_wrap">
        <table id="transaction" class="ui celled table">
            <thead>
            <tr>
                <th><?php _e('Sl no', 'marketing') ?>.</th>
                <th><?php _e('User ID', 'marketing') ?></th>
                <th><?php _e('Payout Rewards', 'marketing') ?></th>
                <th><?php _e('Payout Date and Time', 'marketing') ?></th>
                <th><?php _e('After account balance', 'marketing'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php $i=1; foreach ($rewardsHistory as $item) {  
                // Конвертируем строку даты в DateTime с часовым поясом Almaty
                $date = new DateTime($item->created_at);
                $date->setTimezone($timezone);
                ?>
                <tr id="trr<?= $item->id; ?>">
                    <td><?= $i; ?></td>
                    <td><?= $item->unique_id; ?></td>
                    <td><?= $item->amount; ?></td>
                    <td><?= $date->format('d.m.Y H:i:s') ?></td>
                    <td><?= $item->after_rewords_balance; ?></td>
                </tr>
                <?php $i++; } ?>
            </tbody>
            <tfoot>
            <tr>
                <th><?php _e('Sl no', 'marketing') ?>.</th>
                <th><?php _e('User ID', 'marketing') ?></th>
                <th><?php _e('Payout Rewards', 'marketing') ?></th>
                <th><?php _e('Payout Date and Time', 'marketing') ?></th>
                <th><?php _e('After account balance', 'marketing'); ?></th>
            </tr>
            </tfoot>
        </table>
    </div>

    <br>
    <h4><?php _e('Family tree', 'marketing') ?></h4>
    <div class="eendtree_wrap">
        <div class="tree">
            <ul>
                <li>
                    <div class="family">
                        <div class="person child male mngs" style="background: #456990;color: #fff;">
                            <div class="name"><?php _e('Management', 'marketing') ?></div>
                        </div>
                        <div class="parent">
                            <div class="person female" style="background: #F45B69;color: #fff;">
                                <div class="name"><?= $user[0]->user_name; ?></div>
                                <div class="das">
                                    <span><?= $user[0]->unique_id; ?></span>
                                    <span><?= $user[0]->rank; ?></span>
                                </div>
                            </div>
                            <?php
                            $single = '';
                            $end = '';
                            ?>
                            <?php if (count(get_mlm_children($user[0]->unique_id)) > 0) {
                                if (count(get_mlm_children($user[0]->unique_id)) == 1) {
                                    $single = 'single_cl';
                                }
                                ?>
                                <ul>
                                    <?php foreach (get_mlm_children($user[0]->unique_id) as $ndch) {
                                        if (count(get_mlm_children($ndch->unique_id)) == 0) {
                                            $end = 'endcl_cl';
                                        }
                                        ?>
                                        <li>
                                            <div class="family <?php if ($end != '') {
                                                echo $single;
                                            } ?>">
                                                <div class="parent <?= $end; ?>">
                                                    <div class="person male">
                                                        <div class="name"><?= $ndch->user_name; ?></div>
                                                        <div class="das">
                                                            <span><?= $ndch->unique_id; ?></span>
                                                            <span><?= $ndch->rank; ?></span>
                                                        </div>
                                                    </div>
                                                    <?php
                                                    $single = '';
                                                    $end = '';
                                                    ?>
                                                    <?php if (count(get_mlm_children($ndch->unique_id)) > 0) {
                                                        if (count(get_mlm_children($ndch->unique_id)) == 1) {
                                                            $single = 'single_cl';
                                                        }
                                                        ?>
                                                        <ul>
                                                            <?php foreach (get_mlm_children($ndch->unique_id) as $rdnc) {
                                                                if (count(get_mlm_children($rdnc->unique_id)) == 0) {
                                                                    $end = 'endcl_cl';
                                                                }
                                                                ?>
                                                                <li>
                                                                    <div class="family <?php if ($end != '') {
                                                                        echo $single;
                                                                    } ?>">
                                                                        <div class="parent <?= $end; ?>">
                                                                            <div class="person child male">
                                                                                <div class="name"><?= $rdnc->user_name; ?></div>
                                                                                <div class="das">
                                                                                    <span><?= $rdnc->unique_id; ?></span>
                                                                                    <span><?= $rdnc->rank; ?></span>
                                                                                </div>
                                                                            </div>
                                                                            <?php
                                                                            $single = '';
                                                                            $end = '';
                                                                            ?>
                                                                            <?php if (count(get_mlm_children($rdnc->unique_id)) > 0) {
                                                                                if (count(get_mlm_children($rdnc->unique_id)) == 1) {
                                                                                    $single = 'single_cl';
                                                                                }
                                                                                ?>
                                                                                <ul>
                                                                                    <?php foreach (get_mlm_children($rdnc->unique_id) as $fortch) {
                                                                                        if (count(get_mlm_children($fortch->unique_id)) == 0) {
                                                                                            $end = 'endcl_cl';
                                                                                        }
                                                                                        ?>
                                                                                        <li class="<?= count(get_mlm_children($fortch->unique_id)); ?>">
                                                                                            <div class="family <?php if ($end != '') {
                                                                                                echo $single;
                                                                                            } ?>">
                                                                                                <div class="parent <?= $end; ?>">
                                                                                                    <div class="person child male">
                                                                                                        <div class="name"><?= $fortch->user_name; ?></div>
                                                                                                        <div class="das">
                                                                                                            <span><?= $fortch->unique_id; ?></span>
                                                                                                            <span><?= $fortch->rank; ?></span>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <?php
                                                                                                    $single = '';
                                                                                                    $end = '';
                                                                                                    ?>
                                                                                                    <?php if (count(get_mlm_children($fortch->unique_id)) > 0) {
                                                                                                        if (count(get_mlm_children($fortch->unique_id)) == 1) {
                                                                                                            $single = 'single_cl';
                                                                                                        }
                                                                                                        ?>
                                                                                                        <ul>
                                                                                                            <?php foreach (get_mlm_children($fortch->unique_id) as $fift) {
                                                                                                                if (count(get_mlm_children($fift->unique_id)) == 0) {
                                                                                                                    $end = 'endcl_cl';
                                                                                                                }
                                                                                                                ?>
                                                                                                                <li>
                                                                                                                    <div class="family <?php if ($end != '') {
                                                                                                                        echo $single;
                                                                                                                    } ?>">
                                                                                                                        <div class="parent <?= $end; ?>">
                                                                                                                            <div class="person child male">
                                                                                                                                <div class="name"><?= $fift->user_name; ?></div>
                                                                                                                                <div class="das">
                                                                                                                                    <span><?= $fift->unique_id; ?></span>
                                                                                                                                    <span><?= $fift->rank; ?></span>
                                                                                                                                </div>
                                                                                                                            </div>
                                                                                                                            <?php
                                                                                                                            $single = '';
                                                                                                                            $end = '';
                                                                                                                            ?>
                                                                                                                            <?php if (count(get_mlm_children($fift->unique_id)) > 0) {
                                                                                                                                if (count(get_mlm_children($fift->unique_id)) == 1) {
                                                                                                                                    $single = 'single_cl';
                                                                                                                                } ?>
                                                                                                                                <ul>
                                                                                                                                    <?php foreach (get_mlm_children($fift->unique_id) as $sisxth) {
                                                                                                                                        if (count(get_mlm_children($sisxth->unique_id)) == 0) {
                                                                                                                                            $end = 'endcl_cl';
                                                                                                                                        } ?>
                                                                                                                                        <li>
                                                                                                                                            <div class="family <?php if ($end != '') {
                                                                                                                                                echo $single;
                                                                                                                                            } ?>">
                                                                                                                                                <div class="parent <?= $end; ?>">
                                                                                                                                                    <div class="person child male">
                                                                                                                                                        <div class="name"><?= $sisxth->user_name; ?></div>
                                                                                                                                                        <div class="das">
                                                                                                                                                            <span><?= $sisxth->unique_id; ?></span>
                                                                                                                                                            <span><?= $sisxth->rank; ?></span>
                                                                                                                                                        </div>
                                                                                                                                                    </div>
                                                                                                                                                    <?php
                                                                                                                                                    $single = '';
                                                                                                                                                    $end = '';
                                                                                                                                                    ?>
                                                                                                                                                    <?php if (count(get_mlm_children($sisxth->unique_id)) > 0) {
                                                                                                                                                        if (count(get_mlm_children($sisxth->unique_id)) == 1) {
                                                                                                                                                            $single = 'single_cl';
                                                                                                                                                        } ?>
                                                                                                                                                        <ul>
                                                                                                                                                            <?php foreach (get_mlm_children($sisxth->unique_id) as $sevnth) {
                                                                                                                                                                if (count(get_mlm_children($sevnth->unique_id)) == 0) {
                                                                                                                                                                    $end = 'endcl_cl';
                                                                                                                                                                }
                                                                                                                                                                ?>
                                                                                                                                                                <li>
                                                                                                                                                                    <div class="family <?php if ($end != '') {
                                                                                                                                                                        echo $single;
                                                                                                                                                                    } ?>">
                                                                                                                                                                        <div class="parent <?= $end; ?>">
                                                                                                                                                                            <div class="person child male">
                                                                                                                                                                                <div class="name"><?= $sevnth->user_name; ?></div>
                                                                                                                                                                                <div class="das">
                                                                                                                                                                                    <span><?= $sevnth->unique_id; ?></span>
                                                                                                                                                                                    <span><?= $sevnth->rank; ?></span>
                                                                                                                                                                                </div>
                                                                                                                                                                            </div>
                                                                                                                                                                            <?php
                                                                                                                                                                            $single = '';
                                                                                                                                                                            $end = '';
                                                                                                                                                                            ?>
                                                                                                                                                                            <?php if (count(get_mlm_children($sevnth->unique_id)) > 0) {
                                                                                                                                                                                if (count(get_mlm_children($sevnth->unique_id)) == 1) {
                                                                                                                                                                                    $single = 'single_cl';
                                                                                                                                                                                } ?>
                                                                                                                                                                                <ul>
                                                                                                                                                                                    <?php foreach (get_mlm_children($sevnth->unique_id) as $eigth) {
                                                                                                                                                                                        if (count(get_mlm_children($eigth->unique_id)) == 0) {
                                                                                                                                                                                            $end = 'endcl_cl';
                                                                                                                                                                                        }
                                                                                                                                                                                        ?>
                                                                                                                                                                                        <li>
                                                                                                                                                                                            <div class="family <?php if ($end != '') {
                                                                                                                                                                                                echo $single;
                                                                                                                                                                                            } ?>">
                                                                                                                                                                                                <div class="parent <?= $end; ?>">
                                                                                                                                                                                                    <div class="person child male">
                                                                                                                                                                                                        <div class="name"><?= $eigth->user_name; ?></div>
                                                                                                                                                                                                        <div class="das">
                                                                                                                                                                                                            <span><?= $eigth->unique_id; ?></span>
                                                                                                                                                                                                            <span><?= $eigth->rank; ?></span>
                                                                                                                                                                                                        </div>
                                                                                                                                                                                                    </div>
                                                                                                                                                                                                    <?php
                                                                                                                                                                                                    $single = '';
                                                                                                                                                                                                    $end = '';
                                                                                                                                                                                                    ?>
                                                                                                                                                                                                    <?php if (count(get_mlm_children($eigth->unique_id)) > 0) {
                                                                                                                                                                                                        if (count(get_mlm_children($eigth->unique_id)) == 1) {
                                                                                                                                                                                                            $single = 'single_cl';
                                                                                                                                                                                                        } ?>
                                                                                                                                                                                                        <ul>
                                                                                                                                                                                                            <?php foreach (get_mlm_children($eigth->unique_id) as $ninth) {
                                                                                                                                                                                                                if (count(get_mlm_children($ninth->unique_id)) == 0) {
                                                                                                                                                                                                                    $end = 'endcl_cl';
                                                                                                                                                                                                                } ?>
                                                                                                                                                                                                                <li>
                                                                                                                                                                                                                    <div class="family <?php if ($end != '') {
                                                                                                                                                                                                                        echo $single;
                                                                                                                                                                                                                    } ?>">
                                                                                                                                                                                                                        <div class="parent <?= $end; ?>">
                                                                                                                                                                                                                            <div class="person child male">
                                                                                                                                                                                                                                <div class="name"><?= $ninth->user_name; ?></div>
                                                                                                                                                                                                                                <div class="das">
                                                                                                                                                                                                                                    <span><?= $ninth->unique_id; ?></span>
                                                                                                                                                                                                                                    <span><?= $ninth->rank; ?></span>
                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                </li>
                                                                                                                                                                                                            <?php } ?>
                                                                                                                                                                                                        </ul>
                                                                                                                                                                                                    <?php } ?>
                                                                                                                                                                                                </div>
                                                                                                                                                                                            </div>
                                                                                                                                                                                        </li>
                                                                                                                                                                                    <?php } ?>
                                                                                                                                                                                </ul>
                                                                                                                                                                            <?php } ?>
                                                                                                                                                                        </div>
                                                                                                                                                                    </div>
                                                                                                                                                                </li>
                                                                                                                                                            <?php } ?>
                                                                                                                                                        </ul>
                                                                                                                                                    <?php } ?>
                                                                                                                                                </div>
                                                                                                                                            </div>
                                                                                                                                        </li>
                                                                                                                                    <?php } ?>
                                                                                                                                </ul>
                                                                                                                            <?php } ?>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                </li>
                                                                                                            <?php } ?>
                                                                                                        </ul>
                                                                                                    <?php } ?>
                                                                                                </div>
                                                                                            </div>
                                                                                        </li>
                                                                                    <?php } ?>
                                                                                </ul>
                                                                            <?php } ?>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                            <?php } ?>
                                                        </ul>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </li>
                                    <?php } ?>
                                </ul>
                            <?php } ?>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>

<?php } else { ?>
    <div class="sadf">
        <h5><?php _e('PLease Login or register to view profile.', 'marketing'); ?></h5>
        <a href="<?= get_site_url() . '/login'; ?>"><?php _e('Login', 'marketing');?></a>
        <a href="<?= get_site_url() . '/register'; ?>"><?php _e('Register', 'marketing');?></a>
    </div>
<?php } ?>
</div>

<script>
    jQuery(document).ready(function () {
        var adminAjax = "<?= admin_url('admin-ajax.php'); ?>";
        var id = jQuery('input[name="login_user_id"]').val();

        jQuery('.edtshowName').click(function () {
            jQuery('input[name="us_user_name"]').removeAttr('readonly');
            jQuery('.chkhidName').removeClass('disnon');
            jQuery(this).addClass('disnon');
        });
        jQuery('.edtshowEmail').click(function () {
            jQuery('input[name="us_user_email"]').removeAttr('readonly');
            jQuery('.chkhidEmail').removeClass('disnon');
            jQuery(this).addClass('disnon');
        });
        jQuery('.edtshowPass').click(function () {
            jQuery('input[name="us_user_password"]').removeAttr('readonly');
            jQuery('.chkhidPass').removeClass('disnon');
            jQuery(this).addClass('disnon');
        });

        jQuery('.chkhidName').click(function () {
            var name = jQuery('input[name="us_user_name"]').val();
            jQuery('.prespinner').css('display', 'block');
            jQuery.ajax({
                type: "POST",
                url: adminAjax,
                data: {'action': "update_data", 'us_item': 'name', 'us_value': name, 'us_user_id': id},
                success: function (response) {
                    jQuery('.prespinner').css('display', 'none');
                    jQuery('input[name="us_user_name"]').attr('readonly', 'readonly');
                    jQuery('.edtshowName').removeClass('disnon');
                    jQuery('.chkhidName').addClass('disnon');
                }
            });
        })

        jQuery('.chkhidEmail').click(function () {
            var email = jQuery('input[name="us_user_email"]').val();
            jQuery('.prespinner').css('display', 'block');
            jQuery.ajax({
                type: "POST",
                url: adminAjax,
                data: {'action': "update_data", 'us_item': 'email', 'us_value': email, 'us_user_id': id},
                success: function (response) {
                    if (response == 1) {
                        jQuery('.prespinner').css('display', 'none');
                        jQuery('input[name="us_user_email"]').attr('readonly', 'readonly');
                        jQuery('.edtshowEmail').removeClass('disnon');
                        jQuery('.chkhidEmail').addClass('disnon');
                    } else {
                        jQuery('.prespinner').css('display', 'none');
                        alert('Email already exits');
                    }
                }
            });
        })

        jQuery('.chkhidPass').click(function () {
            var password = jQuery('input[name="us_user_password"]').val();
            jQuery('.prespinner').css('display', 'block');
            jQuery.ajax({
                type: "POST",
                url: adminAjax,
                data: {'action': "update_data", 'us_item': 'password', 'us_value': password, 'us_user_id': id},
                success: function (response) {
                    jQuery('.prespinner').css('display', 'none');
                    jQuery('input[name="us_user_password"]').attr('readonly', 'readonly');
                    jQuery('.edtshowPass').removeClass('disnon');
                    jQuery('.chkhidPass').addClass('disnon');
                    jQuery('input[name="us_user_password"]').val('');
                }
            });
        })
        jQuery('#transaction').DataTable({
            language: {
                "search": "<?php _e('Search:', 'marketing') ?>",
                "lengthMenu": "<?php _e('Show _MENU_ entries', 'marketing') ?>",
                "info": "<?php _e('Showing _START_ to _END_ of _TOTAL_ entries', 'marketing') ?>",
                "infoEmpty": "<?php _e('Showing 0 to 0 of 0 entries', 'marketing');?>",
                "emptyTable": "<?php _e('No data available in table', 'marketing'); ?>",
                "paginate": {
                    "first": "<?php _e('First', 'marketing') ?>",
                    "previous": "<?php _e('Previous', 'marketing'); ?>",
                    "next": "<?php _e('Next', 'marketing') ?>",
                    "last": "<?php _e('Last', 'marketing') ?>",
                },
            }
        });
    });
</script>