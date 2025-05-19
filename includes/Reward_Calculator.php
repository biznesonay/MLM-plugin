<?php

require_once(ABSPATH . 'wp-config.php');

class Reward_Calculator
{

    public function calculation($price, $distributor_id)
    {
        global $wpdb;
        $sql1 = "SELECT * FROM {$wpdb->prefix}mlm_users where unique_id='" . $distributor_id . "'";
        $getSponsor = $wpdb->get_results($sql1);
        $getRank = get_user_rank($getSponsor[0]->sponsor_id);

        $insert_data = array('tran_user_id' => $distributor_id, 'amount' => $price, 'date' => strtotime("now"));
        $transuction = $wpdb->insert("{$wpdb->prefix}mlm_transactions", $insert_data);

        if ($getRank == 1) {
            $this->calculateFirstRank($distributor_id, $price);
        } elseif ($getRank == 2) {
            $this->calculateSecondRank($distributor_id, $price);
        } elseif ($getRank == 3) {
            $this->calculateThirdRank($distributor_id, $price);
        } elseif ($getRank == 4) {
            $this->calculateForthRank($distributor_id, $price);
        }
    }

    //TODO CHECK SR
    public function calculateFirstRank($distributor_id, $price = 0)
    {

        global $wpdb;
        $sponsor_id = get_sponsor_id($distributor_id);

        $getTotalCU = get_user_pcc($distributor_id);
        $getTotalCU = ($getTotalCU + $price);

        $prescc = get_user_scc($sponsor_id);
        $newscc = ($prescc + $price);

        $count = "SELECT COUNT(tran_user_id) as total_tran FROM {$wpdb->prefix}mlm_transactions WHERE tran_user_id='" . $distributor_id . "'";
        $ncount = $wpdb->get_results($count);


        $updatescc = array('scc' => $newscc);
        $conditionscc = array('mlm_user_id' => $sponsor_id);
        $wpdb->update("{$wpdb->prefix}mlm_rewards", $updatescc, $conditionscc);

        if ($getTotalCU == '') {
            $getTotalCU = 0;
            $update = array('rank' => 1);
            $condition = array('unique_id' => $distributor_id);
            $wpdb->update("{$wpdb->prefix}mlm_users", $update, $condition);
        }
        if ($getTotalCU >= 20000) {
            $update = array('rank' => 2);
            $condition = array('unique_id' => $distributor_id);
            $wpdb->update("{$wpdb->prefix}mlm_users", $update, $condition);
        }
        if ($getTotalCU < 20000) {
            $update = array('rank' => 1);
            $condition = array('unique_id' => $distributor_id);
            $wpdb->update("{$wpdb->prefix}mlm_users", $update, $condition);
        }

        $pcc = $getTotalCU;
        $percentToGet = 5;
        $percentInDecimal = $percentToGet / 100;
        $dr = $percentInDecimal * 5000;

        $sql2 = "SELECT * FROM {$wpdb->prefix}mlm_rewards where mlm_user_id='" . $sponsor_id . "'";
        $getParentdr = $wpdb->get_results($sql2);

        $getParentDr = $getParentdr[0]->dr;
        $newDr = ($dr + $getParentDr);

        if ($ncount[0]->total_tran < 2) {
            $update2 = array('dr' => $newDr);
            $condition2 = array('mlm_user_id' => $sponsor_id);
            $wpdb->update("{$wpdb->prefix}mlm_rewards", $update2, $condition2);
        }
        $update3 = array('pcc' => $pcc);
        $condition3 = array('mlm_user_id' => $distributor_id);
        $wpdb->update("{$wpdb->prefix}mlm_rewards", $update3, $condition3);

        $getParentParentID = get_sponsor_id($distributor_id);
        $getParent3ID = get_sponsor_id($getParentParentID);

        if ($getParentParentID != '') {
            $usRank = get_user_rank($getParentParentID);
            if ($usRank == 2) {
                $percentToGet1 = 3;
                $percentInDecimal1 = $percentToGet1 / 100;
                $sr = $percentInDecimal1 * $price;
                $presr = get_user_sr($getParentParentID);
                $totalsr = ($sr + $presr);

                $update4 = array('sr' => $totalsr);
                $condition4 = array('mlm_user_id' => $getParentParentID);
                $wpdb->update("{$wpdb->prefix}mlm_rewards", $update4, $condition4);
            }
            $children = get_mlm_children($getParentParentID);
            $totalpscc = 0;
            foreach ($children as $child) {
                $cssc = get_user_scc($child->unique_id);
                $cpcc = get_user_pcc($child->unique_id);
                $totalpscc = ($cssc + $cpcc) + $totalpscc;
            }
            $updatescc1 = array('scc' => $totalpscc);
            $conditionscc1 = array('mlm_user_id' => $getParentParentID);
            $wpdb->update("{$wpdb->prefix}mlm_rewards", $updatescc1, $conditionscc1);
        }

        if ($getParent3ID != '') {
            $P3Rank = get_user_rank($getParent3ID);
            $p2rank = get_user_rank($getParentParentID);
            if ($P3Rank != $p2rank) {

                if ($P3Rank == 3) {
                    if ($p2rank == 1) {
                        $percentToGet2 = 7;
                    } else {
                        $percentToGet2 = 4;
                    }
                    $percentInDecimal2 = $percentToGet2 / 100;
                    $sr1 = $percentInDecimal2 * $price;
                    $presr1 = get_user_sr($getParent3ID);
                    $totalsr1 = ($sr1 + $presr1);

                    $update5 = array('sr' => $totalsr1);
                    $condition5 = array('mlm_user_id' => $getParent3ID);
                    $wpdb->update("{$wpdb->prefix}mlm_rewards", $update5, $condition5);
                }
                if ($P3Rank == 2) {
                    $percentToGet2 = 3;
                    $percentInDecimal2 = $percentToGet2 / 100;
                    $sr1 = $percentInDecimal2 * $price;
                    $presr1 = get_user_sr($getParent3ID);
                    $totalsr1 = ($sr1 + $presr1);

                    $update5 = array('sr' => $totalsr1);
                    $condition5 = array('mlm_user_id' => $getParent3ID);
                    $wpdb->update("{$wpdb->prefix}mlm_rewards", $update5, $condition5);
                }
            }
            $children2 = get_mlm_children($getParent3ID);
            $totalpscc2 = 0;
            foreach ($children2 as $child2) {
                $cssc2 = get_user_scc($child2->unique_id);
                $cpcc2 = get_user_pcc($child2->unique_id);
                $totalpscc2 = ($cssc2 + $cpcc2) + $totalpscc2;
            }
            $updatescc2 = array('scc' => $totalpscc2);
            $conditionscc2 = array('mlm_user_id' => $getParent3ID);
            $wpdb->update("{$wpdb->prefix}mlm_rewards", $updatescc2, $conditionscc2);
        }
        $getParent4ID = get_sponsor_id($getParent3ID);

        if ($getParent4ID != '') {
            $P4Rank = get_user_rank($getParent4ID);
            $p3rank = get_user_rank($getParent3ID);
            if ($P4Rank != $p3rank) {

                if ($P4Rank == 3) {
                    if ($p3rank == 1) {
                        $percentToGet2 = 7;
                    } else {
                        $percentToGet2 = 3;
                    }
                    $percentInDecimal2 = $percentToGet2 / 100;
                    $sr1 = $percentInDecimal2 * $price;
                    $presr1 = get_user_sr($getParent4ID);
                    $totalsr1 = ($sr1 + $presr1);

                    $update5 = array('sr' => $totalsr1);
                    $condition5 = array('mlm_user_id' => $getParent4ID);
                    $wpdb->update("{$wpdb->prefix}mlm_rewards", $update5, $condition5);
                }
                if ($P4Rank == 2) {
                    $percentToGet2 = 3;
                    $percentInDecimal2 = $percentToGet2 / 100;
                    $sr1 = $percentInDecimal2 * $price;
                    $presr1 = get_user_sr($getParent4ID);
                    $totalsr1 = ($sr1 + $presr1);

                    $update5 = array('sr' => $totalsr1);
                    $condition5 = array('mlm_user_id' => $getParent4ID);
                    $wpdb->update("{$wpdb->prefix}mlm_rewards", $update5, $condition5);
                }
            }
            $children2 = get_mlm_children($getParent4ID);
            $totalpscc2 = 0;
            foreach ($children2 as $child2) {
                $cssc2 = get_user_scc($child2->unique_id);
                $cpcc2 = get_user_pcc($child2->unique_id);
                $totalpscc2 = ($cssc2 + $cpcc2) + $totalpscc2;
            }
            $updatescc2 = array('scc' => $totalpscc2);
            $conditionscc2 = array('mlm_user_id' => $getParent4ID);
            $wpdb->update("{$wpdb->prefix}mlm_rewards", $updatescc2, $conditionscc2);
        }


    }

