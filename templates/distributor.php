<?php
$datatable = new Datatable_List();
$condition = "role = 'distributor' AND rank > 0";
$sponsors = $datatable->get_all_cond_data('mlm_users', $condition);
$users = $datatable->get_all_current_distrubutor_city();

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
            <li class="act"><a href="<?= get_admin_url() . 'admin.php?page=mlm-distributor-panel'; ?>">
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
            <li>
                <a href="<?= get_admin_url() . 'admin.php?page=rank'; ?>">
                    <?php _e('Date of Rank\'s change', 'marketing') ?>
                </a>
            </li>
        </ul>
    </div>

    <div class="FRuy">
        <form class="form_cla" id="distributor-register-form" action="<?= admin_url('admin-post.php'); ?>"
              method="POST">
            <h3><?php _e('Distributor Register', 'marketing'); ?></h3>
            <label for="distributor_name"><?php _e('Name', 'marketing'); ?> <strong>*</strong></label>
            <input type="text" name="mlm_distributor_name" id="distributor_name" required>
            <label for="distributor_phone"><?php _e('Phone', 'marketing'); ?> <strong>*</strong></label>
            <input type="text" name="mlm_distributor_phone" id="distributor_phone" required value="+7 (___) ___-__-__">
            <label for="distributor_sponsor"><?php _e('Sponsor ID', 'marketing'); ?> <strong>*</strong></label>
            <select class="ui search dropdown" name="mlm_distributor_sponsor" id="distributor_sponsor" required>
                <option value=""><?php _e('Select Sponsor', 'marketing'); ?></option>
                <?php foreach ($sponsors as $sponsor) { ?>
                    <option value="<?= $sponsor->unique_id; ?>"><?= $sponsor->user_name . ' (' . $sponsor->unique_id . ')'; ?></option>
                <?php } ?>
            </select>

            <input type="hidden" name="action" value="mlm_distributor_register">
            <input type="submit" name="submit" value="<?php _e('Create', 'marketing'); ?>">
        </form>
    </div>
    <h3><?php _e('Current Users', 'marketing'); ?></h3>
    <table id="distributor-table" class="ui celled table" style="width:100%">
        <thead>
        <tr>
            <th><?php _e('Sl no', 'marketing'); ?>.</th>
            <th><?php _e('Unique ID', 'marketing'); ?></th>
            <th><?php _e('Name', 'marketing') ?></th>
            <th><?php _e('City Name', 'marketing'); ?></th>
            <th><?php _e('Phone', 'marketing') ?></th>
            <th><?php _e('Sponsor Name', 'marketing'); ?></th>
            <th><?php _e('Sponsor ID', 'marketing') ?></th>
            <th><?php _e('Action', 'marketing'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php $i = 1;
        foreach ($users as $user) { ?>
            <tr id="trr<?= $user->id; ?>">
                <td><?= $i; ?></td>
                <td><?= $user->unique_id; ?></td>
                <td><?= $user->user_name; ?></td>
                <td><?= $user->city_name; ?></td>
                <td><?= $user->user_phone; ?></td>
                <td><?= get_sponsor($user->id); ?></td>
                <td><?= $user->sponsor_id; ?></td>
                <td>
                    <a onclick="editDistributor(<?= $user->id; ?>)"><i class="fa fa-pencil"></i></a>
                    <a onclick="deleteDistributor(<?= $user->id; ?>)"><i class="fa fa-trash"></i></a>
                </td>
            </tr>
            <?php $i++;
        } ?>
        </tbody>
        <tfoot>
        <tr>
            <th><?php _e('Sl no', 'marketing'); ?>.</th>
            <th><?php _e('Unique ID', 'marketing'); ?></th>
            <th><?php _e('Name', 'marketing') ?></th>
            <th><?php _e('City Name', 'marketing'); ?></th>
            <th><?php _e('Phone', 'marketing') ?></th>
            <th><?php _e('Sponsor Name', 'marketing'); ?></th>
            <th><?php _e('Sponsor ID', 'marketing') ?></th>
            <th><?php _e('Action', 'marketing'); ?></th>
        </tr>
        </tfoot>
    </table>

    <br>
</div>

<div id="popup1" class="overlay-popup">
    <div class="popup">
        <h2><?php _e('Edit Details', 'marketing'); ?></h2>
        <a class="close">&times;</a>
        <div class="content" id="ppcont">
            <form class="form_cla form_CLa">
                <label><?php _e('Name', 'marketing'); ?></label>
                <input type="text" name="name">
                <input type="submit" name="submit" value="<?php _e('Create', 'marketing'); ?>">
            </form>
        </div>
    </div>
</div>

<?php if (isset($_GET['already_exits'])) { ?>
    <script>
        Swal.fire({
            icon: 'error',
            text: "<?php _e('This user phone already exits!', 'marketing') ?>"
        }).then(function () {
            window.location.href = "<?= get_admin_url() . 'admin.php?page=mlm-distributor-panel'; ?>";
        });
    </script>
<?php } ?>
<?php if (isset($_GET['newuser'])) { ?>
    <script>
        Swal.fire({
            icon: 'success',
            text: "<?php _e('New Distributor register!', 'marketing') ?>"
        }).then(function () {
            window.location.href = "<?= get_admin_url() . 'admin.php?page=mlm-distributor-panel'; ?>";
        });
    </script>
<?php } ?>
<?php if (isset($_GET['update'])) { ?>
    <script>
        Swal.fire({
            icon: 'success',
            text: "<?php _e('Distributor update successful!', 'marketing') ?>"
        });
    </script>
<?php } ?>

<script>
    jQuery(document).ready(function () {
        jQuery('#distributor-table').DataTable({
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

        MaskedInput({
            elm: document.getElementById('distributor_phone'),
            format: '+7 (___) ___-__-__',
            separator: '+7 (   )-'
        });
    });

    function deleteDistributor(id) {
        Swal.fire({
            title: "<?php _e('Are you sure?', 'marketing') ?>",
            text: "<?php _e("You won't be able to revert this!", 'marketing') ?>",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: "<?php _e('Yes', 'marketing') ?>"
        }).then((result) => {
            if (result.value) {
                jQuery('.pre-loader').css('display', 'block');
                jQuery.ajax({
                    type: "POST",
                    url: "<?= admin_url('admin-ajax.php'); ?>",
                    data: {'action': "delete_distributor", 'distid': id},
                    success: function (r) {
                        r = JSON.parse(r);
                        jQuery('.pre-loader').css('display', 'none');
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

    function editDistributor(userID) {
        jQuery('.pre-loader').css('display', 'block');
        var ajaxUrl = "<?= admin_url('admin-ajax.php'); ?>";
        jQuery.ajax({
            type: "POST",
            url: ajaxUrl,
            data: {action: "get_user_details", 'user_id_val': userID},
            success: function (response) {
                jQuery('.pre-loader').css('display', 'none');
                jQuery('#ppcont').html(response);
                jQuery('#popup1').addClass('show');
            }
        });
    }

    jQuery(document).ready(function () {
        jQuery('.close').click(function () {
            jQuery('#popup1').removeClass('show');
        })
    });
</script>