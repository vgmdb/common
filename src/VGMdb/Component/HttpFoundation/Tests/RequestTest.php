<?php

namespace VGMdb\Component\HttpFoundation\Tests;

use VGMdb\Component\HttpFoundation\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getClientIpsProvider
     */
    public function testGetClientIp($expected, $remoteAddr, $httpForwardedFor, $trustedProxies)
    {
        $request = $this->getRequestInstanceForClientIpTests($remoteAddr, $httpForwardedFor, $trustedProxies);

        $this->assertEquals($expected[0], $request->getClientIp());

        Request::setTrustedProxies(array());
    }

    public function getClientIpsProvider()
    {
        return array(
            array(array('88.88.88.88'), '123.45.67.89', '88.88.88.88', array('123.45.67.0/24')),
            array(array('88.88.88.88'), '2a01:198:603:0:396e:4789:8e99:890f', '88.88.88.88', array('2a01:198:603:0::/65')),
        );
    }

    private function getRequestInstanceForClientIpTests($remoteAddr, $httpForwardedFor, $trustedProxies)
    {
        $request = new Request();

        $server = array('REMOTE_ADDR' => $remoteAddr);
        if (null !== $httpForwardedFor) {
            $server['HTTP_X_FORWARDED_FOR'] = $httpForwardedFor;
        }

        if ($trustedProxies) {
            Request::setTrustedProxies($trustedProxies);
        }

        $request->initialize(array(), array(), array(), array(), array(), $server);

        return $request;
    }
}