    //TODO CHECK SR
    public function calculateSecondRank($distributor, $price = 0)
    {

        global $wpdb;

        $pcc = get_user_pcc($distributor);
        $pcc = ($pcc + $price);
        $percentToGet = 8;
        $percentInDecimal = $percentToGet / 100;
        $dr = $percentInDecimal * $price;

        $drsRank = get_user_rank($distributor);


        if ($drsRank == 0) {
            $updater = array('rank' => 1);
            $conditionr = array('unique_id' => $distributor);
            $wpdb->update("{$wpdb->prefix}mlm_users", $updater, $conditionr);
        }

        if ($pcc >= 20000) {
            $update = array('rank' => 2);
            $condition = array('unique_id' => $distributor);
            $wpdb->update("{$wpdb->prefix}mlm_users", $update, $condition);
        }

        if ($pcc == '') {
            $getTotalCU = 0;
            $update = array('rank' => 1);
            $condition = array('unique_id' => $distributor);
            $wpdb->update("{$wpdb->prefix}mlm_users", $update, $condition);
        }
        if ($pcc < 20000) {
            $update = array('rank' => 1);
            $condition = array('unique_id' => $distributor);
            $wpdb->update("{$wpdb->prefix}mlm_users", $update, $condition);
        }


        if ($price != 0 && $drsRank < 2) {
            $sponsor = get_sponsor_id($distributor);
            $sql2 = "SELECT * FROM {$wpdb->prefix}mlm_rewards where mlm_user_id='" . $sponsor . "'";
            $getParentdr = $wpdb->get_results($sql2);
            $getParentDr = $getParentdr[0]->dr;
            $newDr = ($dr + $getParentDr);
            $update2 = array('dr' => $newDr);
            $condition2 = array('mlm_user_id' => $sponsor);
            $wpdb->update("{$wpdb->prefix}mlm_rewards", $update2, $condition2);

            $update5 = array('pcc' => $pcc);
            $condition5 = array('mlm_user_id' => $distributor);
            $wpdb->update("{$wpdb->prefix}mlm_rewards", $update5, $condition5);
        }

        $Parent2ID = get_sponsor_id($distributor);
        $Parent3ID = get_sponsor_id($Parent2ID);

        $prescc = get_user_scc($Parent2ID);
        $newscc = ($prescc + $price);

        $updatescc = array('scc' => $newscc);
        $conditionscc = array('mlm_user_id' => $Parent2ID);
        $wpdb->update("{$wpdb->prefix}mlm_rewards", $updatescc, $conditionscc);

        if ($Parent3ID != '') {
            $p2rank = get_user_rank($Parent2ID);
            $p3rank = get_user_rank($Parent3ID);

            if ($p2rank < $p3rank) {
                $percentToGet1 = 4;
                $percentInDecimal1 = $percentToGet1 / 100;
                $sr = $percentInDecimal1 * $price;
                $presr = get_user_sr($Parent3ID);
                $totalsr = ($sr + $presr);
                if ($p3rank != $drsRank) {
                    $update4 = array('sr' => $totalsr);
                    $condition4 = array('mlm_user_id' => $Parent3ID);
                    $wpdb->update("{$wpdb->prefix}mlm_rewards", $update4, $condition4);
                }
                $update5 = array('pcc' => $pcc);
                $condition5 = array('mlm_user_id' => $distributor);
                $wpdb->update("{$wpdb->prefix}mlm_rewards", $update5, $condition5);
            }
            $children = get_mlm_children($Parent3ID);
            $totalpscc = 0;
            foreach ($children as $child) {
                $cssc = get_user_scc($child->unique_id);
                $cpcc = get_user_pcc($child->unique_id);
                $totalpscc = ($cssc + $cpcc) + $totalpscc;
            }
            $updatescc1 = array('scc' => $totalpscc);
            $conditionscc1 = array('mlm_user_id' => $Parent3ID);
            $wpdb->update("{$wpdb->prefix}mlm_rewards", $updatescc1, $conditionscc1);
        }
        $Parent4ID = get_sponsor_id($Parent3ID);

    }

