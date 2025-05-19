<?php

class RankReward
{
    public function calculate($balance, $userUniqueId): array
    {
        $result = ['status' => false, 'message' => translate('Error to create')];
        $curUserWithReward = RankDB::geUserRankWithReward($userUniqueId);
        $userRank = $curUserWithReward ? (int)$curUserWithReward['rank'] : 0;
        $clearBalance = $balance;
        $balance = $curUserWithReward ? $curUserWithReward['pcc'] + $balance : $balance;

        $parents = RankDB::getUserPatents($userUniqueId);
        $children = RankDB::getUserChildren($userUniqueId);
        $childrenFirstHashArray = RankHelper::firstLevelRankHashArray($children);

        $curUserScc = $curUserWithReward ? $curUserWithReward['scc'] : 0.0;

        $rank = $this->getRank($userRank, $balance, $curUserScc, $childrenFirstHashArray);

        if ($rank && $userRank != $rank) {
            $user = RankDB::getUserByUniqueId($userUniqueId);

            $bonusPccScc = (float)$curUserWithReward['pcc'] + (float)$curUserWithReward['scc'];
            $userId = $user['user_id'] ?? null;

            RankDB::saveUserRank($userUniqueId, $rank, $userId, $bonusPccScc);
        }

        $status = RankDB::createTransaction($userUniqueId, $clearBalance);

        if ($status) {
            RankDB::saveRewardByCondition($userUniqueId, ['pcc' => $balance]);
            $createdScc = $this->setUsersScc($userUniqueId, $clearBalance, $parents);
//            if ($createdScc) $this->saveSccAt($parents); // need to Br rank
            $this->createRewardDr($userRank, $parents, $clearBalance);
            $this->createRewardSr($parents, $clearBalance, $curUserWithReward);
            $this->createRewardMr($userRank, $parents, $clearBalance);
//            $this->calculateBr($userUniqueId);
            $this->calculateBrAndBrCar($userUniqueId, $userRank);

            $result['status'] = true;
            $result['message'] = translate('Successfully created');
        }

        return $result;
    }

    public function calculateRank(string $userUniqueId): bool
    {
        $status = false;
        $curUserWithReward = RankDB::geUserRankWithReward($userUniqueId);
        $userRank = $curUserWithReward ? (int)$curUserWithReward['rank'] : 0;
        $balance = $curUserWithReward ? $curUserWithReward['pcc'] : 0;
        $curUserScc = $curUserWithReward ? $curUserWithReward['scc'] : 0.0;

        $children = RankDB::getUserChildren($userUniqueId);
        $childrenFirstHashArray = RankHelper::firstLevelRankHashArray($children);

        $rank = $this->getRankIncrement($userRank, $balance, $curUserScc, $childrenFirstHashArray);

        if ($rank && $userRank != $rank) {
            $bonusPccScc = (float)$balance + (float)$curUserScc;
            $status = RankDB::saveUserRank($userUniqueId, $rank, $curUserWithReward['user_id'], $bonusPccScc);
        }

        return $status;
    }

    protected function getRankIncrement($userRank, $balance, $curUserScc, $childrenFirstHashArray): int
    {
        if ($userRank == 0) {
            return $this->calculateFirstRank($userRank, $balance);
        }
        if ($userRank == 1) {
            return $this->calculateSecondRank($userRank, $balance);
        }
        if ($userRank == 2) {
            return $this->calculateThirdRank($userRank, $balance);
        }
        if ($userRank == 3) {
            return $this->calculateFourthRank($userRank, $balance);
        }
        if ($userRank == 4) {
            return $this->calculateFifthRank($userRank, $balance, $curUserScc, $childrenFirstHashArray);
        }
        if ($userRank == 5) {
            return $this->calculateSixthRank($userRank, $balance, $curUserScc, $childrenFirstHashArray);
        }
        if ($userRank == 6) {
            return $this->calculateSeventhRank($userRank, $balance, $curUserScc, $childrenFirstHashArray);
        }
        if ($userRank == 7) {
            return $this->calculateEightRank($userRank, $balance, $curUserScc, $childrenFirstHashArray);
        }
        if ($userRank == 8) {
            return $this->calculateNinthRank($userRank, $balance, $curUserScc, $childrenFirstHashArray);
        }

        return 0;
    }

