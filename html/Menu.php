<?php

namespace hikari\html;

class Menu extends \hikari\component\Component {
    public $html;

    function render($view, array $items, $wrap = ['nav', 'ul'], $tag = 'li', $innerWrap = ['ul']) {
        $result = '';
        foreach($wrap as $t) {
            $result .= $this->html->open($t);
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
        foreach($wrap as $t) {
            $result .= $this->html->close($t);
        }
        return $result;
    }
}
