<?php

namespace App\Domain\Content\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

class HtmlSanitizer
{
    private const ALLOWED_TAGS = ['p', 'br', 'strong', 'em', 'u', 's', 'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'blockquote', 'a'];

    private const ALLOWED_ATTRIBUTES = ['href', 'title', 'target', 'rel'];

    public function sanitize(string $html): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div id="content-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $root = (new DOMXPath($document))->query('//*[@id="content-root"]')->item(0);
        if (! $root) {
            return '';
        }
        $this->cleanChildren($root);
        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        return trim($output);
    }

    private function cleanChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }
            $tag = strtolower($node->tagName);
            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form'], true)) {
                    $parent->removeChild($node);
                } else {
                    $this->cleanChildren($node);
                    $this->unwrap($node);
                }

                continue;
            }
            foreach (iterator_to_array($node->attributes) as $attribute) {
                if (! in_array(strtolower($attribute->name), self::ALLOWED_ATTRIBUTES, true)) {
                    $node->removeAttribute($attribute->name);
                }
            }
            if ($tag === 'a') {
                $this->cleanLink($node);
            }
            $this->cleanChildren($node);
        }
    }

    private function cleanLink(DOMElement $link): void
    {
        $href = trim($link->getAttribute('href'));
        if ($href !== '' && ! preg_match('/^(https?:\/\/|mailto:|tel:|\/|#)/i', $href)) {
            $link->removeAttribute('href');
        }
        if ($link->getAttribute('target') === '_blank') {
            $link->setAttribute('rel', 'noopener noreferrer');
        } elseif ($link->hasAttribute('target')) {
            $link->removeAttribute('target');
        }
    }

    private function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;
        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }
        $parent->removeChild($element);
    }
}