    protected function getRank($userRank, $balance, $curUserScc, $childrenFirstHashArray): int
    {
        $rank = 0;

        if ($calkRank = $this->calculateFirstRank($userRank, $balance)) {
            $rank = $calkRank;
        }
        if ($calkRank = $this->calculateSecondRank($userRank, $balance)) {
            $rank = $calkRank;
        }
        if ($calkRank = $this->calculateThirdRank($userRank, $balance)) {
            $rank = $calkRank;
        }
        if ($calkRank = $this->calculateFourthRank($userRank, $balance)) {
            $rank = $calkRank;
        }
        if ($calkRank = $this->calculateFifthRank($userRank, $balance, $curUserScc, $childrenFirstHashArray)) {
            $rank = $calkRank;
        }
        if ($calkRank = $this->calculateSixthRank($userRank, $balance, $curUserScc, $childrenFirstHashArray)) {
            $rank = $calkRank;
        }
        if ($calkRank = $this->calculateSeventhRank($userRank, $balance, $curUserScc, $childrenFirstHashArray)) {
            $rank = $calkRank;
        }
        if ($calkRank = $this->calculateEightRank($userRank, $balance, $curUserScc, $childrenFirstHashArray)) {
            $rank = $calkRank;
        }
        if ($calkRank = $this->calculateNinthRank($userRank, $balance, $curUserScc, $childrenFirstHashArray)) {
            $rank = $calkRank;
        }

        return $rank;
    }

    protected function calculateFirstRank(int $userRank, float $rewardPcc)
    {
        $rank = 0;

        if ($userRank < 5 && $rewardPcc >= 7000) {
            $rank = 1;
        }

        return $rank;
    }

    protected function calculateSecondRank(int $userRank, float $rewardPcc)
    {
        $rank = 0;

        if ($userRank < 5 && $rewardPcc >= 25000) {
            $rank = 2;
        }

        return $rank;
    }

    protected function calculateThirdRank(int $userRank, float $rewardPcc)
    {
        $rank = 0;

        if ($userRank < 5 && $rewardPcc >= 70000) {
            $rank = 3;
        }

        return $rank;
    }

    protected function calculateFourthRank(int $userRank, float $rewardPcc)
    {
        $rank = 0;

        if ($userRank < 5 && $rewardPcc >= 100000) {
            $rank = 4;
        }

        return $rank;
    }

    protected function calculateFifthRank(int $userRank, float $rewardPcc, float $rewardScc, array $children)
    {
        $rank = 0;
        $fourthRankUserCount = 0;

        if ($userRank == 4 && $rewardPcc >= 100000 && ($rewardPcc + $rewardScc) >= 500000) {

            if (!$children) return $rank;

            foreach ($children as $child) {
                if ($child[4] ?? null) {
                    $fourthRankUserCount++;
                }
            }
            if ($fourthRankUserCount >= 2) {
                $rank = 5;
            }
        }

        return $rank;
    }

    protected function calculateSixthRank(int $userRank, float $rewardPcc, float $rewardScc, array $children)
    {
        $rank = 0;

        if ($userRank == 5 && $rewardPcc >= 100000 && ($rewardPcc + $rewardScc) >= 2000000) {
            $rank = $this->getNeedRankInFirstHashArray($children, 5, 4, 6);
        }

        return $rank;
    }

    protected function calculateSeventhRank(int $userRank, float $rewardPcc, float $rewardScc, array $children)
    {
        $rank = 0;

        if ($userRank == 6 && $rewardPcc >= 100000 && ($rewardPcc + $rewardScc) >= 10000000) {
            $rank = $this->getNeedRankInFirstHashArray($children, 6, 5, 7);
        }

        return $rank;
    }

    protected function calculateEightRank(int $userRank, float $rewardPcc, float $rewardScc, array $children)
    {
        $rank = 0;

        if ($userRank == 7 && $rewardPcc >= 100000 && ($rewardPcc + $rewardScc) >= 40000000) {
            $rank = $this->getNeedRankInFirstHashArray($children, 7, 6, 8);
        }

        return $rank;
    }

