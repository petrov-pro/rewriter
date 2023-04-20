<?php
namespace App\Service\Parser\StructureParser;

use App\Service\Parser\StructureParserInterface;
use DOMDocument;
use DOMXPath;

class HtmlParser implements StructureParserInterface
{

    public function proccess(string $page, string $pattern, array $allowTag = [], array $skipWords = [], array $allowTagWithAttribute = []): string
    {
        return $this->handleResult($this->parse($page, $pattern, $allowTag), $skipWords, $allowTagWithAttribute);
    }

    public function parse(string $page, string $pattern, array $allowTag = []): mixed
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($page);

        $xpath = new DOMXPath($dom);

        if ($allowTag) {
            $pattern = $pattern . '/*[self::' . (implode(' or self::', $allowTag)) . ']';
        }

        return $xpath->query($pattern);
    }

    public function handleResult(mixed $nodes, array $skipWords = [], array $allowTagWithAttribute = []): string
    {
        $result = '';
        foreach ($nodes as $node) {
            foreach ($skipWords as $skipWord) {
                if (strpos($node->nodeValue, $skipWord) !== false) {
                    continue 2;
                }
            }

            if ($node->hasAttributes() && $allowTagWithAttribute) {
                $skipNode = true;
                foreach ($allowTagWithAttribute as $attr => $value) {
                    if ($node->getAttribute($attr) === $value) {
                        $skipNode = false;
                        break;
                    }
                }

                if ($skipNode) {
                    continue;
                }
            }

            $result .= "<$node->nodeName>" . $node->nodeValue . "</$node->nodeName>";
        }

        return $result;
    }
}
