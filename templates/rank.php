<?php

$datatable = new Datatable_List();
$data = $datatable->getAllUserRank();
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
            <li>
                <a href="<?= get_admin_url() . 'admin.php?page=mlm-rewards-history-panel'; ?>">
                    <?php _e('Rewards History', 'marketing') ?>
                </a>
            </li>
            <li class="act">
                <a href="<?= get_admin_url() . 'admin.php?page=rank'; ?>">
                    <?php _e('Date of Rank\'s change', 'marketing') ?>
                </a>
            </li>
        </ul>
    </div>

    <h3><?php _e('Date of Rank\'s change', 'marketing') ?></h3>

    <table id="rank" class="ui celled table" style="width:100%">
        <thead>
        <tr>
            <th><?php _e('Sl no', 'marketing') ?>.</th>
            <th><?php _e('User ID', 'marketing') ?></th>
            <th><?php _e('User Name', 'marketing') ?></th>
            <th><?php _e('Rank', 'marketing') ?></th>
            <th><?php _e('Date and Time', 'marketing') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php 
        $timezone = new DateTimeZone('Asia/Almaty');
        foreach ($data as $k => $item) : 
            $date = new DateTime($item['created_at']);
            $date->setTimezone($timezone);
            $timestamp = $date->getTimestamp();
        ?>
            <tr id="trr<?= $item['id']; ?>">
                <td><?= $k + 1; ?></td>
                <td><?= $item['unique_id']; ?></td>
                <td><?= $item['user_name']; ?></td>
                <td><?= $item['rank_id']; ?></td>
                <td data-order="<?= $timestamp; ?>"><?= $date->format('d.m.Y H:i:s') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <th><?php _e('Sl no', 'marketing') ?>.</th>
            <th><?php _e('User ID', 'marketing') ?></th>
            <th><?php _e('User Name', 'marketing') ?></th>
            <th><?php _e('Rank', 'marketing') ?></th>
            <th><?php _e('Date and Time', 'marketing') ?></th>
        </tr>
        </tfoot>
    </table>
</div>

<script>
    jQuery(document).ready(function () {
        jQuery('#rank').DataTable({
            "order": [[ 4, "desc" ]], // Сортировка по дате по умолчанию
            columnDefs: [
                {
                    targets: 4, // Колонка с датой
                    type: 'num' // Используем числовую сортировку для timestamp
                }
            ],
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