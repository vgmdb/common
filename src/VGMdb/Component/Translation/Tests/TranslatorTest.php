<?php

namespace VGMdb\Component\Translation\Tests;

use VGMdb\Component\Translation\Translator;
use VGMdb\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\ArrayLoader;

/**
 * Translator test cases.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class TranslatorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp() {}

    protected function tearDown() {}

    public function testTransFallbackWithNoTranslation()
    {
        $translator = new Translator('en', new MessageSelector());
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', array('Foo' => 'Bar', 'Baz' => ''), 'en', 'messages');

        $expected = 'Bar';
        $actual = $translator->trans('Foo');
        $this->assertEquals($expected, $actual);

        $expected = 'Baz';
        $actual = $translator->trans('Baz');
        $this->assertEquals($expected, $actual);
    }

    public function testTransMustacheTemplateWithId()
    {
        if (!class_exists('Mustache_Engine')) {
            $this->markTestSkipped('Mustache templating engine not installed.');
        }

        $translator = new Translator('zh', new MessageSelector());
        $translator->setFallbackLocale('en');
        $translator->addLoader('xliff', new XliffFileLoader());
        $translator->addResource('xliff', __DIR__ . '/Fixtures/messages.zh.xlf', 'zh', 'messages');

        $mustache = new \Mustache_Engine();
        $mustache->addHelper('t', function ($string) use ($translator) {
            $string = trim($string);
            if (false !== strpos($string, '{{!')) {
                $string = trim(substr(strstr($string, '}}', true), 3));
            }

            return $translator->trans($string);
        });

        $template = file_get_contents(__DIR__ . '/Fixtures/user.ms');
        $output = $mustache->loadTemplate($template)->render();

        $this->assertContains('登录网站', $output);
        $this->assertContains('无名氏', $output);
        $this->assertContains('placeholder="Username"', $output);
        $this->assertContains('placeholder="密码"', $output);
        $this->assertContains('<button type="submit">登录</button>', $output);
        $this->assertContains('使用Facebook登录', $output);
        $this->assertContains('使用Twitter登录', $output);
        $this->assertContains('使用Google登录', $output);
    }
}
