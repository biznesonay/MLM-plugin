<?php
/**
 * Created by PhpStorm.
 * User: zh.zhumagali
 * Date: 02.10.2020
 * Time: 18:01
 */

class TransactionParser
{
    public static function parse($transactions)
    {
        $result = ['status' => false, 'count' => 0];

        if (!$transactions) return $result;

        foreach ($transactions as $tr) {
            $balance = (float)$tr['amount'];
            $parents = RankDB::getUserPatents($tr['tran_user_id']);

            $scc = self::setUsersScc($balance, $parents);

            $result['status'] = $scc ? true : false;
            if ($scc) $result['count'] += 1;
        }

        return $result;
    }

    protected static function setUsersScc(float $balance, array $parents)
    {
        if (!$balance || !$parents) return false;
        $status = false;

        foreach ($parents as $parent) {
            $setSccBalance = $balance + (float)$parent['scc_second'];
            if ($setSccBalance) {
                $status = RankDB::saveRewardByCondition($parent['unique_id'], ['scc_second' => $setSccBalance]);
            }
        }

        return $status;
    }
}