    protected function calculateNinthRank(int $userRank, float $rewardPcc, float $rewardScc, array $children)
    {
        $rank = 0;
        if ($userRank == 8 && $rewardPcc >= 100000 && ($rewardPcc + $rewardScc) >= 150000000) {
            var_dump($userRank. ' pcc =' . $rewardPcc . ' all = ' . ($rewardPcc + $rewardScc) );

            $rank = $this->getNeedRankInFirstHashArray($children, 8, 7, 9);
        }

        return $rank;
    }

    protected function setUsersScc($currentUser, $balance, array $users)
    {
        if (!$users) return false;
        $status = false;
        foreach ($users as $userItems) {
            $setSccBalance = $balance + (float)$userItems['scc'];
            $status = RankDB::saveRewardByCondition($userItems['unique_id'], ['scc' => $setSccBalance]);
        }

        return $status;
    }

    protected function getNeedRankInFirstHashArray(array $children, $needRankFirst, $needRankSecond, $returnRank)
    {
        $rank = 0;
        if (!$children) return $rank;

        $needRankFirstCount = 0;
        $needRankSecondCount = 0;

        foreach ($children as $child) {
            if ($child[$needRankFirst] ?? null) {
                $needRankFirstCount++;
            }
            if ($child[$needRankSecond] ?? null) {
                $needRankSecondCount++;
            }
        }

        if ($needRankFirstCount >= 2 || ($needRankFirstCount && $needRankSecondCount >= 6)) {
            $rank = $returnRank;
        }

        return $rank;
    }

    protected function saveSccAt(array $parents)
    {
        $status = false;
        //todo all parents rank > 6
        if ($parents) {
            foreach ($parents as $parent) {
                if ($parent['rank'] > 6) {
                    $status = RankDB::saveRewardByCondition($parent['unique_id'], ['scc_at' => date('Y-m-d H:i:s')]);
                }
            }
        }

        return $status;
    }

    protected function createRewardDr(int $setUserRank, array $parents, float $clearBalance)
    {
        $status = false;
        $closerFirstParent = $parents ? end($parents) : false;
        $parentUniqueId = $closerFirstParent ? $closerFirstParent['unique_id'] : null;
        $parentRank = $closerFirstParent ? (int)$closerFirstParent['rank'] : 0;
        $prevParentRewardDr = $closerFirstParent ? (float)$closerFirstParent['dr'] : 0;

        $curUserRewardDr = Reward::countDr($setUserRank, $parentRank, $clearBalance);
        $rewardDr = $prevParentRewardDr + $curUserRewardDr;

        if ($parentUniqueId) {
            $status = RankDB::saveRewardByCondition($parentUniqueId, ['dr' => $rewardDr]);
        }
        return $status;
    }

    protected function createRewardSr(array $parents, float $clearBalance, array $curUserWithReward)
    {
        $status = false;
        $lastParent = end($parents);
        if (!$lastParent) {
            $lastParent['unique_id'] = null;
        }
        if ($parents) {
            $parents[] = $curUserWithReward;
        }
        $usersRewardSr = Reward::countSr($clearBalance, $parents, $lastParent);
        if ($usersRewardSr) {
            foreach ($usersRewardSr as $parentId => $item) {
                $rewardSr = $item['pre_sr'] + $item['sr'];
                RankDB::saveRewardByCondition($parentId, ['sr' => $rewardSr]);
            }
        }

        return $status;
    }

    protected function createRewardMr(int $setUserRank, array $parents, float $clearBalance)
    {
        $status = false;
        //array_pop($parents);
        $parents = array_reverse($parents);
        $mrWithParent = Reward::countMr($setUserRank, $clearBalance, $parents);

        if ($mrWithParent['mr'] && $mrWithParent['parent']) {
            $parentUniqueId = $mrWithParent['parent']['data']['unique_id'] ?? null;
            $parentRewardMr = $mrWithParent['parent']['data']['mr'] ? (float)$mrWithParent['parent']['data']['mr'] : 0;
            $rewardMr = $parentRewardMr + $mrWithParent['mr'];

            if ($parentUniqueId) {
                $status = RankDB::saveRewardByCondition($parentUniqueId, ['mr' => $rewardMr]);
            }
        }
        return $status;
    }

