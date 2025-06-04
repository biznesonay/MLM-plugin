<?php

if (!is_user_logged_in()) {
    return false;
}
$userId = get_current_user_id();
$datatable = new Datatable_List();
$condition = "user_id = '" . get_current_user_id() . "'";
$user = $datatable->get_all_cond_data('mlm_users', $condition);
$condition2 = "mlm_user_id='" . $user[0]->unique_id . "'";
$reword = $datatable->get_all_cond_data('mlm_rewards', $condition2);
$users = UserTree::getUserChildren('USER' . $userId);
// Изменено: добавлен параметр для фильтрации по текущему пользователю
$rank = $datatable->getAllUserRank('USER' . $userId);
$reward = $datatable->getUserRewardNotification('USER' . $userId)
?>

<style>
    .HGGu_login {
        border: 1px solid #e3e3e3;
    }

    .dataTables_wrapper {
        padding: 20px;
    }

    .profile {
        display: block;
        width: 100%;
        padding: 20px;
    }

    .user-info {
        margin: 10px 0 10px 0;
    }

    #tree-container {
        height: 600px;
        width: 100%;
        overflow: auto;
        border: 1px solid #e3e3e3;
        position: relative;
        background-color: #f9f9f9;
    }

    @media only screen and (max-width: 533px) {
        .entry-content h1, h1 {
            font-size: 14px;
        }
        div, a, th, td, input, input[readonly], label, a {
            font-size: 10px;
        }
        .HGGu_login {
            padding: 10px;
        }
        .profile {
            padding: 5px;
        }

        .dataTables_wrapper {
            padding: 5px;
        }

        #tree-container {
            padding: 5px;
            height: 400px;
        }
    }
</style>

<div class="profile">
    <?php if ($reward) : ?>
        <p style="color: red; text-align: center; font-weight: bold; font-size: 22px"><?= $reward ?></p>
    <?php endif; ?>

    <div class="user-info HGGu_login">

        <h1><?php _e('User Profile', 'marketing') ?></h1>

        <form class="form_cla_login">
            <div class="dbhd">
                <label><?php _e('Name', 'marketing') ?></label>
                <input type="text" name="us_user_name" value="<?= $user[0]->user_name; ?>" readonly>
                <span>
                    <i class="fa fa-pencil-square-o edtshowName"></i>
                    <i class="fa fa-check disnon chkhidName"></i>
                </span>
            </div>

            <label><?php _e('User id', 'marketing') ?></label>
            <a class="disable"><?= $user[0]->unique_id; ?></a>

            <label><?php _e('Phone', 'marketing') ?></label>
            <a class="disable"><?= $user[0]->user_phone; ?></a>

            <input type="hidden" name="login_user_id" value="<?= get_current_user_id(); ?>">

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
        </form>
    </div>

    <div class="personal-transactions">
        <h1><?php _e('Personal Transaction Table', 'marketing') ?></h1>

        <table id="personal-transaction" class="ui celled table" style="width: 100%">
            <thead>
            <tr>
                <th><?php _e('Sl no.', 'marketing') ?></th>
                <th><?php _e('Amount', 'marketing') ?></th>
                <th><?php _e('Transaction Date', 'marketing') ?></th>
            </tr>
            </thead>

            <tfoot>
            <tr>
                <th><?php _e('Sl no.', 'marketing') ?></th>
                <th><?php _e('Amount', 'marketing') ?></th>
                <th><?php _e('Transaction Date', 'marketing') ?></th>
            </tr>
            </tfoot>
        </table>
    </div>

    <div class="rewards-history">
        <h1><?php _e('Rewards history', 'marketing') ?></h1>

        <table id="rewards-history" class="ui celled table" style="width: 100%">
            <thead>
            <tr>
                <th><?php _e('Sl no', 'marketing') ?>.</th>
                <th><?php _e('Payout Rewards', 'marketing') ?></th>
                <th><?php _e('Payout Date and Time', 'marketing') ?></th>
                <th><?php _e('After account balance', 'marketing'); ?></th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <th><?php _e('Sl no', 'marketing') ?>.</th>
                <th><?php _e('Payout Rewards', 'marketing') ?></th>
                <th><?php _e('Payout Date and Time', 'marketing') ?></th>
                <th><?php _e('After account balance', 'marketing'); ?></th>
            </tr>
            </tfoot>
        </table>
    </div>

    <div class="rank">
        <h1><?php _e('Date of Rank\'s change', 'marketing') ?></h1>

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
            $i = 1;
            foreach ($rank as $k => $item) {  
                $date = new DateTime($item['created_at']);
                $date->setTimezone($timezone);
            ?>
                <tr id="trr<?= $item['id']; ?>">
                    <td><?= $k+1; ?></td>
                    <td><?= $item['unique_id']; ?></td>
                    <td><?= $item['user_name']; ?></td>
                    <td><?= $item['rank_id']; ?></td>
                    <td><?= $date->format('F j, Y H:i:s') ?></td>
                </tr>
                <?php $i++; } ?>
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

    <div class="user-child">
        <h1><?php _e('Family Panel', 'marketing') ?></h1>
        <div id="tree-container"></div>
    </div>
