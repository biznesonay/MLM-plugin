<?php
$datatable = new Datatable_List();
$distributors = $datatable->getAllDistributorWithRewards('mlm_users');
$files = $datatable->getAllWillPayReportFile();
$payedFiles = $datatable->getAllPayedReportFile();
?>
<style>
    .reports-list {
        display: flex;
    }
    .reports-list table {
        display: table;
        width: 250px;
    }

    .reports-list table thead {
        display: block;
        text-align: left;
    }

    .reports-list table tbody {
        display: block;
        overflow-y: auto;
        height: 110px
    }
</style>

<div class="jhRf">
    <div class="pre-loader" style="display: none">
        <div class="overlay"></div>

        <div class='loader-container'>
            <div class="prespinner"></div>
        </div>
    </div>

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
            <li class="act">
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
            <li>
                <a href="<?= get_admin_url() . 'admin.php?page=rank'; ?>">
                    <?php _e('Date of Rank’s change', 'marketing') ?>
                </a>
            </li>
        </ul>
    </div>

    <div class="reports-list">
        <div class="report-1">
            <h3><?php _e('List of reports (Pay)', 'marketing') ?></h3>
            <?php if ($files): ?>


                <table>
                    <thead>
                    <tr>
                        <th>№</th>
                        <th><?php _e('File', 'marketing') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $dir = wp_upload_dir();
                    $basePath = $dir['baseurl'];
                    foreach ($files as $k => $file) : ?>
                        <tr>
                            <td><?= $k + 1; ?></td>
                            <td>
                                <a href="<?= $basePath . '/report/' . $file['file_name'] ?>"><?= $file['file_name'] ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

            <?php endif; ?>
            <br>
        </div>
        <div class="report-2">
            <h3><?php _e('List of reports (Payed)', 'marketing') ?></h3>

            <?php if ($payedFiles): ?>


                <table>
                    <thead>
                    <tr>
                        <th>№</th>
                        <th><?php _e('File', 'marketing') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $dir = wp_upload_dir();
                    $basePath = $dir['baseurl'];
                    foreach ($payedFiles as $k => $file) : ?>
                        <tr>
                            <td><?= $k + 1; ?></td>
                            <td>
                                <a href="<?= $basePath . '/report/' . $file['file_name'] ?>"><?= $file['file_name'] ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <br>
        </div>
    </div>

    <div id="structure-panel" class="tavsect">
        <h3><?php _e('Structure Panel', 'marketing') ?></h3>

        <table id="structure-table" class="ui celled table" style="width:100%">
            <thead>
            <tr>
                <th><?php _e('Sl no', 'marketing') ?></th>
                <th><?php _e('Distributor ID', 'marketing') ?></th>
                <th><?php _e('Distributor Name', 'marketing') ?></th>
                <th><?php _e('Rank', 'marketing') ?></th>
                <th><?php _e('Sponsor ID', 'marketing') ?></th>
                <th><?php _e('Sponsor Name', 'marketing') ?></th>
                <th><?php _e('PCC', 'marketing') ?></th>
                <th><?php _e('SCC', 'marketing') ?></th>
                <th><?php _e('DR', 'marketing') ?></th>
                <th><?php _e('SR', 'marketing') ?></th>
                <th><?php _e('MR', 'marketing') ?></th>
                <th><?php _e('BR', 'marketing') ?></th>
                <th><?php _e('BRC', 'marketing') ?></th>
                <th><?php _e('ALLR', 'marketing') ?></th>
                <th>
                    <label for="select-all"><?php _e('Select All', 'marketing') ?> </label>
                    <input type="checkbox" name="all" value="all" id="select-all">
                </th>
            </tr>
            </thead>
            <tbody>
            <?php $i = 1;
            foreach ($distributors as $distributor) { ?>
                <tr>
                    <td><?= $i; ?></td>
                    <td><?= $distributor->unique_id; ?></td>
                    <td><?= $distributor->user_name; ?></td>
                    <td><?= $distributor->rank; ?></td>
                    <td><?= $distributor->sponsor_id; ?></td>
                    <td><?= get_user_name($distributor->sponsor_id); ?></td>
                    <td><?= $distributor->pcc; ?></td>
                    <td><?= $distributor->scc; ?></td>
                    <td><?= $distributor->dr; ?></td>
                    <td><?= $distributor->sr; ?></td>
                    <td><?= $distributor->mr; ?></td>
                    <td><?= $distributor->br; ?></td>
                    <td><?= $distributor->br_car ?? 0; ?></td>
                    <td><?= (float)$distributor->dr + (float)$distributor->sr + (float)$distributor->mr ?></td>
                    <td class="to-select">
                        <input type="checkbox" name="user_id" value="<?= $distributor->unique_id ?>"
                               class="select-item">
                    </td>
                </tr>
                <?php $i++;
            } ?>
            </tbody>
            <tfoot>
            <tr>
                <th><?php _e('Sl no', 'marketing') ?></th>
                <th><?php _e('Distributor ID', 'marketing') ?></th>
                <th><?php _e('Distributor Name', 'marketing') ?></th>
                <th><?php _e('Rank', 'marketing') ?></th>
                <th><?php _e('Sponsor ID', 'marketing') ?></th>
                <th><?php _e('Sponsor Name', 'marketing') ?></th>
                <th><?php _e('PCC', 'marketing') ?></th>
                <th><?php _e('SCC', 'marketing') ?></th>
                <th><?php _e('DR', 'marketing') ?></th>
                <th><?php _e('SR', 'marketing') ?></th>
                <th><?php _e('MR', 'marketing') ?></th>
                <th><?php _e('BR', 'marketing') ?></th>
                <th><?php _e('BRC', 'marketing') ?></th>
                <th><?php _e('ALLR', 'marketing') ?></th>
                <th>
                    <button id="pay"><?php _e('Pay', 'marketing') ?></button>
                </th>
            </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
   jQuery('#structure-table').DataTable({
    order: [[13, 'desc']], // Сортировка по столбцу ALLR (индекс 13) по убыванию
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

    jQuery(document).ready(function () {
        jQuery('#select-all').click(function () {
            jQuery('.select-item').each(function (index, item) {
                var $_item = jQuery(item);
                if ($_item.is(':checked')) {
                    $_item.prop('checked', false)
                } else {
                    $_item.prop('checked', true);
                }
            })
        })

        jQuery('#pay').click(function () {
            const users = [];

            jQuery('.to-select input:checkbox:checked').each(function (index, item) {
                users.push(jQuery(item).val());
            });


            jQuery.ajax({
                type: "POST",
                url: "<?= admin_url('admin-ajax.php'); ?>",
                data: {'action': "all_circulation", 'users': users},
                success: function (r) {
                    console.log(r);
                    r = JSON.parse(r);

                    jQuery('.prespinner').css('display', 'none');
                    // jQuery('#trr' + id).fadeOut();

                    Swal.fire({
                        icon: r.status ? 'success' : 'error',
                        text: r.message
                    });

                    if (r.status) {
                        location.reload();
                    }
                }
            });
        })
    });

</script>