<?php
namespace App\Tests;

use App\Service\FlareSolverrService;
use App\Service\Parser\ParserFactory;
use App\Service\Parser\SiteParser\Common;
use App\Service\Parser\StructureParserInterface;
use Nette\Utils\FileSystem;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use function PHPUnit\Framework\once;

class CommonParserTest extends KernelTestCase
{

    private MockObject $mockSource;
    private ParserFactory $parserFactory;

    public function sample()
    {
        return [
            [
                [
                    'site' => 'LiveBitcoinNews',
                    'contain' => [
                        'The federal government is looking to regulate crypto mining',
                        'if miners disclosed all data regarding their resources'
                    ],
                    'notContain' => [
                        '+ Leave a Comment',
                        'TAGS:'
                    ]
                ],
            ],
            [
                [
                    'site' => 'The Currency Analytics',
                    'contain' => [
                        'chances of success in 2023 and beyond.',
                        'As the cryptocurrency market continues to'
                    ],
                    'notContain' => [
                        'Post Views:',
                        'Read more about:',
                        'Cryptocurrency Profits in 2023: The Ultimate Guide to Making Money in the Crypto Market'
                    ]
                ],
            ],
            [
                [
                    'site' => 'Coincu',
                    'contain' => [
                        'After the fall of Signature Bank last month, Binance.US is trying to find a bank to handle its',
                        'Signature Bank left Binance.US without banking facilities, relying on middleman’s banks to keep cash on its behalf.',
                        'constitute investment advice. We encourage you to do your own research before investing.',
                    ],
                    'notContain' => [
                        'Join us to keep track of',
                        'Key Points:',
                        'Binance.US has struggled to find a bank to hold its',
                        'Harold'
                    ]
                ],
            ],
            [
                [
                    'site' => 'CryptoPotato',
                    'contain' => [
                        'If you have been in the crypto sphere for some time, there are two things you should already know: ',
                        'and users might never be able to recover them. However, he ask',
                        'be a brand new, exciting development for the Bitcoin ecosystem?'
                    ],
                    'notContain' => [
                        'You Might Also Like:'
                    ]
                ]
            ],
            [
                [
                    'site' => 'Cryptopolitan',
                    'contain' => [
                        'e United States arm of cryptocurrency exchange Binance, is facing a major obstacle as it struggles to find a bank to handle its customers’ cash.',
                        'In other news, Binance has turned down an offer to acquire Tron blockchain founder Justin Sun’s ownership stake in rival exchange Huobi. Binance wasn’t interested because of rumors that ',
                        ' with a qualified professional before making any investment decision.'
                    ],
                    'notContain' => [
                        'Jai Hamid is an enthusiastic writer whose current area of interest',
                        'Banks are reportedly reluctant to partner with the company due to concerns over regulatory risk.'
                    ]
                ]
            ],
            [
                [
                    'site' => 'Blockchain News',
                    'contain' => [
                        "The Pheu Thai Party, Thailand's political opposition, has announced a proposal to give every citizen of the country nearly $300 in digital currency should the party win the",
                        "The proposed crypto project could potentially cost the government between $14 billion to $18 billion, given that Thailand's population is ",
                        " as well as the potential risks and challenges that may arise in the implementation process."
                    ],
                    'notContain' => [
                        'PHEU THAI PARTY',
                        'Min Read'
                    ]
                ]
            ],
            [
                [
                    'site' => 'Bitcoin',
                    'contain' => [
                        "Texas Lawmakers Introduce Bill Proposing to Establish a ",
                        "edemption in gold of all units of the digital currency that have been issued and a",
                        "currency to any other person electronically."
                    ],
                    'notContain' => [
                        'TAGS IN THIS STORY',
                        'priation, liberty dollar, Lone Star State, Marjorie Tay',
                        ' could potentially affect the future of alternative currencies in the U',
                        'Image Credits'
                    ]
                ]
            ],
            [
                [
                    'site' => 'Zycrypto',
                    'contain' => [
                        "Binance, the world’s largest cryptocurrency exchange, has aided the ",
                        "Despite some lost funds being tracked, victims o",
                        "cryptocurrency industry by whatever means, including using FUD."
                    ],
                    'notContain' => [
                        'TAGS',
                        'Bone Shibasw',
                        'Binance Backs US Authorities in',
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider sample
     */
    public function testSite(array $siteData): void
    {
        $sample = FileSystem::read('./tests/resourses/' . $siteData['site'] . '.html');
        $this->mockSource
            ->expects(once())
            ->method("getData")
            ->willReturn($sample);

        $parser = $this->parserFactory->create($siteData['site']);
        $result = $parser->parser('test.com');

        $this->assertNotEmpty($result);
        foreach ($siteData['notContain'] as $value) {
            $this->assertStringNotContainsString($value, $result);
        }

        foreach ($siteData['contain'] as $value) {
            $this->assertStringContainsString($value, $result);
        }
    }

    protected function setUp(): void
    {

        self::bootKernel([
            'environment' => 'test',
            'debug' => true,
        ]);
        $dom = static::getContainer()->get(StructureParserInterface::class);
        $this->mockSource = $this->createMock(FlareSolverrService::class);
        $this->parserFactory = new ParserFactory([
            new Common($this->mockSource, $dom)
        ]);
        parent::setUp();
    }
}
