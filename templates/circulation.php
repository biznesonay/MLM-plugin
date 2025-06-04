<?php

$datatable = new Datatable_List();
$distributors = $datatable->get_all_current_distrubutor('mlm_users');
$results = $datatable->get_all_transuctions();
?>

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
            <li class="act">
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
            <li>
                <a href="<?= get_admin_url() . 'admin.php?page=rank'; ?>">
                    <?php _e('Date of Rank\'s change', 'marketing') ?>
                </a>
            </li>
        </ul>
    </div>

    <div class="FRuy">
        <form class="form_cla" id="circulation-form" action="<?= admin_url('admin-ajax.php'); ?>" method="POST">
<!--        <form class="form_cla" id="circulation-form_" action="--><?//= admin_url('admin-post.php'); ?><!--" method="POST">-->
            <h3><?php _e('Circulation Commodity', 'marketing'); ?></h3>

            <label for="distributor_id"><?php _e('Distributor ID', 'marketing'); ?>
                <strong>*</strong></label>

            <select class="ui search dropdown" name="mlm_distributor_id" id="distributor_id" required>
                <option value=""><?php _e('Select Distributor', 'marketing'); ?></option>
                <?php foreach ($distributors as $distributor) { ?>
                    <option value="<?= $distributor->unique_id; ?>"><?= $distributor->user_name . ' (' . $distributor->unique_id . ')'; ?></option>
                <?php } ?>
            </select>

            <label for="set_balance"><?php _e('Personal Circulation Commodity', 'marketing'); ?>
                <strong>*</strong></label>
            <input type="number" name="set-balance" id="set_balance" required>

            <label><?php _e('Additional expenses', 'marketing'); ?> <strong>*</strong></label>
            <div style="display: flex;">
                <div style="width: 20%; margin-right: 10px;">
                    <input type="number" name="percent_number" id="percent_number" placeholder="9%" value="9">
                </div>
                <div style="width: 80%">
                    <input type="number" name="percent_amount" id="percent_amount" readonly>
                </div>
            </div>

            <label for="circulation_commodity"><?php _e('Contribution', 'marketing'); ?>
                <strong>*</strong></label>
            <input type="number" name="mlm_circulation_commodity" id="amount" required readonly>

            <input type="hidden" name="action" value="mlm_circulation_commodity">
            <input type="submit" name="submit" value="Create">
        </form>
    </div>
    <h3>Transactions Table</h3>
    <table id="transaction" class="ui celled table" style="width:100%">
        <thead>
        <tr>
            <th><?php _e('Sl no', 'marketing'); ?></th>
            <th><?php _e('Distributor ID', 'marketing'); ?></th>
            <th><?php _e('Distributor Name', 'marketing'); ?></th>
            <th><?php _e('Source', 'marketing'); ?></th>
            <th><?php _e('Amount', 'marketing'); ?></th>
            <th><?php _e('Transaction Date', 'marketing'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php 
        $i = 1;
        $timezone = new DateTimeZone('Asia/Almaty');
        foreach ($results as $result) { 
            $date = new DateTime();
            $date->setTimestamp($result->transuction_date);
            $date->setTimezone($timezone);
        ?>
            <tr id="trr<?= $result->transuction_id; ?>">
                <td><?= $i; ?></td>
                <td><?= $result->unique_id; ?></td>
                <td><?= $result->user_name; ?></td>
                <td><?= $result->post_id ? __('site', 'marketing') : __('direct', 'marketing'); ?></td>
                <td><?= $result->amount . ' c.u.'; ?></td>
                <td data-order="<?= $result->transuction_date; ?>"><?= $date->format('F j, Y H:i:s'); ?></td>
            </tr>
            <?php $i++;
        } ?>
        </tbody>
        <tfoot>
        <tr>
            <th><?php _e('Sl no', 'marketing'); ?></th>
            <th><?php _e('Distributor ID', 'marketing'); ?></th>
            <th><?php _e('Distributor Name', 'marketing'); ?></th>
            <th><?php _e('Source', 'marketing'); ?></th>
            <th><?php _e('Amount', 'marketing'); ?></th>
            <th><?php _e('Transaction Date', 'marketing'); ?></th>
        </tr>
        </tfoot>
    </table>
</div>

<?php if (isset($_GET['somerror'])) { ?>
    <script>
        Swal.fire({
            icon: 'error',
            text: "<?php _e('Somethings wents wrong!', 'marketing') ?>"
        }).then(function () {
            window.location.href = "<?= get_admin_url() . 'admin.php?page=mlm-commodity-circulation-panel'; ?>";
        });
    </script>
<?php } ?>
<?php if (isset($_GET['transuction'])) { ?>
    <script>
        Swal.fire({
            icon: 'success',
            text: "<?php _e('New Personal Circulation Commodity added!', 'marketing') ?>"
        }).then(function () {
            window.location.href = "<?= get_admin_url() . 'admin.php?page=mlm-commodity-circulation-panel'; ?>";
        });
    </script>
<?php } ?>

<script>
    jQuery(document).ready(function () {
        jQuery('.ui.dropdown').dropdown();
        jQuery('#transaction').DataTable({
            order: [[5, 'desc']], // Сортировка по дате по умолчанию
            columnDefs: [
                {
                    targets: 5, // Колонка с датой
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

        jQuery('#set_balance, #percent_number').change(function () {
            $ = jQuery;
            var percent = $('#percent_number').val();
            var setAmount = $('#set_balance').val();
            var amount = Math.round(setAmount * percent / 100);
            $('#percent_amount').val(amount);
            $('#amount').val(setAmount - amount);
        });

        jQuery('#circulation-form').submit(function (e) {
            e.preventDefault();
            Swal.fire({
                title: "<?php _e('Are you sure?', 'marketing') ?>",
                text: "<?php _e('Do you wont to create Circulation Commodity', 'marketing') ?>",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.value) {
                    jQuery('.pre-loader').css('display', 'block');
                    var mlm_distributor_id = jQuery('#distributor_id').val();
                    var mlm_circulation_commodity = jQuery('#amount').val();
                    jQuery.ajax({
                        type: "POST",
                        url: "<?= admin_url('admin-ajax.php'); ?>",
                        data: {
                            'action': "circulation_commodity",
                            'mlm_distributor_id': mlm_distributor_id,
                            'mlm_circulation_commodity': mlm_circulation_commodity
                        },
                        success: function (r) {
                            r = JSON.parse(r);
                            jQuery('.pre-loader').css('display', 'none');

                            Swal.fire({
                                icon: r.status ? 'success' : 'error',
                                text: r.message
                            });

                            if (r.status) {
                                location.reload();
                            }
                        }
                    });
                }
            })

        });
    });

    function deleteTransaction(id) {
        Swal.fire({
            title: "<?php _e('Are you sure?', 'marketing') ?>",
            text: "<?php _e('You wont be able to revert this!', 'marketing') ?>",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.value) {
                jQuery('.prespinner').css('display', 'block');
                jQuery.ajax({
                    type: "POST",
                    url: "<?= admin_url('admin-ajax.php'); ?>",
                    data: {'action': "delete_transaction", 'trnid': id},
                    success: function (r) {
                        console.log(r);
                        r = JSON.parse(r);

                        jQuery('.prespinner').css('display', 'none');
                        jQuery('#trr' + id).fadeOut();

                        Swal.fire({
                            icon: r.status ? 'success' : 'error',
                            text: r.message
                        });
                    }
                });
            }
        })
    }
</script>