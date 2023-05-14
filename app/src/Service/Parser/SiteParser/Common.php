<?php
namespace App\Service\Parser\SiteParser;

use App\Service\Parser\NotWantParserException;
use App\Service\Parser\SiteParserInterface;
use App\Service\Parser\StructureParserInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
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
            'pattern' => "//div[contains(@class, 'entry-content')]",
            'allowTag' => ['p', 'blockquote', 'h1', 'h3'],
            'skipWords' => [
                'Signup for our newsletter to stay in the loop.',
            ],
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
        'BeInCrypto' => [
            'pattern' => "//div[contains(@class, 'entry-content-inner')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
            ],
            'supportTagWithAttribute' => []
        ],
        'Dailycoin' => [
            'pattern' => "//div[contains(@class, 'mkd-post-text-inner')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
                'On the Flipside',
                'Why You Should Care'
            ],
            'supportTagWithAttribute' => []
        ],
        'The Daily Hodl' => [
            'pattern' => "//div[contains(@class, 'content-inner')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
                'Generated Image: Midjourney'
            ],
            'supportTagWithAttribute' => []
        ],
        'Crypto news' => [
            'pattern' => "//div[contains(@class, 'post-detail__content blocks')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
            ],
            'supportTagWithAttribute' => []
        ],
        'Bitcoin Magazine' => [
            'pattern' => "//div[contains(@class, 'm-detail--body')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
            ],
            'supportTagWithAttribute' => []
        ],
        'PYMNTS' => [
            'pattern' => "//div[contains(@class, 'lh-article')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
            ],
            'supportTagWithAttribute' => []
        ],
        'Crypto Daily' => [
            'pattern' => "//div[contains(@class, 'news-content')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
                'Disclaimer: This article is provided for informational purposes only. It is not offered or intended to be used as legal, tax, investment, financial, or other advice.'
            ],
            'supportTagWithAttribute' => []
        ],
        'CryptoGlobe' => [
            'pattern' => "//div[contains(@class, 'article-body')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
            ],
            'supportTagWithAttribute' => []
        ],
        'CrowdFundInsider' => [
            'pattern' => "//div[contains(@class, 'article')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
            ],
            'supportTagWithAttribute' => []
        ],
        'TheNewsCrypto' => [
            'pattern' => "//section[contains(@class, 'article-content-wrapper')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
                'More Details On The Key Findings'
            ],
            'supportTagWithAttribute' => []
        ],
        'Bitcoinworld' => [
            'pattern' => "//div[contains(@class, 'entry-content')]//div//div",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
            ],
            'supportTagWithAttribute' => []
        ],
        'CoinPedia' => [
            'pattern' => "//div[contains(@class, 'entry-content')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
            ],
            'supportTagWithAttribute' => []
        ],
        'Proactive Investors' => [
            'pattern' => "//div[contains(@class, 'ckeditor-content')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
            ],
            'supportTagWithAttribute' => [
            ]
        ],
        'The Cryptonomist' => [
            'pattern' => "//div[contains(@class, 'post-content')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
            ],
            'supportTagWithAttribute' => [
            ]
        ],
        'UToday' => [
            'pattern' => "//div[contains(@class, 'article__content')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
            ],
            'supportTagWithAttribute' => [
            ]
        ],
        'Crypto Briefing' => [
            'pattern' => "//section[contains(@class, 'article-content')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
                'More Details On The Key Findings'
            ],
            'supportTagWithAttribute' => []
        ],
        'AMBCrypto' => [
            'pattern' => "//div[contains(@class, 'single-post-main-middle')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
            ],
            'supportTagWithAttribute' => [
            ]
        ],
        'Coinspeaker' => [
            'pattern' => "//div[contains(@class, 'content')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
            ],
            'supportTagWithAttribute' => [
                'CRYPTOCURRENCY NEWS'
            ]
        ],
        'Coingape' => [
            'pattern' => "//div[contains(@class, 'c-content')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
            ],
            'supportTagWithAttribute' => [
            ]
        ],
        'NewsBTC' => [
            'pattern' => "//div[contains(@class, 'content-inner')]",
            'allowTag' => ['p', 'blockquote', 'h2', 'h1', 'h3'],
            'skipWords' => [
                '-Featured Image from '
            ],
            'supportTagWithAttribute' => [
            ]
        ],
    ];
    private const SOURCE_NAME_NOT_WANT = [
        'Benzinga',
        'The Block',
        'Coindesk',
        'CNBC Television'
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

        if (in_array($sourceName, self::SOURCE_NAME_NOT_WANT)) {
            throw new NotWantParserException('Parser does not support site: ' . $sourceName);
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
            throw new UnrecoverableMessageHandlingException('Can not find element for source: ' . $this->currentSourceName);
        }

        return strip_tags($content, $this->currentRule['allowTag']);
    }
}