</div>


<script>
    const adminAjax = "<?= admin_url('admin-ajax.php'); ?>";

    document.addEventListener("DOMContentLoaded",function () {
        // Часовой пояс Алматы
        const almatyTimeZone = { timeZone: 'Asia/Almaty' };
        
        function addZero(strNumber) {
            return (strNumber < 10 ? "0" + strNumber : strNumber);
        }

        function dateFormat(timestamp) {
            const date = new Date(timestamp * 1000);
            // Форматируем дату для часового пояса Алматы
            const options = {
                ...almatyTimeZone,
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            };
            
            const parts = new Intl.DateTimeFormat('en-US', options).formatToParts(date);
            const values = {};
            parts.forEach(part => {
                values[part.type] = part.value;
            });
            
            return `${values.day}.${values.month}.${values.year} ${values.hour}:${values.minute}:${values.second}`;
        }

        const id = jQuery('input[name="login_user_id"]').val();

        jQuery('.edtshowName').click(function () {
            jQuery('input[name="us_user_name"]').removeAttr('readonly');
            jQuery('.chkhidName').removeClass('disnon');
            jQuery(this).addClass('disnon');
        });

        jQuery('.chkhidName').click(function () {
            var name = $('input[name="us_user_name"]').val();
            $('.prespinner').css('display', 'block');
            $.ajax({
                type: "POST",
                url: adminAjax,
                data: {'action': "update_data", 'us_item': 'name', 'us_value': name, 'us_user_id': id},
                success: function (response) {
                    $('.prespinner').css('display', 'none');
                    $('input[name="us_user_name"]').attr('readonly', 'readonly');
                    $('.edtshowName').removeClass('disnon');
                    $('.chkhidName').addClass('disnon');
                }
            });
        })

        jQuery('#personal-transaction').DataTable({
            processing: true,
            serverSide: true,
            ordering: true,
            order: [[0, 'desc']],
            serverMethod: 'post',
            ajax: {
                'url': adminAjax + '?action=transactions'
            },
            columns: [
                {data: 'id', orderable: true},
                {data: 'amount', orderable: true},
                {
                    data: 'date',
                    orderable: true,
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return dateFormat(data);
                        }
                        return data;
                    }
                },
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

        jQuery('#rewards-history').DataTable({
            processing: true,
            serverSide: true,
            ordering: true,
            order: [[0, 'desc']],
            serverMethod: 'post',
            ajax: {
                'url': adminAjax + '?action=reward_history'
            },
            columns: [
                {data: 'id', orderable: true},
                {data: 'amount', orderable: true},
                {data: 'after_rewords_balance', orderable: true},
                {data: 'created_at', orderable: true},
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

        // DataTable для таблицы рангов - не серверная
        jQuery('#rank').DataTable({
            ordering: true,
            order: [[0, 'desc']],
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

    const users = <?= json_encode($users) ?>;
    let treeList;

    const idMapping = users.reduce((acc, el, i) => {
        acc[el.id] = i;
        return acc;
    }, {});

    users.forEach(el => {
        // Handle the root element
        if (el.sponsor_id === null || el.sponsor_id == 0) {
            treeList = el;
            return;
        }
        // Use our mapping to locate the parent element in our data array
        const parentEl = users[idMapping[el.sponsor_id]];
        // Add our current el to its parent's `children` array
        parentEl.children = [...(parentEl.children || []), el];
    });

    const treeData = treeList;

    // Переопределяем размеры для дерева после загрузки dndTree.js
    jQuery(window).on('load', function() {
        setTimeout(function() {
            // Получаем размеры контейнера
            var containerEl = document.getElementById('tree-container');
            if (!containerEl) return;
            
            var containerWidth = containerEl.offsetWidth;
            var containerHeight = 600;
            
            // Обновляем размеры viewer для dndTree
            if (typeof viewerWidth !== 'undefined' && typeof viewerHeight !== 'undefined') {
                viewerWidth = containerWidth;
                viewerHeight = containerHeight;
            }
            
            // Пересоздаем дерево с новыми размерами
            if (typeof tree !== 'undefined' && typeof root !== 'undefined') {
                tree.size([containerHeight - 100, containerWidth - 200]);
                
                // Обновляем и центрируем
                if (typeof update === 'function' && typeof centerNode === 'function') {
                    update(root);
                    
                    // Центрируем корневой узел
                    var scale = 1;
                    var x = -root.y0;
                    var y = -root.x0;
                    x = x * scale + containerWidth / 2;
                    y = y * scale + containerHeight / 2;
                    
                    d3.select("#tree-container svg g").transition()
                        .duration(750)
                        .attr("transform", "translate(" + x + "," + y + ")scale(" + scale + ")");
                    
                    if (typeof zoomListener !== 'undefined') {
                        zoomListener.scale(scale);
                        zoomListener.translate([x, y]);
                    }
                }
            }
        }, 1500); // Даем время загрузиться dndTree.js
    });
</script>