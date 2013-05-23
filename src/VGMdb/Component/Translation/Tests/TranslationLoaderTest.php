<?php

namespace VGMdb\Component\Translation\Tests;

use VGMdb\Component\Translation\TranslationLoader;
use VGMdb\Component\Translation\MessageCatalogue;
use VGMdb\Component\Translation\Loader\XliffFileLoader;

/**
 * TranslationLoader test cases.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class TranslationLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp() {}

    protected function tearDown() {}

    public function testLoaderMapsFormatToExtension()
    {
        $catalogue = new MessageCatalogue('zh');
        $loader = new TranslationLoader();
        $loader->addLoader('xliff', new XliffFileLoader());
        $loader->loadMessages(__DIR__ . '/Fixtures', $catalogue);

        $this->assertFalse($catalogue->has('userbox.menu.logout', 'messages'));

        $loader->setExtensions(array(
            'xliff' => 'xlf'
        ));
        $loader->loadMessages(__DIR__ . '/Fixtures', $catalogue);
        $this->assertTrue($catalogue->has('userbox.menu.logout', 'messages'));

        $expected = '登出';
        $actual = $catalogue->get('userbox.menu.logout', 'messages');
        $this->assertEquals($expected, $actual);
    }
}
