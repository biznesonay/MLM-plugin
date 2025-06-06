<?php

$datatable = new Datatable_List();
$distributors = $datatable->get_all_current_distrubutor('mlm_users');
$rewardsHistory = $datatable->getAllRewardsHistory();
?>

<div class="jhRf">
    <div class="trR">
        <ul>
            <li><a href="<?= get_admin_url() . 'admin.php?page=mlm-distributor-panel'; ?>">
                    <?php _e('Distributor panel', 'marketing') ?></a>
            </li>
            <li>
                <a href="<?= get_admin_url() . 'admin.php?page=mlm-commodity-circulation-panel'; ?>">
                    <?php _e('Commodity Circulation Panel', 'marketing') ?>
                </a>
            </li>
            <li>
                <a href="<?= get_admin_url() . 'admin.php?page=mlm-structure-panel'; ?>">
                    <?php _e('Structure Panel', 'marketing'); ?>
                </a>
            </li>
            <li>
                <a href="<?= get_admin_url() . 'admin.php?page=mlm-family-panel'; ?>">
                    <?php _e('Family Panel', 'marketing'); ?>
                </a>
            </li>
            <li class="act">
                <a href="<?= get_admin_url() . 'admin.php?page=mlm-rewards-history-panel'; ?>">
                    <?php _e('Rewards History', 'marketing') ?>
                </a>
            </li>
            <li>
                <a href="<?= get_admin_url() . 'admin.php?page=rank'; ?>">
                    <?php _e('Date of Rank\'s change', 'marketing') ?>
                </a>
            </li>
        </ul>
    </div>


    <div class="FRuy">
        <form class="form_cla" id="distributor-register-form" action="<?= admin_url('admin-post.php'); ?>" method="POST">
            <h3><?php _e( 'History of getting rewards by distributors', 'marketing' ); ?></h3>
            <label for="amount"><?php _e( 'Amount of rewards', 'marketing' ); ?> <strong>*</strong></label>
            <input type="text" name="amount" id="amount" required>

            <label for="distributor_id"><?php _e( 'Distributor ID', 'marketing' ); ?> <strong>*</strong></label>
            <select class="ui search dropdown" name="user_id" id="distributor_id" required>
                <option value="">Select Distributor</option>
                <?php foreach ($distributors as $distributor) { ?>
                    <option value="<?= $distributor->unique_id; ?>"><?= $distributor->user_name . ' (' . $distributor->unique_id . ')'; ?></option>
                <?php } ?>
            </select>

            <input type="hidden" name="action" value="rewords_history">
            <input type="submit" name="submit" value="<?php _e('Create', 'marketing'); ?>">
        </form>
    </div>

    <h3><?php _e('Rewards history', 'marketing') ?></h3>

    <table id="transaction" class="ui celled table" style="width:100%">
        <thead>
        <tr>
            <th><?php _e('Sl no', 'marketing') ?>.</th>
            <th><?php _e('User ID', 'marketing') ?></th>
            <th><?php _e('Name', 'marketing') ?></th>
            <th><?php _e('Payout Rewards', 'marketing') ?></th>
            <th><?php _e('After account balance', 'marketing'); ?></th>
            <th><?php _e('Payout Date and Time', 'marketing') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php 
        $i=1; 
        $timezone = new DateTimeZone('Asia/Almaty');
        foreach ($rewardsHistory as $item) {
            $date = new DateTime($item->created_at);
            $date->setTimezone($timezone);
        ?>
            <tr id="trr<?= $item->id; ?>">
                <td><?= $i; ?></td>
                <td><?= $item->user_id; ?></td>
                <td><?= $item->user_name; ?></td>
                <td><?= $item->amount; ?></td>
                <td><?= $item->after_rewords_balance; ?></td>
                <td><?= $date->format('d.m.Y H:i:s') ?></td>
            </tr>
            <?php $i++; } ?>
        </tbody>
        <tfoot>
        <tr>
            <th><?php _e('Sl no', 'marketing') ?>.</th>
            <th><?php _e('User ID', 'marketing') ?></th>
            <th><?php _e('Name', 'marketing') ?></th>
            <th><?php _e('Payout Rewards', 'marketing') ?></th>
            <th><?php _e('After account balance', 'marketing'); ?></th>
            <th><?php _e('Payout Date and Time', 'marketing') ?></th>
        </tr>
        </tfoot>
    </table>
</div>

<script>
    jQuery(document).ready(function() {
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
        jQuery('.ui.dropdown').dropdown();
    });
</script>