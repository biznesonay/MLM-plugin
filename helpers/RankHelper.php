<?php

class RankHelper
{
    public static function firstLevelRankHashArray($data)
    {
        $result = [];

        if ($data) {
            foreach ($data as $item) {
                $path = explode('-', $item['path']);
                $key = $item['level'] == 1 ? $item['unique_id'] : $path['1'];
                $prevVal = $result[$key][$item['rank']] ?? 0;
                $result[$key][$item['rank']] = $prevVal + 1;
            }
        }

        return $result;
    }

    public static function firstLevelRankHashArraySecond($data)
    {
        $result = [];

        if ($data) {
            foreach ($data as $item) {
                $path = explode('->', $item['path']);
                if ($path) {
                    $prevVal = $result[$path[0]]['rank'] ?? 0;
                    $result[$path[0]][$item['rank']] = $prevVal + 1;
                }
            }
        }

        return $result;
    }

    public static function getUserList(array $usersLineTree)
    {
        $result = [];
        if ($usersLineTree) {

            foreach ($usersLineTree as $user) {
                $parents = explode('->', $user['path']);
                $last = array_pop($parents);

                if ($parents) {
                    foreach ($parents as $parent) {
                        self::setRankAndScc($parent, $result);
                    }
                } else {
                    self::setRankAndScc($last, $result);
                }
            }
        }

        return $result;
    }

    public static function getUserAllList(array $usersLineTree)
    {
        $result = [];
        if ($usersLineTree) {

            foreach ($usersLineTree as $user) {
                $parents = explode('->', $user['path']);

                if ($parents) {
                    foreach ($parents as $parent) {
                        self::setRankAndScc($parent, $result);
                    }
                } else {
                    $last = array_pop($parents);
                    self::setRankAndScc($last, $result);
                }
            }
        }

        return $result;
    }

    protected static function setRankAndScc($item, &$result): void
    {
        $parentAndScc = explode('[', $item);
        if ($parentAndScc) {
            $sccWithRank = explode(',', $parentAndScc[1]);
            if ($sccWithRank) {
                $result[$parentAndScc[0]]['unique_id'] = $parentAndScc[0];
                $result[$parentAndScc[0]]['scc'] = $sccWithRank[0];
                $result[$parentAndScc[0]]['rank'] = $sccWithRank[1];
                $result[$parentAndScc[0]]['dr'] = $sccWithRank[2];
                $result[$parentAndScc[0]]['sr'] = $sccWithRank[3];
                $result[$parentAndScc[0]]['mr'] = str_replace(']', '', $sccWithRank[4]);
            }
        }
    }
}