    protected function calculateBr(string $userId)
    {
        $status = false;
        $reward = RankDB::getUserReward($userId);

        $startDateUnix = $reward && $reward['br_start_at'] ? \DateTime::createFromFormat('Y-m-d H:i:s', $reward['br_start_at'])->format('Y-m-d') : null;
        $endDateUnix = $reward && $reward['scc_at'] ? \DateTime::createFromFormat('Y-m-d H:i:s', $reward['scc_at'])->format('Y-m-d') : null;
        $userPrevBrBalance = $reward && $reward['prev_br_balance'] ? (float)$reward['prev_br_balance'] : 0.0;

        if (!$startDateUnix) {
            $transaction = RankDB::getUserFirstTransaction($userId);
            if ($transaction) {
                $startDateUnix = date('Y-m-d', $transaction['date']);
                RankDB::saveRewardByCondition($userId, ['br_start_at' => date('Y-m-d H:i:s', $transaction['date'])]);
            }
        }

        if ($startDateUnix && $endDateUnix) {
            $startDate = new DateTime($startDateUnix);
            $endDate = new DateTime($endDateUnix);
            $days = $endDate->diff($startDate)->format("%d");

            if ($days <= 112) {
                $allChildren = RankDB::getUserWithChildrenTree($userId);
                $usersId = "'{$userId}'";
                if ($child = $this->getParentsUniqueIdString($allChildren)) $usersId .= ', ' . $child;
                $totalBalance = RankDB::getTransactionSumAmount($usersId, $startDateUnix, $endDateUnix);
                $totalBalance = $totalBalance - $userPrevBrBalance;

                if ($totalBalance >= 4000000) {
                    $status = RankDB::saveRewardByCondition($userId, ['br' => 1, 'prev_br_balance' => $totalBalance]);
                    $newStartDate = new DateTime();
                    $newStartDate->modify('+1 day');
                    $newStartDate = $newStartDate->format('Y-m-d H:i:s');
                    RankDB::saveRewardByCondition($userId, ['br_start_at' => $newStartDate]);
                }
            } else {
                $transaction = RankDB::getUserNextTransactionByDate($userId, $startDateUnix);
                $newStartDate = $transaction ? date('Y-m-d H:i:s', $transaction['date']) : null;
                if ($newStartDate) {
                    $status = RankDB::saveRewardByCondition($userId, ['br_start_at' => $newStartDate]);
                }
            }
        }

        return $status;
    }

