<?php
namespace App\Service\Parser;

class ParserFactory
{

    /**
     * @param SiteParserInterface[] $siteParsers
     */
    public function __construct(
        private iterable $siteParsers
    )
    {
        
    }

    public function create(string $sourceName): SiteParserInterface
    {
        foreach ($this->siteParsers as $parser) {
            if ($parser->isSupport($sourceName)) {
                return $parser;
            }
        }
        throw new NotFoundParserException('Not found parser for site: ' . $sourceName);
    }
}
