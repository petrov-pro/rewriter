<?php
namespace App\Command;

use App\MessageHandler\RewriteHandler;
use App\Request\Cryptonews\DTO\NewsDTO;
use App\Service\AI\OpenAIService;
use App\Util\HtmlTagEnum;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function dd;

#[AsCommand(
        name: 'app:test',
        description: 'Add a short description for your command',
    )]
class TestCommand extends Command
{

    public function __construct(
        private RewriteHandler $t,
        private OpenAIService $o
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $message = (new NewsDTO())
            ->setSourceName('my')
            ->setId(31)
            ->setTitle('What is WordCounter?')
            ->setDescription("Gary Gensler is facing a barrage of criticism over his agency's crackdown on crypto platforms.")
            ->setText("<p>The United States Treasury did a risk assessment of decentralized finance (DeFi) and found the sector lacking in several ways, Assistant Treasury Secretary for Terrorist Financing and Financial Crime Elizabeth Rosenberg reminded an audience at the Atlantic Council think tank on April 21. Get ready for more regulation, she said.</p><p>Rosenberg was referring to a report released earlier in April by the Treasury that found scammers, money launderers and North Korean hackers benefitting from the lack of Anti-Money Laundering (AML) and Countering the Financing of Terrorism (CFT) compliance in the sector. That report was part of the Treasuryas response to U.S. President Joe Bidenas executive order on the responsible development of digital assets. </p><p>The report also found that DeFi was not always very decentralized. aThere are generally persons and firms associated with those [DeFi] services to which AML/CFT obligations may already apply,a Rosenberg said. The assessment report established that all DeFi services are liable to comply with the Bank Secrecy Act, including AML/CFT.</p><p>aWe will assess enhancements to our domestic AML/CFT regulatory regime as applied to DeFi services and monitor responsible innovation of AML/CFT and sanctions compliance tools,a Rosenberg said. She continued:</p><blockquote>aI want to offer a specific message to the private sector. aDeFi innovationa should not only occur in the technical, financial domain a there is an enormous need and potential for innovation in compliance mechanisms that could help all players in the digital ecosystem ensure they remain on the right side of the law.a</blockquote><p>Rosenberg and her team were freshly back from the Financial Action Task Force (FATF) Virtual Assets Contact Group meeting in Tokyo, she said. The team presented the results of the Treasuryas DeFi risk assessment there as well. </p><p>Related: FATF agrees on roadmap or implementation of crypto standards</p><p>The timing of Rosenbergas speech is also notable because the European Parliament passed the Markets in Crypto-Assets legislation a day earlier. The MiCA legislation included provisions for tracing or blocking certain payments using crypto assets. This AML/CFT practice is already used in traditional finance and is known as the aTravel Rulea by the FATF. It was also a key part of the Treasuryas risk assessment. </p>")
            ->setSiteId(8)
            ->setLang("ru")
            ->setUserId(4);

        $t1 = $this->t->handle($message);
        dd($t1);

        $io->success('done');

        return Command::SUCCESS;
    }
}
