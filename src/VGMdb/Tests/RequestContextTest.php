<?php

namespace VGMdb\Tests;

use VGMdb\Component\Routing\RequestContext;

/**
 * Request context test cases.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class RequestContextTest extends \PHPUnit_Framework_TestCase
{
    private $context;
    private $detector;

    protected function setUp()
    {
        $this->context = new RequestContext();
        $this->detector = new \Mobile_Detect();
    }

    protected function tearDown() {}

    public function testRequestContextSupportsSubdomain()
    {
        $this->context->setSubdomain('m');
        $expected = 'm';
        $actual = $this->context->getSubdomain();
        $this->assertSame($expected, $actual);
    }

    public function testRequestContextSupportsFormat()
    {
        $this->context->setFormat('json');
        $expected = 'json';
        $actual = $this->context->getFormat();
        $this->assertSame($expected, $actual);
    }

    public function testRequestContextSupportsVersion()
    {
        $this->context->setVersion('0.0.1');
        $expected = '0.0.1';
        $actual = $this->context->getVersion();
        $this->assertSame($expected, $actual);
    }

    public function testRequestContextSupportsLocale()
    {
        $this->context->setLocale('en_US');
        $expected = 'en_US';
        $actual = $this->context->getLocale();
        $this->assertSame($expected, $actual);
    }

    public function testRequestContextSupportsLanguage()
    {
        $this->context->setLanguage('en');
        $expected = 'en';
        $actual = $this->context->getLanguage();
        $this->assertSame($expected, $actual);
    }

    public function testRequestContextSupportsRegion()
    {
        $this->context->setRegion('US');
        $expected = 'US';
        $actual = $this->context->getRegion();
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider provideLocale
     */
    public function testRequestContextSetLocaleShouldSetLanguage($locale, $expectedLanguage, $expectedRegion)
    {
        $this->context->setLocale($locale);
        $actual = $this->context->getLanguage();
        $this->assertSame($expectedLanguage, $actual);
    }

    /**
     * @dataProvider provideLocale
     */
    public function testRequestContextSetLocaleShouldSetRegion($locale, $expectedLanguage, $expectedRegion)
    {
        $this->context->setLocale($locale);
        $actual = $this->context->getRegion();
        $this->assertSame($expectedRegion, $actual);
    }

    public function provideLocale()
    {
        return array(
            array('en_US', 'en', 'US'),
            array('zh_Hant_TW', 'zh_Hant', 'TW'),
            array('en', 'en', null),
            array('zh_Hans', 'zh_Hans', null),
            array('', null, null),
        );
    }

    public function testRequestContextSupportsUserAgent()
    {
        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.101 Safari/537.11';

        $this->context->setUserAgent($userAgent);
        $expected = $userAgent;
        $actual = $this->context->getUserAgent();
        $this->assertSame($expected, $actual);
    }

    public function testRequestContextSupportsIpAddress()
    {
        $ipAddress = '192.168.0.1';

        $this->context->setIpAddress($ipAddress);
        $expected = $ipAddress;
        $actual = $this->context->getIpAddress();
        $this->assertSame($expected, $actual);
    }

    public function testRequestContextSupportsReferer()
    {
        $this->context->setReferer('http://www.example.com/');
        $expected = 'http://www.example.com/';
        $actual = $this->context->getReferer();
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider provideMobileUserAgent
     */
    public function testRequestContextDetectsMobileBrowser($userAgent)
    {
        $this->context->setUserAgent($userAgent);
        $this->assertTrue($this->context->isMobile());
    }

    /**
     * @dataProvider provideTabletUserAgent
     */
    public function testRequestContextDetectsTabletBrowser($userAgent)
    {
        $this->context->setUserAgent($userAgent);
        $this->assertTrue($this->context->isTablet());
    }

    /**
     * @dataProvider provideWebUserAgent
     */
    public function testRequestContextDetectsWebBrowser($userAgent)
    {
        $this->context->setUserAgent($userAgent);
        $this->assertTrue($this->context->isWeb());
    }

    /**
     * @dataProvider provideMobileUserAgent
     */
    public function testMobileDetectorDetectsMobileBrowser($userAgent)
    {
        $this->context->setMobileDetector($this->detector);
        $this->context->setUserAgent($userAgent);
        $this->assertTrue($this->context->isMobile());
    }

    /**
     * @dataProvider provideTabletUserAgent
     */
    public function testMobileDetectorDetectsTabletBrowser($userAgent)
    {
        $this->context->setMobileDetector($this->detector);
        $this->context->setUserAgent($userAgent);
        $this->assertTrue($this->context->isTablet());
    }

    /**
     * @dataProvider provideWebUserAgent
     */
    public function testMobileDetectorDetectsWebBrowser($userAgent)
    {
        $this->context->setMobileDetector($this->detector);
        $this->context->setUserAgent($userAgent);
        $this->assertTrue($this->context->isWeb());
    }

    public function provideMobileUserAgent()
    {
        return array(
            'Motorola Droid X' => array('Mozilla/5.0 (Linux; U; Android 2.2; en-us; DROIDX Build/VZW) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1'),
            'Apple iPhone 5'   => array('Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3'),
            'Nokia Lumia 900'  => array('Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; NOKIA; Lumia 900)'),
            'BlackBerry 9700'  => array('Mozilla/5.0 (BlackBerry; U; BlackBerry 9700; ja) AppleWebKit/534.8+ (KHTML, like Gecko) Version/6.0.0.570 Mobile Safari/534.8+'),
        );
    }

    public function provideTabletUserAgent()
    {
        return array(
            'Apple iPad'          => array('Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5355d Safari/8536.25'),
            'Google Nexus 7'      => array('Mozilla/5.0 (Linux; Android 4.1.1; Nexus 7 Build/JRO03D) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.166  Safari/535.19'),
            'Samsung Galaxy Tab'  => array('Mozilla/5.0 (Linux; U; Android 3.2; en-us; GT-P7510 Build/HTJ85B) AppleWebKit/534.13 (KHTML, like Gecko) Version/4.0 Safari/534.13'),
            'BlackBerry Playbook' => array('Mozilla/5.0 (PlayBook; U; RIM Tablet OS 2.0.1; en-US) AppleWebKit/535.8+ (KHTML, like Gecko) Version/7.2.0.1 Safari/535.8+'),
        );
    }

    public function provideWebUserAgent()
    {
        return array(
            'MSIE 10'          => array('Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Win64; x64; Trident/6.0)'),
            'Google Chrome 23' => array('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.101 Safari/537.11 FirePHP/4Chrome'),
            'Firefox 18'       => array('Mozilla/5.0 (Windows NT 6.2; WOW64; rv:18.0) Gecko/20100101 Firefox/18.0'),
            'PlayStation 3'    => array('Mozilla/5.0 (PLAYSTATION 3; 1.00)'),
        );
    }
}
