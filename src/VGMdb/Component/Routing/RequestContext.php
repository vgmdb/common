<?php

namespace VGMdb\Component\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext as BaseRequestContext;

/**
 * Holds extra information about the current request.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class RequestContext extends BaseRequestContext
{
    private $appName;
    private $appEnv;
    private $isDebug;
    private $subdomain;
    private $format;
    private $version;
    private $locale;
    private $localeKeywords;
    private $language;
    private $region;
    private $userAgent;
    private $ipAddress;
    private $referer;

    private $mobileDetector;
    private $client;
    private $isMobile;
    private $isTablet;
    private $isDesktop;

    /**
     * {@inheritDoc}
     */
    public function fromRequest(Request $request)
    {
        parent::fromRequest($request);

        $this->setUserAgent($request->headers->get('User-Agent'));
        $this->setIpAddress($request->getClientIp());
        $this->setReferer($request->headers->get('Referer'));

        // Inject the query string as a parameter for Symfony versions <= 2.2
        if (!method_exists($this, 'getQueryString') && '' !== $qs = $request->server->get('QUERY_STRING')) {
            $this->setParameter('QUERY_STRING', $qs);
        }

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
     * Gets the application name.
     *
     * @return string Application name.
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     * Sets the application name.
     *
     * @param string $appName Application name.
     */
    public function setAppName($appName)
    {
        $this->appName = $appName;
    }

    /**
     * Gets the application environment.
     *
     * @return string Application environment.
     */
    public function getEnvironment()
    {
        return $this->appEnv;
    }

    /**
     * Sets the application environment.
     *
     * @param string $appEnv Application environment.
     */
    public function setEnvironment($appEnv)
    {
        $this->appEnv = $appEnv;
    }

    /**
     * Check whether debug flag is activated.
     *
     * @return Boolean The debug flag.
     */
    public function isDebug()
    {
        return $this->isDebug;
    }

    /**
     * Sets the debug flag.
     *
     * @param Boolean $debug Whether the debug flag is activated.
     */
    public function setDebug($debug)
    {
        $this->isDebug = (Boolean) $debug;
    }

    /**
     * Gets the subdomain.
     *
     * @return string The subdomain.
     */
    public function getSubdomain()
    {
        return $this->subdomain;
    }

    /**
     * Sets the subdomain.
     *
     * @param string $subdomain The subdomain.
     */
    public function setSubdomain($subdomain)
    {
        $this->subdomain = $subdomain;
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
     * @param string $format The response format.
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
     * @param string $version The API version.
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
     * Gets the locale string with keywords.
     *
     * @return string The locale string.
     */
    public function getLocaleWithKeywords()
    {
        return $this->locale . ($this->localeKeywords ? '@' . $this->localeKeywords : '');
    }

    /**
     * Sets the locale string.
     *
     * Also sets the language and region at the same time. The locale may contain
     * additional keywords, which will be stored in localeKeywords.
     *
     * @param string $locale The locale string.
     */
    public function setLocale($locale)
    {
        if (false !== strpos($locale, '@')) {
            $locale = explode('@', $locale);
            $this->setLocaleKeywords(end($locale));
            $locale = reset($locale);
        }

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
     * Gets the locale keywords.
     *
     * @return string The locale keywords.
     */
    public function getLocaleKeywords()
    {
        return $this->localeKeywords;
    }

    /**
     * Sets the locale keywords, such as calendar and currency.
     *
     * @link http://userguide.icu-project.org/locale
     *
     * @param string $keywords The locale keywords.
     */
    public function setLocaleKeywords($keywords)
    {
        $this->localeKeywords = $keywords;
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
     * @param string $language The language code.
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
     * @param string $region The region code.
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
     * @param string $userAgent The user agent.
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        if (null !== $this->mobileDetector) {
            $this->mobileDetector->setUserAgent($userAgent);
        }

        $this->isMobile = $this->isTablet = $this->isDesktop = null;
    }

    /**
     * Gets the IP address.
     *
     * @return string The IP address.
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * Sets the IP address.
     *
     * @param string $ipAddress The IP address.
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }

    /**
     * Gets the HTTP referer.
     *
     * @return string The HTTP referer.
     */
    public function getReferer()
    {
        return $this->referer;
    }

    /**
     * Sets the HTTP referer.
     *
     * @param string $referer The HTTP referer.
     */
    public function setReferer($referer)
    {
        $this->referer = $referer;
    }

    /**
     * Gets the client.
     *
     * @return string The client.
     */
    public function getClient()
    {
        if (null !== $this->client) {
            return $this->client;
        }

        return $this->client = $this->isMobile() ? 'mobile' : 'desktop';
    }

    /**
     * Sets the client.
     *
     * @param string $client The client.
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * Check whether the client is a mobile device.
     *
     * @todo Cache results in APC
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
     * Check whether the client is a desktop browser.
     *
     * @return Boolean
     */
    public function isDesktop()
    {
        if (null !== $this->isDesktop) {
            return $this->isDesktop;
        }

        $this->isDesktop = !$this->isMobile() && !$this->isTablet();

        return $this->isDesktop;
    }

    /**
     * Sets the mobile useragent detection library.
     *
     * @param mixed $mobileDetector
     */
    public function setMobileDetector($mobileDetector)
    {
        $this->mobileDetector = $mobileDetector;
    }

    /**
     * Dump the context to an array.
     */
    public function toArray()
    {
        $data = array(
            'app_name'        => $this->getAppName(),
            'app_env'         => $this->getEnvironment(),
            'is_debug'        => (Boolean) $this->isDebug(),
            'subdomain'       => $this->getSubdomain(),
            'base_url'        => $this->getBaseUrl(),
            'path_info'       => $this->getPathInfo(),
            'method'          => $this->getMethod(),
            'host'            => $this->getHost(),
            'scheme'          => $this->getScheme(),
            'http_port'       => $this->getHttpPort(),
            'https_port'      => $this->getHttpsPort(),
            'parameters'      => $this->getParameters(),
            'format'          => $this->getFormat(),
            'version'         => $this->getVersion(),
            'locale'          => $this->getLocale(),
            'locale_keywords' => $this->getLocaleKeywords(),
            'language'        => $this->getLanguage(),
            'region'          => $this->getRegion(),
            'user_agent'      => $this->getUserAgent(),
            'ip_address'      => $this->getIpAddress(),
            'referer'         => $this->getReferer(),
            'client'          => $this->getClient(),
            'is_mobile'       => (Boolean) $this->isMobile(),
            'is_tablet'       => (Boolean) $this->isTablet(),
            'is_desktop'      => (Boolean) $this->isDesktop(),
        );

        return $data;
    }
}
