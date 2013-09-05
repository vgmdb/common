<?php

namespace VGMdb\Component\HttpFoundation;

use VGMdb\Component\View\ViewInterface;
use Symfony\Component\HttpFoundation\Request as BaseRequest;
use Knp\Snappy\Pdf;

/**
 * Returns a PDF file.
 *
 * @author Gigablah <gigablah@vgmdb.net>
 */
class PdfResponse extends Response
{
    protected $snappy;
    protected $saveAs;

    /**
     * {@inheritdoc}
     */
    public function prepare(BaseRequest $request)
    {
        $content = $view = $this->content;

        if ($view instanceof ViewInterface) {
            $content = (string) $view;
            if ($view->hasException()) {
                throw $view->getException();
            }
        }

        $this->headers->set('Content-Type', 'application/pdf');

        $this->snappy = new Pdf('/usr/local/bin/wkhtmltopdf');

        if ($options = $request->attributes->get('pdf_options')) {
            $this->snappy->setOptions($options);
        }

        if ($filename = $request->get('filename')) {
            $this->setSaveAs($filename);
        }

        $this->setContent($this->snappy->getOutputFromHtml($content));

        if ($this->saveAs) {
            $this->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $this->saveAs));
        }

        return parent::prepare($request);
    }

    /**
     * Forces the file to be downloaded.
     *
     * @param string $name The download filename
     *
     * @return PdfResponse
     */
    public function setSaveAs($name = null)
    {
        $this->saveAs = $name;

        return $this;
    }
}
