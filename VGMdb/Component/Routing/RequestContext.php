<?php

namespace VGMdb\Component\Routing;

use Symfony\Component\Routing\RequestContext as BaseRequestContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Holds extra information about the current request.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class RequestContext extends BaseRequestContext
{
    private $format;
    private $version;
    private $locale;
    private $language;
    private $region;
    private $userAgent;
    private $referer;

    private $mobileDetector;
    private $isMobile;
    private $isTablet;
    private $isWeb;

    /**
     * {@inheritDoc}
     */
    public function fromRequest(Request $request)
    {
        parent::fromRequest($request);

        $this->setUserAgent($request->headers->get('User-Agent'));
        $this->setReferer($request->headers->get('Referer'));

        if (null !== $this->mobileDetector) {
            $headers = array();
            foreach ($request->headers->all() as $key => $value) {
                $key = strtoupper(str_replace('-', '_', $key));
                if (!in_array($key, array('CONTENT_TYPE', 'CONTENT_LENGTH'))) {
                    $headers['HTTP_'.$key] = implode(', ', $value);
                }
            }
            $this->mobileDetector->setHttpHeaders($headers);
        }
    }

    /**
     * Gets the requested response format.
     *
     * @return string The response format.
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Sets the response format.
     *
     * @param string The response format.
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * Gets the API version.
     *
     * @return string The API version.
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Sets the API version.
     *
     * @param string The API version.
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Gets the locale string.
     *
     * @return string The locale string.
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Sets the locale string.
     *
     * Also sets the language and region at the same time.
     *
     * @param string The locale string.
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        $locale = explode('_', $locale);

        if (count($locale) > 1 && strlen(end($locale)) === 2) {
            $this->region = strtoupper(array_pop($locale));
        }

        if ($language = implode('_', $locale)) {
            $this->language = $language;
        }
    }

    /**
     * Gets the language code.
     *
     * @return string The language code.
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Sets the language code.
     *
     * @param string The language code.
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Gets the region code.
     *
     * @return string The region code.
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Sets the region code.
     *
     * @param string The region code.
     */
    public function setRegion($region)
    {
        $this->region = strtoupper($region);
    }

    /**
     * Gets the user agent.
     *
     * @return string The user agent.
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Sets the user agent.
     *
     * @param string The user agent.
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        if (null !== $this->mobileDetector) {
            $this->mobileDetector->setUserAgent($userAgent);
        }

        $this->isMobile = $this->isTablet = $this->isWeb = null;
    }

    /**
     * Gets the HTTP referer.
     *
     * @return string The HTTP referer
     */
    public function getReferer()
    {
        return $this->referer;
    }

    /**
     * Gets the HTTP referer.
     *
     * @param string The HTTP referer
     */
    public function setReferer($referer)
    {
        $this->referer = $referer;
    }

    /**
     * Check whether the client is a mobile device.
     *
     * @link http://detectmobilebrowsers.com/
     *
     * @return Boolean
     */
    public function isMobile()
    {
        if (null !== $this->isMobile) {
            return $this->isMobile;
        }

        if (null !== $this->mobileDetector) {
            $this->isMobile = $this->mobileDetector->isMobile();

            return $this->isMobile;
        }

        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $this->userAgent)) {
            $this->isMobile = true;

            return true;
        }

        if (preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($this->userAgent, 0, 4))) {
            $this->isMobile = true;

            return true;
        }

        $this->isMobile = false;

        return false;
    }

    /**
     * Check whether the client is a tablet device.
     *
     * @link http://detectmobilebrowsers.com/
     *
     * @return Boolean
     */
    public function isTablet()
    {
        if (null !== $this->isTablet) {
            return $this->isTablet;
        }

        if (null !== $this->mobileDetector) {
            $this->isTablet = $this->mobileDetector->isTablet();

            return $this->isTablet;
        }

        if ($this->isMobile()) {
            $this->isTablet = false;

            return false;
        }

        if (preg_match('/android|ipad|playbook|silk|tablet/i', $this->userAgent)) {
            $this->isTablet = true;

            return true;
        }

        $this->isTablet = false;

        return false;
    }

    /**
     * Check whether the client is a web browser.
     *
     * @return Boolean
     */
    public function isWeb()
    {
        if (null !== $this->isWeb) {
            return $this->isWeb;
        }

        $this->isWeb = !$this->isMobile() && !$this->isTablet();

        return $this->isWeb;
    }

    /**
     * Sets the mobile useragent detection library.
     */
    public function setMobileDetector($mobileDetector)
    {
        $this->mobileDetector = $mobileDetector;
    }
}
