<?php

namespace VGMdb\Component\Translation\Extractor;

use VGMdb\Component\Translation\Annotation\Desc;
use VGMdb\Component\Translation\Annotation\Ignore;
use VGMdb\Component\Translation\Annotation\Meaning;
use Silex\Application;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * PhpExtractor extracts translation messages from PHP files.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Gigablah <gigablah@vgmdb.net>
 */
class PhpExtractor extends AbstractExtractor implements \PHPParser_NodeVisitor
{
    protected $docParser;
    protected $phpParser;
    protected $traverser;
    protected $logger;

    public function __construct(Application $app)
    {
        $this->docParser = $app['translator.doc_parser'];
        $this->phpParser = $app['translator.php_parser'];
        $this->traverser = $app['translator.php_traverser'];
        $this->traverser->addVisitor($this);
    }

    /**
     * {@inheritDoc}
     */
    public function extract($directory, MessageCatalogue $catalogue)
    {
        if (null === $this->phpParser) {
            return;
        }

        $files = $this->findFiles($directory.'/*.php');
        foreach ($files as $file) {
            try {
                $ast = $this->phpParser->parse(file_get_contents($file));
            } catch (\PHPParser_Error $e) {
                throw new \RuntimeException(sprintf('Could not parse "%s": %s', $file, $e->getMessage()), $e->getCode(), $e);
            }

            $this->file = $file;
            $this->catalogue = $catalogue;
            $this->traverser->traverse($ast);
        }
    }

    public function beforeTraverse(array $nodes)
    {
        return;
    }

    public function enterNode(\PHPParser_Node $node)
    {
        if (!$node instanceof \PHPParser_Node_Expr_MethodCall
            || !is_string($node->name)
            || ('trans' !== strtolower($node->name) && 'transchoice' !== strtolower($node->name))) {

            $this->previousNode = $node;

            return;
        }

        $ignore = false;
        $desc = $meaning = null;
        if (null !== $docComment = $this->getDocCommentForNode($node)) {
            foreach ($this->docParser->parse($docComment, 'file '.$this->file.' near line '.$node->getLine()) as $annotation) {
                if ($annotation instanceof Ignore) {
                    $ignore = true;
                } elseif ($annotation instanceof Desc) {
                    $desc = $annotation->text;
                } elseif ($annotation instanceof Meaning) {
                    $meaning = $annotation->text;
                }
            }
        }

        if (!$node->args[0]->value instanceof \PHPParser_Node_Scalar_String) {
            if ($ignore) {
                return;
            }

            $message = sprintf('Can only extract the translation id from a scalar string, but got "%s". Please refactor your code to make it extractable, or add the doc comment /** @Ignore */ to this code element (in %s on line %d).', get_class($node->args[0]->value), $this->file, $node->args[0]->value->getLine());

            if (null !== $this->logger) {
                $this->logger->error($message);

                return;
            }

            throw new \RuntimeException($message);
        }

        $id = $node->args[0]->value->value;

        $index = 'trans' === strtolower($node->name) ? 2 : 3;
        if (isset($node->args[$index])) {
            if (!$node->args[$index]->value instanceof \PHPParser_Node_Scalar_String) {
                if ($ignore) {
                    return;
                }

                $message = sprintf('Can only extract the translation domain from a scalar string, but got "%s". Please refactor your code to make it extractable, or add the doc comment /** @Ignore */ to this code element (in %s on line %d).', get_class($node->args[0]->value), $this->file, $node->args[0]->value->getLine());

                if (null !== $this->logger) {
                    $this->logger->error($message);

                    return;
                }

                throw new \RuntimeException($message);
            }

            $domain = $node->args[$index]->value->value;
        } else {
            $domain = 'messages';
        }

        $this->catalogue->set($id, $this->generateUntranslatedMessage($id), $domain);

        if ($desc) {
            $this->catalogue->setMetadata('desc.'.$id, $desc, $domain);
        }

        if ($meaning) {
            $this->catalogue->setMetadata('meaning.'.$id, $meaning, $domain);
        }
    }

    public function leaveNode(\PHPParser_Node $node)
    {
        return;
    }

    public function afterTraverse(array $nodes)
    {
        return;
    }

    protected function getDocCommentForNode(\PHPParser_Node $node)
    {
        // check if there is a doc comment for the ID argument
        // ->trans(/** @Desc("FOO") */ 'my.id')
        if (null !== $comment = $node->args[0]->getDocComment()) {
            return $comment;
        }

        // this may be placed somewhere up in the hierarchy,
        // -> /** @Desc("FOO") */ trans('my.id')
        // /** @Desc("FOO") */ ->trans('my.id')
        // /** @Desc("FOO") */ $translator->trans('my.id')
        if (null !== $comment = $node->getDocComment()) {
            return $comment;
        } elseif (null !== $this->previousNode) {
            return $this->previousNode->getDocComment();
        }

        return null;
    }
}
