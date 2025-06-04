<?php
/**
 * Created by PhpStorm.
 * User: zh.zhumagali
 * Date: 02.10.2020
 * Time: 22:13
 */

$users = UserTree::getUsers();

?>
<div class="search-flter">
    <div class="ttbcs">
        <div class="aaEbs">
            <form action="#" method="GET" id="user-form">
                <input type="hidden" name="page" value="mlm-tree-panel">
                <input type="text" name="user-name" placeholder="<?php _e('Search User', 'marketing') ?>">
                <input type="submit" value="<?php _e('Search', 'marketing') ?>">
            </form>
        </div>
    </div>
</div>

<div id="tree-container"></div>

<style>
    #tree-container {
        width: 100%;
        overflow-x: hidden;
    }
</style>

<!-- Объект с переводами для JavaScript -->
<script>
var mlm_translations = {
    rewards: '<?php _e('Rewards', 'marketing'); ?>',
    search_user: '<?php _e('Search User', 'marketing'); ?>',
    pcc_scc: '<?php _e('PCC+SCC', 'marketing'); ?>',
    dr: '<?php _e('DR', 'marketing'); ?>',
    sr: '<?php _e('SR', 'marketing'); ?>',
    mr: '<?php _e('MR', 'marketing'); ?>',
    error: '<?php _e('Error', 'marketing'); ?>',
    success: '<?php _e('Success', 'marketing'); ?>',
    loading: '<?php _e('Loading...', 'marketing'); ?>'
};
</script>

<script>
    const users = <?= json_encode($users) ?>;
    let treeList;


    const idMapping = users.reduce((acc, el, i) => {
        acc[el.id] = i;
        return acc;
    }, {});

    users.forEach(el => {
        // Handle the root element
        if (el.sponsor_id === null) {
            treeList = el;
            return;
        }
        // Use our mapping to locate the parent element in our data array
        const parentEl = users[idMapping[el.sponsor_id]];
        // Add our current el to its parent's `children` array
        parentEl.children = [...(parentEl.children || []), el];
    });


    const treeData = treeList;

    jQuery("#user-form").submit(function (e) {
        e.preventDefault();
        const user = $('input[name="user-name"]').val();
        if (!user) return false;
        jQuery(".node text").each(function () {
            const item = jQuery(this).text();
            // if (item.indexOf(user) > -1) {
            if (item.search(new RegExp(user,'gi')) != -1) {
                // const firstChildXY = jQuery(this).parent('g').attr('transform');
                jQuery(this).attr('fill', 'red');
                // jQuery('svg > g').attr('transform', firstChildXY + 'scale(2)');
            } else {
                jQuery(this).removeAttr('fill');
            }
        });
    });

    jQuery(document).ready(function () {
        // jQuery(document).on("mousedown", ".node", function(e) {
        jQuery(document).on("contextmenu", ".node", function(e) {
            e.preventDefault();
            const userName = jQuery(this).text();

            jQuery.ajax({
                type: "GET",
                url: "<?= admin_url('admin-ajax.php'); ?>",
                data: {'action': "get_user_reward", 'user': userName},
                success: function (r) {
                    r = JSON.parse(r);
                    const amount = parseFloat(r.pcc) + parseFloat(r.scc);
                    const text = mlm_translations.pcc_scc + " = " + amount + 
                            '; ' + mlm_translations.dr + ' = ' + r.dr + 
                            '; ' + mlm_translations.sr + ' = ' + r.sr + 
                            '; ' + mlm_translations.mr + ' = ' + r.mr;
                    Swal.fire({
                        // icon: 'success',
                        // position: 'top-end',
                        title: mlm_translations.rewards,
                        text: text
                    });
                }
            });
        });
    });
</script>
