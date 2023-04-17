<?php
namespace App\Command;

use App\Entity\Account;
use App\Entity\APIToken;
use App\Entity\Billing;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Util\CategoryMainEnum;
use DateTime;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
        name: 'app:token-create',
        description: 'Create token for user',
    )]
class TokenCreateCommand extends Command
{

    public function __construct(
        private UserRepository $user,
        private string $saltWord,
        private array $availableLangs
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('user_email', InputArgument::REQUIRED, 'User email')
            ->addArgument('user_password', InputArgument::REQUIRED, 'User password')
            ->addArgument('account_balance', InputArgument::REQUIRED, 'User account balance')
            ->addArgument('user_category', InputArgument::OPTIONAL, 'User category', [CategoryMainEnum::CRYPTO->value]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userEmail = $input->getArgument('user_email');
        $userPassword = $input->getArgument('user_password');
        $userCategory = $input->getArgument('user_category');
        $accountBalance = $input->getArgument('account_balance');

        $user = $this->user->findOneBy(['email' => $userEmail]);

        if (!$user) {
            $io->info('Try to create user');
            $user = (new User())->setEmail($userEmail)
                ->setLang($this->availableLangs)
                ->setContextCategory($userCategory)
                ->setPassword($userPassword);
            $this->user->save($user, true);
        }

        $user->setAccount((new Account())->setBalance($accountBalance)
                ->addBilling((new Billing())
                    ->setCustomer($user)
                    ->setType(Billing::TYPE_DEPOSIT)
                    ->setSystem(Billing::SYSTEM)
                    ->setSum($accountBalance)
        ));

        $hash = md5($user->getEmail() . $this->saltWord);
        $apiToken = (new APIToken())->setIsValid(true)
            ->setDate(new DateTime('now +1 year'))
            ->setToken($hash);

        $user->addAPIToken($apiToken);

        $this->user->save($user, true);

        $io->success('Done. Hash: ' . $hash);

        return Command::SUCCESS;
    }
}
