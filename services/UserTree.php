<?php
/**
 * Created by PhpStorm.
 * User: zh.zhumagali
 * Date: 22.09.2020
 * Time: 19:33
 */

/**
 * Class UserTree
 */
class UserTree
{

    /**
     * @return array
     */
    public static function getTree()
    {
        $data = self::getUsers();

        return self::genTreeData($data);
    }

    /**
     * @return array
     */
    public static function getUsers(): array
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $sql = "SELECT id,  CONCAT(user_name, ' (', rank , ')')as name, unique_id, (select u.id from {$prefix}mlm_users as u where u.unique_id =  {$prefix}mlm_users.sponsor_id) as sponsor_id, id as length FROM {$prefix}mlm_users ";
        $sql .= "order by id";
        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result ?: [];
    }

    public static function getUserChildren_(string $uniqueId)
    {
        global $wpdb;

        $sql = "WITH RECURSIVE rec(id, name, unique_id, sponsor_id, rank) AS ( 
             SELECT  id, CONCAT(user_name, ' (', rank , ')') as name, unique_id, sponsor_id, rank FROM {$wpdb->prefix}mlm_users WHERE sponsor_id = '{$uniqueId}'
             UNION ALL
             SELECT u.id, CONCAT(u.user_name, ' (', u.rank , ')') AS name, u.unique_id, u.sponsor_id, u.rank FROM rec 
             INNER JOIN {$wpdb->prefix}mlm_users AS u ON rec.unique_id = u.sponsor_id
        ) ";

        $sql .= "SELECT * FROM rec  ORDER BY id ASC";

        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result ? $result : [];
    }

    public static function getUserChildren(string $uniqueId)
    {
        global $wpdb;

        $sql = "WITH RECURSIVE rec(id, name, unique_id, sponsor_id, length) AS ( 
             SELECT  id, CONCAT(user_name, ' (', rank , ')') as name, unique_id, 0, id FROM {$wpdb->prefix}mlm_users WHERE unique_id = '{$uniqueId}'
             UNION ALL
             SELECT u.id, CONCAT(u.user_name, ' (', u.rank , ')') AS user_name, u.unique_id, 
             (select uu.id from {$wpdb->prefix}mlm_users as uu where uu.unique_id =  u.sponsor_id) as sponsor_id, u.id FROM rec 
             INNER JOIN {$wpdb->prefix}mlm_users AS u ON rec.unique_id = u.sponsor_id
        ) ";

        $sql .= "SELECT * FROM rec  ORDER BY id ASC";

        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result ? $result : [];
    }

    /**
     * @param $data
     * @return array
     */
    protected static function genTreeData($data)
    {
        $tree = [];
        $data = self::uniqueArray($data);

        foreach ($data as $id => &$node) {
            if (!$node['sponsor_id']) {
                $tree[$id] = &$node;
            } else {
                $data[$node['sponsor_id']]['children'][$node['unique_id']] = &$node;
            }
        }
        return $tree;
    }

    /**
     * Associative array by unique_id column
     * @param $data
     * @return array
     */
    protected static function uniqueArray($data)
    {
        $result = [];

        if ($data) {
            foreach ($data as $id => $item) {
                $result[$item['unique_id']] = $item;
            }
        }

        return $result;
    }

    public static function html($tree)
    {
        $html = '<ul>';
        $html .= '<li>';
        $html .= self::genHtml($tree);
        $html .= '</li>';
        $html .= '</ul>';

        return $html;
    }

    protected static function genHtml($tree)
    {
        $str = '';
        foreach ($tree as $k => $item) {
            $str .= self::catToTemplate($k, $item);
        }

        return $str;
    }

    protected static function catToTemplate($k, $item)
    {
        $html = '<li>';
        $html .= "<a>{$item['user_name']}</a>";


        if (isset($item['children'])) {
            $html .= '<ul>';
            $html .= self::genHtml($item['children']);
            $html .= '</ul>';
        }
        $html .= '</li>';

        return $html;
    }
}