<?php
namespace App\Service\Parser\SiteParser;

use App\Service\Parser\SiteParserInterface;
use App\Service\Parser\StructureParserInterface;
use Exception;
use Webmozart\Assert\InvalidArgumentException;

class Common implements SiteParserInterface
{

    private array $currentRule = [];
    private string $currentSourceName;

    private const SOURCE_NAME = [
        'LiveBitcoinNews' => [
            'pattern' => "//div[contains(@class, 'post-content')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [],
            'supportTagWithAttribute' => []
        ],
        'The Currency Analytics' => [
            'pattern' => "//div[contains(@class, 'single_content_detail')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [],
            'supportTagWithAttribute' => []
        ],
        'Coincu' => [
            'pattern' => "//div[contains(@class, 'content-inner')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3', 'h5'],
            'skipWords' => [
                'Key Points:',
                'Join us to keep track of news:'
            ],
            'supportTagWithAttribute' => [
                'class' => 'wp-block-heading'
            ]
        ],
        'CryptoPotato' => [
            'pattern' => "//div[contains(@class, 'coincodex-content')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [],
            'supportTagWithAttribute' => []
        ],
        'Cryptopolitan' => [
            'pattern' => "//div[contains(@class, 'elementor-widget-container')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [],
            'supportTagWithAttribute' => []
        ],
        'Blockchain News' => [
            'pattern' => "//div[contains(@class, 'textbody')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [],
            'supportTagWithAttribute' => []
        ],
        'Bitcoin' => [
            'pattern' => "//article[contains(@class, 'article__body')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3', 'header'],
            'skipWords' => [
                'What do you think the future',
                'What are your thoughts',
                'in the comments section below.',
                'Image Credits'
            ],
            'supportTagWithAttribute' => []
        ],
        'Zycrypto' => [
            'pattern' => "//div[contains(@class, 'td-post-content')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [],
            'supportTagWithAttribute' => []
        ],
        'Bitcoinist' => [
            'pattern' => "//div[contains(@class, 'content-inner')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => ['Featured image from Pixabay and chart'],
            'supportTagWithAttribute' => []
        ],
        'Business Insider' => [
            'pattern' => "//div[contains(@class, 'content-lock-content')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [],
            'supportTagWithAttribute' => []
        ],
        'Cointelegraph' => [
            'pattern' => "//div[contains(@class, 'post-content')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
                'Magazine: '
            ],
            'supportTagWithAttribute' => []
        ],
        'Fox Business' => [
            'pattern' => "//div[contains(@class, 'article-body')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
                'READ MORE ABOUT'
            ],
            'supportTagWithAttribute' => []
        ],
        'Blockworks' => [
            'pattern' => "//section[contains(@class, 'w-full')]//div",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
                'Get the day’s top crypto news and insights delivered to your ',
                'Want alpha sent directly to your inbox? ',
                'Can’t wait? Get our news the fastest way possible'
            ],
            'supportTagWithAttribute' => []
        ],
        'Invezz' => [
            'pattern' => "//div[contains(@class, 'font-source')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
            ],
            'supportTagWithAttribute' => []
        ],
    ];

    public function __construct(
        private StructureParserInterface $parser
    )
    {
        
    }

    public function isSupport(string $sourceName): bool
    {
        if (isset(self::SOURCE_NAME[$sourceName])) {
            $this->currentSourceName = $sourceName;
            $this->currentRule = self::SOURCE_NAME[$sourceName];

            return true;
        }

        return false;
    }

    public function parser(string $data): string
    {
        if (!$this->currentRule) {
            throw new InvalidArgumentException('Current rule for provider context not define.');
        }

        $content = $this->parser->proccess(
            $data,
            $this->currentRule['pattern'],
            $this->currentRule['allowTag'],
            $this->currentRule['skipWords'],
            $this->currentRule['supportTagWithAttribute']
        );

        if (empty($content)) {
            throw new Exception('Can not find element for source: ' . $this->currentSourceName);
        }

        return strip_tags($content, $this->currentRule['allowTag']);
    }
}