    //TODO CHECK
    public function calculateThirdRank($distributor, $price = 0)
    {

        global $wpdb;

        $pcc = get_user_pcc($distributor);
        $pcc = ($pcc + $price);

        $percentToGet = 12;
        $percentInDecimal = $percentToGet / 100;
        $dr = $percentInDecimal * $price;

        $drsRank = get_user_rank($distributor);

        if ($drsRank == 0) {
            $updater = array('rank' => 1);
            $conditionr = array('unique_id' => $distributor);
            $wpdb->update("{$wpdb->prefix}mlm_users", $updater, $conditionr);
        }

        if ($pcc >= 20000) {
            $update = array('rank' => 2);
            $condition = array('unique_id' => $distributor);
            $wpdb->update("{$wpdb->prefix}mlm_users", $update, $condition);
        }

        if ($pcc >= 50000) {
            $update = array('rank' => 3);
            $condition = array('unique_id' => $distributor);
            $wpdb->update("{$wpdb->prefix}mlm_users", $update, $condition);
        }

        if ($price != 0 && $drsRank < 3) {
            $sponsor = get_sponsor_id($distributor);
            $sql2 = "SELECT * FROM {$wpdb->prefix}mlm_rewards where mlm_user_id='" . $sponsor . "'";
            $getParentdr = $wpdb->get_results($sql2);
            $getParentDr = $getParentdr[0]->dr;
            $newDr = ($dr + $getParentDr);
            $update2 = array('dr' => $newDr);
            $condition2 = array('mlm_user_id' => $sponsor);
            $wpdb->update("{$wpdb->prefix}mlm_rewards", $update2, $condition2);

            $update5 = array('pcc' => $pcc);
            $condition5 = array('mlm_user_id' => $distributor);
            $wpdb->update("{$wpdb->prefix}mlm_rewards", $update5, $condition5);
        }


        $Parent2ID = get_sponsor_id($distributor);
        $Parent3ID = get_sponsor_id($Parent2ID);

        if ($Parent3ID != '') {

            $p2rank = get_user_rank($Parent2ID);
            $p3rank = get_user_rank($Parent3ID);

            if ($p2rank < $p3rank) {
                $percentToGet1 = 3;
                $percentInDecimal1 = $percentToGet1 / 100;
                $sr = $percentInDecimal1 * $price;
                $presr = get_user_sr($Parent3ID);
                $totalsr = ($sr + $presr);
                if ($p3rank != $drsRank) {
                    $update4 = array('sr' => $totalsr);
                    $condition4 = array('mlm_user_id' => $Parent3ID);
                    $wpdb->update("{$wpdb->prefix}mlm_rewards", $update4, $condition4);
                }
                $update5 = array('pcc' => $pcc);
                $condition5 = array('mlm_user_id' => $distributor);
                $wpdb->update("{$wpdb->prefix}mlm_rewards", $update5, $condition5);
            }
            $children = get_mlm_children($Parent3ID);
            $totalpscc = 0;
            foreach ($children as $child) {
                $cssc = get_user_scc($child->unique_id);
                $cpcc = get_user_pcc($child->unique_id);
                $totalpscc = ($cssc + $cpcc) + $totalpscc;
            }
            $updatescc1 = array('scc' => $totalpscc);
            $conditionscc1 = array('mlm_user_id' => $Parent3ID);
            $wpdb->update("{$wpdb->prefix}mlm_rewards", $updatescc1, $conditionscc1);
        }
    }

