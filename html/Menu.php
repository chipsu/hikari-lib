<?php

namespace hikari\html;

class Menu extends \hikari\core\Component {
    public $html;

    function render($view, array $items, $wrap = ['nav', 'ul'], $tag = 'li', $innerWrap = ['ul']) {
        $result = '';
        foreach($wrap as $key => $value) {
            if(is_numeric($key)) {
                $result .= $this->html->open($value);
            } else {
                $result .= $this->html->open($key, $value);
            }
        }
        foreach($items as $item) {
            # todo: item template/callback
            $content = sprintf('<i class="fa fa-fw %s"></i> %s', $item['icon'], $item['title']);
            $result .= $this->html->open($tag, isset($item['attr']) ? $item['attr'] : []);
            $result .= $this->html->a(call_user_func_array(array($view, 'url'), $item['route']), [], $content);
            if(!empty($item['items'])) {
                $result .= $this->render($view, $item['items'], $innerWrap, $tag, $innerWrap);
            }
            $result .= $this->html->close($tag);
        }
        foreach($wrap as $key => $value) {
            $result .= $this->html->close(is_numeric($key) ? $value : $key);
        }
        return $result;
    }
}
