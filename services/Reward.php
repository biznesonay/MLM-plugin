<?php

class Reward
{
    const RANK_PERCENT = [
        1 => 5,
        2 => 8,
        3 => 12,
        4 => 15,
        5 => 18,
        6 => 21,
        7 => 24,
        8 => 27,
        9 => 30,
    ];

    public static function countDr(int $setUserRank, int $parentRank, float $balance): float
    {
        $dr = 0.0;
        if ($setUserRank < $parentRank || ($setUserRank == 1 && $parentRank == 1)) {
            $rankToPercent = self::RANK_PERCENT[$parentRank] ?? 0;

            $dr = ($balance * $rankToPercent) / 100;
        }

        return $dr ? round($dr, 2) : 0.0;
    }

    public static function countSr(float $balance, array $parents, array $lastParent)
    {
        $result = [];
        if ($parents) {
            $parentList = $parents;
            $now = date('Y-m-d');

            foreach ($parents as $parentKey => $parent) {
                $parentId = $parent['unique_id'];
                $parentRank = (int)$parent['rank'];
                $parentSr = (float)$parent['sr'];
                $srAt = $parent['sr_at'] ?? null;

                unset($parentList[$parentKey]);
                $maxRank = self::getMaxRank($parentList);

                //$lastParent['unique_id'] != $parentId Не должен передовать первому родителю
                if ($maxRank && $parentRank > $maxRank && $lastParent['unique_id'] != $parentId && $parentRank >= 4 && $srAt >= $now) {
                    $parentRankToPercent = self::RANK_PERCENT[$parentRank] ?? 0;
                    $maxRankToPercent = self::RANK_PERCENT[$maxRank] ?? 0;
                    $sr = round(($balance * ($parentRankToPercent - $maxRankToPercent)) / 100, 2);
                    $result[$parentId]['sr'] = $sr;
                    $result[$parentId]['pre_sr'] = $parentSr;
                }
            }
        }

        return $result;
    }

    public static function countMr($rank, float $balance, array $parents)
    {
        $mr = 0;
        $mrParent = null;

        $firstParent = self::findFirstParentByRank($parents, 6);
        if ($firstParent) {
            unset($parents[$firstParent['key']]);
        }

        $secondParent = self::findFirstParentByRank($parents, 6);

        if ($firstParent && $secondParent) {
            if ($firstParent['data']['rank'] == $secondParent['data']['rank']) {
                $mr = round(($balance * 3) / 100, 2);

                $mrParent = $secondParent;
            }
        }

        return [
            'mr' => $mr,
            'parent' => $mrParent
        ];
    }

    protected static function findFirstParentByRank($list, $need)
    {
        $result = false;
        if (!$list) return $result;

        foreach ($list as $key => $item) {
            if ($item['rank'] >= $need) {
                $result['key'] = $key;
                $result['data'] = $item;
                break;
            }
        }

        return $result;
    }

    protected static function getMaxRank(array $list)
    {
        $maxRank = 0;
        if (!$list) return $maxRank;

        foreach ($list as $item) {
            if ($maxRank < (int)$item['rank']) {
                $maxRank = $item['rank'];
            }
        }

        return (int)$maxRank;
    }
}