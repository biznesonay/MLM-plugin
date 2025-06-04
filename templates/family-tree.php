<?php
$datatable = new Datatable_List();
$condition = "id = '".get_current_user_id()."'";
$user = $datatable->get_all_cond_data('mlm_users',$condition);
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
            <li>
                <a href="<?= get_admin_url() . 'admin.php?page=mlm-commodity-circulation-panel'; ?>">
                    <?php _e('Commodity Circulation panel', 'marketing') ?>
                </a>
            </li>
            <li>
                <a href="<?= get_admin_url() . 'admin.php?page=mlm-structure-panel'; ?>">
                    <?php _e('Structure panel', 'marketing'); ?>
                </a>
            </li>
            <li class="act">
                <a href="<?= get_admin_url() . 'admin.php?page=mlm-family-panel'; ?>">
                    <?php _e('Family panel', 'marketing'); ?>
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


    <div class="ttbcs">
        <div class="aaEbs">
            <form action="" method="GET">
                <input type="search" name="search_mlmuser" placeholder="<?php _e('Search User', 'marketing') ?>">
                <input type="hidden" name="page" value="mlm-family-panel">
                <input type="submit" value="<?php _e('Search', 'marketing') ?>">
            </form>
        </div>
    </div>

    <div id="family-tree" class="tavsect">
        <div class="eendtree_wrap">

            <div class="tree dsG">

                <ul>

                    <li>

                        <div class="family">

                            <div class="person child male mngs" style="background: #456990;color: #fff; margin-bottom: 10px">

                                <div class="name"><?php _e('Management', 'marketing') ?></div>

                            </div>

                            <div class="parent">
                                <?php if(isset($_GET['search_mlmuser'])){ ?>
                                    <div class="person female" style="background: #F45B69;color: #fff;">
                                        <?php $uid = $_GET['search_mlmuser']; ?>
                                        <div class="name"><?= get_user_name($uid); ?></div>
                                        <div class="das">
                                            <span><?= $uid; ?></span>
                                            <span><?= get_user_rank($uid); ?></span>
                                        </div>
                                    </div>
                                <?php }else{ ?>
                                    <div class="person female" style="background: #F45B69;color: #fff;">
                                        <div class="name"><?= $user[0]->user_name; ?></div>
                                        <div class="das">
                                            <?php $uid = $user[0]->unique_id; ?>
                                            <span><?= $uid; ?></span>
                                            <span><?= $user[0]->rank; ?></span>
                                        </div>
                                    </div>

                                <?php } ?>

                                <?php

                                $single = '';

                                $end = '';

                                ?>

                                <?php if(count(get_mlm_children($uid)) > 0){

                                    if(count(get_mlm_children($uid)) == 1){ $single = 'single_cl'; }

                                    ?>

                                    <ul>

                                        <?php foreach (get_mlm_children($uid) as $ndch) {

                                            if(count(get_mlm_children($ndch->unique_id)) == 0){ $end = 'endcl_cl'; }

                                            ?>

                                            <li>

                                                <div class="family <?php if($end != ''){ echo $single; } ?>">

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

                                                        <?php if(count(get_mlm_children($ndch->unique_id)) > 0){

                                                            if(count(get_mlm_children($ndch->unique_id)) == 1){ $single = 'single_cl'; }

                                                            ?>

                                                            <ul>

                                                                <?php foreach (get_mlm_children($ndch->unique_id) as $rdnc) {

                                                                    if(count(get_mlm_children($rdnc->unique_id)) == 0){ $end = 'endcl_cl'; }

                                                                    ?>

                                                                    <li>

                                                                        <div class="family <?php if($end != ''){ echo $single; } ?>">

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

                                                                                <?php if(count(get_mlm_children($rdnc->unique_id)) > 0){

                                                                                    if(count(get_mlm_children($rdnc->unique_id)) == 1){ $single = 'single_cl'; }

                                                                                    ?>

                                                                                    <ul>

                                                                                        <?php foreach (get_mlm_children($rdnc->unique_id) as $fortch) {

                                                                                            if(count(get_mlm_children($fortch->unique_id)) == 0){ $end = 'endcl_cl'; }

                                                                                            ?>

                                                                                            <li class="<?= count(get_mlm_children($fortch->unique_id)); ?>">

                                                                                                <div class="family <?php if($end != ''){ echo $single; } ?>">

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

                                                                                                        <?php if(count(get_mlm_children($fortch->unique_id)) > 0){

                                                                                                            if(count(get_mlm_children($fortch->unique_id)) == 1){ $single = 'single_cl'; }

                                                                                                            ?>

                                                                                                            <ul>

                                                                                                                <?php foreach (get_mlm_children($fortch->unique_id) as $fift) {

                                                                                                                    if(count(get_mlm_children($fift->unique_id)) == 0){ $end = 'endcl_cl'; }

                                                                                                                    ?>

                                                                                                                    <li>

                                                                                                                        <div class="family <?php if($end != ''){ echo $single; } ?>">

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

                                                                                                                                <?php if(count(get_mlm_children($fift->unique_id)) > 0){

                                                                                                                                    if(count(get_mlm_children($fift->unique_id)) == 1){ $single = 'single_cl'; } ?>

                                                                                                                                    <ul>

                                                                                                                                        <?php foreach (get_mlm_children($fift->unique_id) as $sisxth) {

                                                                                                                                            if(count(get_mlm_children($sisxth->unique_id)) == 0){ $end = 'endcl_cl'; } ?>

                                                                                                                                            <li>

                                                                                                                                                <div class="family <?php if($end != ''){ echo $single; } ?>">

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

                                                                                                                                                        <?php if(count(get_mlm_children($sisxth->unique_id)) > 0){

                                                                                                                                                            if(count(get_mlm_children($sisxth->unique_id)) == 1){ $single = 'single_cl'; } ?>

                                                                                                                                                            <ul>

                                                                                                                                                                <?php foreach (get_mlm_children($sisxth->unique_id) as $sevnth) {

                                                                                                                                                                    if(count(get_mlm_children($sevnth->unique_id)) == 0){ $end = 'endcl_cl'; }

                                                                                                                                                                    ?>

                                                                                                                                                                    <li>

                                                                                                                                                                        <div class="family <?php if($end != ''){ echo $single; } ?>">

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

                                                                                                                                                                                <?php if(count(get_mlm_children($sevnth->unique_id)) > 0){

                                                                                                                                                                                    if(count(get_mlm_children($sevnth->unique_id)) == 1){ $single = 'single_cl'; } ?>

                                                                                                                                                                                    <ul>

                                                                                                                                                                                        <?php foreach (get_mlm_children($sevnth->unique_id) as $eigth) {

                                                                                                                                                                                            if(count(get_mlm_children($eigth->unique_id)) == 0){ $end = 'endcl_cl'; }

                                                                                                                                                                                            ?>

                                                                                                                                                                                            <li>

                                                                                                                                                                                                <div class="family <?php if($end != ''){ echo $single; } ?>">

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

                                                                                                                                                                                                        <?php if(count(get_mlm_children($eigth->unique_id)) > 0){

                                                                                                                                                                                                            if(count(get_mlm_children($eigth->unique_id)) == 1){ $single = 'single_cl'; } ?>

                                                                                                                                                                                                            <ul>

                                                                                                                                                                                                                <?php foreach (get_mlm_children($eigth->unique_id) as $ninth) {

                                                                                                                                                                                                                    if(count(get_mlm_children($ninth->unique_id)) == 0){ $end = 'endcl_cl'; } ?>

                                                                                                                                                                                                                    <li>

                                                                                                                                                                                                                        <div class="family <?php if($end != ''){ echo $single; } ?>">

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
    </div>
</div>
