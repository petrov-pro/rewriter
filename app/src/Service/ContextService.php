<?php
namespace App\Service;

use App\Entity\Context;
use App\Entity\Translate;
use App\MessageHandler\Message\ContextInterface;
use App\Repository\ContextRepository;
use App\Repository\SiteRepository;
use App\Repository\TranslateRepository;
use App\Repository\UserRepository;
use App\Util\SentimentEnum;
use App\Util\TypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContextService
{

    public function __construct(
        private ContextRepository $contextRepository,
        private TranslateRepository $translateRepository,
        private UserRepository $userRepository,
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager,
        private SiteRepository $siteRepository
    )
    {
        
    }

    public function save(Context $entity, bool $flush = false): void
    {
        $this->validate($entity);
        $this->entityManager->persist($entity);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function remove(Context $entity, bool $flush = false): void
    {
        $this->entityManager->remove($entity);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function isDuplicate(string $title, string $source): bool
    {
        $context = $this->contextRepository->findOneByTitleSource($title, $source);
        return !empty($context);
    }

    public function create(ContextInterface $context): Context
    {
        $contextEntity = new Context();
        $contextEntity->setCategory($context->getCategory())
            ->setDate($context->getDate())
            ->setImageUrl($context->getImageUrl())
            ->setSentiment($this->prepareSentiment($context->getSentiment()))
            ->setSourceName($context->getSourceName())
            ->setSourceUrl($context->getSourceUrl())
            ->setDescription($context->getDescription())
            ->setLang($context->getLang())
            ->setTitle($context->getTitle())
            ->setStatus(Context::STATUS_INIT)
            ->setCategory($context->getCategory())
            ->setProvider($context->getProvider())
            ->setType($this->prepareType($context->getType()));

        $this->save($contextEntity, true);

        return $contextEntity;
    }

    public function saveModifyContext(
        int $id,
        int $userId,
        int $siteId,
        string $text,
        string $textDescription,
        string $textTitle,
        string $transletTo,
        int $token,
        bool $flush = true
    ): Context
    {
        $contextEntity = $this->findOrThrow($id);

        $translate = (new Translate())->setTitle($textTitle)
            ->setDescription($textDescription)
            ->setText($text)
            ->setCustomer($this->userRepository->findOrThrow($userId))
            ->setToken($token)
            ->setSite($this->siteRepository->findOrThrow($siteId))
            ->setLang($transletTo);

        $contextEntity->addTranslate($translate);
        $this->save($contextEntity, $flush);

        return $contextEntity;
    }

    public function updateStatus(int $id, string $status): Context
    {
        $context = $this->findOrThrow($id);
        $context->setStatus($status);
        $this->save($context, true);

        return $context;
    }

    public function updateStatusText(int $id, string $status, string $text): Context
    {
        $context = $this->findOrThrow($id);
        $context->setStatus($status);
        $context->setText($text);
        $this->save($context, true);

        return $context;
    }

    public function isDuplicateTranslate(int $userId, int $contextId, string $lang): bool
    {
        $translate = $this->translateRepository->findOneBy([
            'customer' => $userId,
            'context' => $contextId,
            'lang' => $lang
        ]);

        return $translate !== null;
    }

    public function findOrThrow(int $id): Context
    {
        $context = $this->contextRepository->find($id);
        if (!$context) {
            throw new InvalidArgumentException('Not found context: ' . $id);
        }

        return $context;
    }

    private function prepareSentiment(string $sentiment): string
    {
        return match ($sentiment) {
            'Positive' => SentimentEnum::POSITIVE->value,
            'Negative' => SentimentEnum::NEGATIVE->value,
            'Neutral' => SentimentEnum::NEUTRAL->value,
            default => $sentiment
        };
    }

    private function prepareType(string $type): string
    {
        return match ($type) {
            'Article' => TypeEnum::ARTICLE->value,
            default => $type,
        };
    }

    private function validate(Context $context): void
    {
        $errors = $this->validator->validate($context);
        if (count($errors) > 0) {
            throw new ValidationFailedException($errors, $errors);
        }
    }
}