    //TODO CHECK
    public function calculateForthRank($distributor, $price = 0)
    {

        global $wpdb;
        $pcc = get_user_pcc($distributor);
        $pcc = ($pcc + $price);

        $percentToGet = 15;
        $percentInDecimal = $percentToGet / 100;
        $dr = $percentInDecimal * $price;

        $drsRank = get_user_rank($distributor);

        if ($drsRank == 0) {
            $updater = array('rank' => 1);
            $conditionr = array('unique_id' => $distributor);
            $wpdb->update("{$wpdb->prefix}mlm_users", $updater, $conditionr);
        }

        if ($pcc >= 20000) {
            $update = array('rank' => 2);
            $condition = array('unique_id' => $distributor);
            $wpdb->update("{$wpdb->prefix}mlm_users", $update, $condition);
        }

        if ($pcc >= 50000) {
            $update = array('rank' => 3);
            $condition = array('unique_id' => $distributor);
            $wpdb->update("{$wpdb->prefix}mlm_users", $update, $condition);
        }


        if ($pcc >= 100000) {
            $update = array('rank' => 4);
            $condition = array('unique_id' => $distributor);
            $wpdb->update("{$wpdb->prefix}mlm_users", $update, $condition);
        }

        if ($price != 0 && $drsRank < 4) {
            $sponsor = get_sponsor_id($distributor);
            $sql2 = "SELECT * FROM {$wpdb->prefix}mlm_rewards where mlm_user_id='" . $sponsor . "'";
            $getParentdr = $wpdb->get_results($sql2);
            $getParentDr = $getParentdr[0]->dr;
            $newDr = ($dr + $getParentDr);
            $update2 = array('dr' => $newDr);
            $condition2 = array('mlm_user_id' => $sponsor);
            $wpdb->update("{$wpdb->prefix}mlm_rewards", $update2, $condition2);

            $update5 = array('pcc' => $pcc);
            $condition5 = array('mlm_user_id' => $distributor);
            $wpdb->update("{$wpdb->prefix}mlm_rewards", $update5, $condition5);
        }

        $Parent2ID = get_sponsor_id($distributor);
        $prescc = get_user_scc($Parent2ID);
        $newscc = ($prescc + $price);

        $updatescc = array('scc' => $newscc);
        $conditionscc = array('mlm_user_id' => $Parent2ID);
        $wpdb->update("{$wpdb->prefix}mlm_rewards", $updatescc, $conditionscc);
    }

    public function calculateFifthRank($distributor, $price = 0)
    {
        global $wpdb;
        $pcc = get_user_pcc($distributor);
        $pcc = ($pcc + $price);

        $percentToGet = 16;
        $percentInDecimal = $percentToGet / 100;
        $dr = $percentInDecimal * $pcc;

        $drsRank = get_user_rank($distributor);
    }
}

?>