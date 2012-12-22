<?php

namespace VGMdb\Component\HttpFoundation\Util;

use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\Request;

/**
 * Parses the Accept header to decide response format and version attribute.
 * Adapted from the FOSRest package (c) FriendsOfSymfony <http://friendsofsymfony.github.com>
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class AcceptNegotiator
{
    /**
     * Deduce the best request format from the Accept header based on priority list.
     *
     * @param Request $request         The request
     * @param array   $priorities      Ordered array of formats (highest priority first)
     * @param Boolean $preferExtension If to consider the extension last or first
     *
     * @return null|string The format string
     */
    public function getBestFormat(Request $request, array $priorities, $preferExtension = false)
    {
        $mimetypes = array();
        $accepts = AcceptHeader::fromString($request->headers->get('Accept'))->all();
        foreach ($accepts as $item) {
            $mimetypes[$item->getValue()] = $item->getQuality();
        }

        $extension = $request->get('_format');
        if (null !== $extension && null !== $request->getMimeType($extension)) {
            $mimetypes[$request->getMimeType($extension)] = (Boolean) $preferExtension
                ? reset($mimetypes) + 1
                : end($mimetypes) - 1;
            arsort($mimetypes);
        }

        if (empty($mimetypes)) {
            return null;
        }

        $catchAllEnabled = in_array('*/*', $priorities);

        return $this->getFormatByPriorities($request, $mimetypes, $priorities, $catchAllEnabled);
    }

    /**
     * Get the format applying the supplied priorities to the mime types.
     *
     * @param Request $request         The request
     * @param array   $mimetypes       Ordered array of mimetypes as keys with priorities as values
     * @param array   $priorities      Ordered array of formats (highest priority first)
     * @param Boolean $catchAllEnabled If there is a catch all priority
     *
     * @return void|string The format string
     */
    protected function getFormatByPriorities($request, $mimetypes, $priorities, $catchAllEnabled = false)
    {
        $max = reset($mimetypes);
        $keys = array_keys($mimetypes, $max);

        $formats = array();
        foreach ($keys as $mimetype) {
            unset($mimetypes[$mimetype]);
            if ($mimetype === '*/*') {
                return reset($priorities);
            }
            $format = $request->getFormat($mimetype);
            if ($format) {
                $priority = array_search($format, $priorities);
                if (false !== $priority) {
                    $formats[$format] = $priority;
                } elseif ($catchAllEnabled) {
                    $formats[$format] = count($priorities);
                }
            }
        }

        if (empty($formats) && !empty($mimetypes)) {
            return $this->getFormatByPriorities($request, $mimetypes, $priorities);
        }

        asort($formats);

        return key($formats);
    }

    /**
     * Deduce the version attribute of a selected format from the Accept header.
     *
     * @param Request $request         The request
     * @param string  $format          The format string
     * @param string  $default_version The version to fall back to if none found
     *
     * @return string The version string
     */
    public function getVersionForFormat($request, $format, $default_version = '1.0')
    {
        $versions = array();
        $accepts = AcceptHeader::fromString($request->headers->get('Accept'))->all();
        foreach ($accepts as $item) {
            $versions[$item->getValue()] = $item->getAttribute('version', $default_version);
        }

        foreach ($versions as $mimetype => $version) {
            if ($request->getFormat($mimetype) === $format) {
                return $version;
            }
        }

        return $default_version;
    }
}