    protected function calculateBrAndBrCar(string $userId, $rank)
    {
        $status = false;
        $reward = RankDB::getUserReward($userId);
        $userRank = RankDB::getUserRank($userId, $rank);
        $startDateUnix = $userRank && $userRank['created_at'] ? \DateTime::createFromFormat('Y-m-d H:i:s', $userRank['created_at'])->format('Y-m-d') : null;
//        $startDateUnix = $reward && $reward['br_start_at'] ? \DateTime::createFromFormat('Y-m-d H:i:s', $reward['br_start_at'])->format('Y-m-d') : null;
        $userPrevBrBalance = isset($userRank['pcc_scc']) && $userRank['pcc_scc'] ? (float)$userRank['pcc_scc'] : 0.0;
//        $userPrevBrBalance = $reward && $reward['prev_br_balance'] ? (float)$reward['prev_br_balance'] : 0.0;

//        if (!$startDateUnix) {
//            $transaction = RankDB::getUserFirstTransaction($userId);
//            if ($transaction) {
//                $startDateUnix = date('Y-m-d', $transaction['date']);
//                RankDB::saveRewardByCondition($userId, ['br_start_at' => date('Y-m-d H:i:s', $transaction['date'])]);
//            }
//        }

        if (!$startDateUnix) {
            return false;
        }

        $startDate = new DateTime($startDateUnix);
        $endDate = new DateTime();
        $days = $endDate->diff($startDate)->format("%d");
        $totalBalance = (float)$reward['pcc'] + (float)$reward['scc'];
        $totalBalance = $totalBalance - $userPrevBrBalance;
        $newBrBalance = $totalBalance + $userPrevBrBalance;

        // Бонусное вознаграждение (BR) уведомление
        if ($days <= 84) {
            $message = null;

            if ($rank == 7 && $totalBalance >= 3000000) {
                $message = 'У Вас есть шанс выиграть путевку на сумму 400 тысяч тенге! Осталось набрать: 4млн минус существующая сумма PCC+SCC тенге!';
            } elseif ($rank == 8 && $totalBalance >= 8000000) {
                $message = 'У Вас есть шанс выиграть путевку на сумму 1 миллион тенге! Осталось набрать: 10млн минус существующая сумма PCC+SCC тенге!';
            } elseif ($rank == 9 && $totalBalance >= 15000000) {
                $message = 'У Вас есть шанс выиграть путевку на сумму 2 миллиона тенге! Осталось набрать: 20млн минус существующая сумма PCC+SCC тенге!';
            }

            if ($message) {
                RankDB::createBrNotification($userId, 4000000, $message);
            }
        }

        // Бонусное вознаграждение машины
        if ($rank == 9 && $days <= 168) {
            if ($totalBalance >= 40000000) {
                $status = RankDB::saveRewardByCondition($userId, ['br_car' => 1, 'prev_br_balance' => $newBrBalance]);
                $this->moveBrDate($userId);
            }
        }

        // Бонусное вознаграждение (BR)
        if ($days <= 112 && $rank > 6) {
            if ($rank == 7 && $totalBalance >= 4000000) {
                $this->saveBr($userId, $newBrBalance);
//                $this->moveBrDate($userId);

                RankDB::deleteBrNotification($userId);

                $message = 'Поздравляем! Вы заслужили путевку на сумму 400 тысяч тенге!';
                RankDB::createBrNotification($userId, 4000000, $message);
            }
            if ($rank == 8 && $totalBalance >= 10000000) {
                $this->saveBr($userId, $newBrBalance);
//                $this->moveBrDate($userId);

                RankDB::deleteBrNotification($userId);
                $message = 'Поздравляем! Вы заслужили путевку на сумму 1 миллион тенге';
                RankDB::createBrNotification($userId, 4000000, $message);
            } elseif ($rank == 9 && $totalBalance >= 20000000) {
                $this->saveBr($userId, $newBrBalance);
//                $this->moveBrDate($userId);

                RankDB::deleteBrNotification($userId);
                $message = 'Поздравляем! Вы заслужили путевку на сумму 2 миллион тенге';
                RankDB::createBrNotification($userId, 4000000, $message);
            }
        } else {
//            $transaction = RankDB::getUserNextTransactionByDate($userId, $startDateUnix);
//            $newStartDate = $transaction ? date('Y-m-d H:i:s', $transaction['date']) : null;
//            if ($newStartDate) {
//                $status = RankDB::saveRewardByCondition($userId, ['br_start_at' => $newStartDate]);
            RankDB::deleteBrNotification($userId);
//            }
        }

        return $status;
    }

    protected function getFirstParent(array $list)
    {
        $result = null;

        if ($list) {
            $result = reset($list);
        }

        return $result;
    }

    protected function getParentsUniqueIdString(array $list)
    {
        $result = null;
        if ($list) {
            foreach ($list as $item) {
                $result .= ($result ? ', ' : '') . "'" . $item['unique_id'] . "'";
            }
        }

        return $result;
    }

    protected function saveBr($userId, $totalBalance)
    {
        return RankDB::saveRewardByCondition($userId, ['br' => 1, 'prev_br_balance' => $totalBalance]);
    }

    protected function moveBrDate($userId)
    {
        $newStartDate = new DateTime();
        $newStartDate->modify('+1 day');
        $newStartDate = $newStartDate->format('Y-m-d H:i:s');
        $status = RankDB::saveRewardByCondition($userId, ['br_start_at' => $newStartDate]);

        return $status;
    }
}