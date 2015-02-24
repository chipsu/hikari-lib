<?php

namespace view;

use \hikari\view\View;

class HtplTest extends \Codeception\TestCase\Test {
    use \Codeception\Specify;

    protected function _before() {
    }

    protected function _after() {
    }

    public function testBasic() {
        $this->specify('h1 compile', function() {
            $view = new View([
                'paths' => [ __DIR__ ],
                'compilers' => ['htpl' => '\hikari\view\compiler\Htpl2Compiler'],
                'storage' => sys_get_temp_dir(),
            ]);
            $source = $view->find('templates/test');
            $result = $view->template('templates/test');
            $this->assertEquals(file_get_contents(preg_replace('/\.htpl$/', '.html', $source)), $result);
        });
    }
}
