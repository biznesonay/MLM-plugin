<?php

namespace core\service;


class Tree
{

    /**
     * Create tree
     * @param array $data
     * @return array
     */
    public static function getTree($data)
    {
        $tree = [];
        foreach ($data as $id => &$node) {
            if (!$node['parent_id']) {
                $tree[$id] = &$node;
            } else {
                $data[$node['parent_id']]['children'][$id] = &$node;
            }
        }
        return $tree;
    }

    /**
     * Create menu
     * @param array $tree
     * @return string
     */
    public static function getMenuHtml($tree)
    {
        $str = '';
        foreach ($tree as $category) {
            $str .= Tree::catToTemplate($category);
        }
        return $str;
    }

    /**
     * Depends getMenuHtml
     * @param array $category
     * @return string
     */
    protected static function catToTemplate($category)
    {
        $html = '<li href="#" class="mycolapse"><a>';
        $html .= $category['name'];
        if (isset($category['children'])) {
            $html .= '<span class="badge pull-right col btnshow"><i class="fa fa-plus"></i></span>';
        }

        if (isset($category['children'])) {
            $html .= '<ul class="children"> ' . Tree::getMenuHtml($category['children']) . '</ul>';
        }

        $html .= '</a></li>';

        return $html;
    }
}