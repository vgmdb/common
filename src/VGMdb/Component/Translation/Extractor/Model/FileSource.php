<?php

/*
 * This code was originally part of JMSTranslationBundle.
 *
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
 */

namespace VGMdb\Component\Translation\Extractor\Model;

class FileSource implements SourceInterface
{
    private $path;
    private $line;
    private $column;

    public function __construct($path, $line = null, $column = null)
    {
        $this->path = $path;
        $this->line = $line;
        $this->column = $column;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function equals(SourceInterface $source)
    {
        if (!$source instanceof FileSource) {
            return false;
        }

        if ($this->path !== $source->getPath()) {
            return false;
        }

        if ($this->line !== $source->getLine()) {
            return false;
        }

        if ($this->column !== $source->getColumn()) {
            return false;
        }

        return true;
    }

    public function __toString()
    {
        $str = $this->path;

        if (null !== $this->line) {
            $str .= ' on line '.$this->line;

            if (null !== $this->column) {
                $str .= ' at column '.$this->column;
            }
        }

        return $str;
    }

    /**
     * Allows us to rehydrate from var_export.
     *
     * @return FileSource
     */
    static public function __set_state(array $data)
    {
        return new FileSource($data['path'], $data['line'], $data['column']);
    }
}
