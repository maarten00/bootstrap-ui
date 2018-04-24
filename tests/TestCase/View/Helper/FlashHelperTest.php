<?php

namespace BootstrapUI\Test\TestCase\View\Helper;

use BootstrapUI\View\Helper\FlashHelper;
use Cake\Core\Exception\Exception;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * FlashHelperTest class
 *
 */
class FlashHelperTest extends TestCase
{
    /**
     * @var View
     */
    public $View;

    /**
     * @var FlashHelper
     */
    public $Flash;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->View = new View();

        // load appropriate session class according to Cake version
        if (!class_exists('\Cake\Http\Session')) {
            // before 3.6
            $session = new \Cake\Network\Session();
        } else {
            // 3.6 and later
            $session = new \Cake\Http\Session();
        }

        $this->View->request = new ServerRequest(['session' => $session]);
        $this->Flash = new FlashHelper($this->View);

        $session->write([
            'Flash' => [
                'flash' => [
                    'key' => 'flash',
                    'message' => 'This is a calling',
                    'element' => 'Flash/default',
                    'params' => []
                ],
                'error' => [
                    'key' => 'error',
                    'message' => 'This is error',
                    'element' => 'Flash/error',
                    'params' => []
                ],
                'custom1' => [
                    'key' => 'custom1',
                    'message' => 'This is custom1',
                    'element' => 'Flash/warning',
                    'params' => []
                ],
                'custom2' => [
                    'key' => 'custom2',
                    'message' => 'This is custom2',
                    'element' => 'Flash/default',
                    'params' => ['class' => 'foobar']
                ],
                'custom3' => [
                    'key' => 'custom3',
                    'message' => 'This is <a href="#">custom3</a>',
                    'element' => 'Flash/default',
                    'params' => ['escape' => false]
                ],
                'custom4' => [
                    'key' => 'flash',
                    'message' => 'testClass',
                    'element' => 'Flash/default',
                    'params' => ['class' => 'primary']
                ],
                'invalidKey' => 'foo'
            ]
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        unset($this->View, $this->Flash);
    }

    /**
     * testFlash method
     *
     * @return void
     */
    public function testRender()
    {
        $result = $this->Flash->render('nonExistentKey');
        $this->assertNull($result);

        $result = $this->Flash->render();
        $this->assertContains('<div role="alert" class="alert alert-dismissible fade show alert-info">', $result);
        $this->assertContains('<button type="button" class="close" data-dismiss="alert" aria-label="Close">', $result);
        $this->assertContains('<span aria-hidden="true">&times;</span></button>', $result);
        $this->assertContains('This is a calling', $result);

        $result = $this->Flash->render('error');
        $this->assertContains('<div role="alert" class="alert alert-dismissible fade show alert-danger">', $result);
        $this->assertContains('<button type="button" class="close" data-dismiss="alert" aria-label="Close">', $result);
        $this->assertContains('This is error', $result);

        $result = $this->Flash->render('custom1', ['params' => ['class' => ['alert']]]);
        $this->assertContains('<div role="alert" class="alert alert-warning">', $result);
        $this->assertNotContains('<span aria-hidden="true">&times;</span></button>', $result);
        $this->assertContains('This is custom1', $result);

        $result = $this->Flash->render('custom2');
        $this->assertContains('<div role="alert" class="foobar">', $result);
        $this->assertContains('This is custom2', $result);

        $result = $this->Flash->render('custom3');
        $this->assertContains('This is <a href="#">custom3</a>', $result);

        $result = $this->Flash->render('custom4');
        $this->assertContains('<div role="alert" class="alert alert-dismissible fade show alert-primary">', $result);
        $this->assertContains('testClass</div>', $result);

        $this->expectException(\UnexpectedValueException::class);
        $this->Flash->render('invalidKey');
    }

    /**
     * In CakePHP 3.1 you multiple message per key
     *
     * @return void
     */
    public function testRenderForMultipleMessages()
    {
        $this->View->request->getSession()->write([
            'Flash' => [
                'flash' => [
                    [
                        'key' => 'flash',
                        'message' => 'This is a calling',
                        'element' => 'Flash/default',
                        'params' => []
                    ],
                    [
                        'key' => 'flash',
                        'message' => 'This is a second message',
                        'element' => 'Flash/default',
                        'params' => ['class' => ['extra']]
                    ],
                ],
                'error' => [
                    [
                        'key' => 'error',
                        'message' => 'This is error',
                        'element' => 'Flash/error',
                        'params' => []
                    ]
                ]
            ]
        ]);

        $result = $this->Flash->render();
        $this->assertContains('<div role="alert" class="alert alert-dismissible fade show alert-info">', $result);
        $this->assertContains('<button type="button" class="close" data-dismiss="alert" aria-label="Close">', $result);
        $this->assertContains('<span aria-hidden="true">&times;</span></button>', $result);
        $this->assertContains('This is a calling', $result);

        $this->assertContains('<div role="alert" class="extra alert-info">', $result);
        $this->assertContains('This is a second message', $result);

        $result = $this->Flash->render('error');
        $this->assertContains('<div role="alert" class="alert alert-dismissible fade show alert-danger">', $result);
        $this->assertContains('<button type="button" class="close" data-dismiss="alert" aria-label="Close">', $result);
        $this->assertContains('This is error', $result);
    }
}
