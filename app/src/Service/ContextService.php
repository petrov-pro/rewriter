<?php
namespace App\Service;

use App\Entity\Context;
use App\Entity\Translate;
use App\MessageHandler\Message\ContextInterface;
use App\Repository\ContextRepository;
use App\Util\SentimentEnum;
use App\Util\TypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContextService
{

    public const STATUS_INIT = 'init';
    public const STATUS_FINISH = 'finish';
    public const STATUS_PARSER_NOT_FOUND = 'parser_not_found';
    public const TYPE_ORIGINAL = 'original';
    public const TYPE_MODIFY = 'modify';

    public function __construct(
        private ContextRepository $contextRepository,
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager
    )
    {
        
    }

    public function findOrCreate(Context $context): Context
    {
        $this->validate($context);
        $contextOriginal = $this->findOneByHash($context->getHash());
        if ($contextOriginal) {
            return $contextOriginal;
        }
        $this->contextRepository->save($context);
        return $context;
    }

    public function save(Context $entity, bool $flush = true): void
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

    public function isDuplicate(string $title): bool
    {
        $context = $this->contextRepository->findOneByHash(md5($title));
        return !empty($context);
    }

    public function create(ContextInterface $context): Context
    {
        $contextEntity = new Context();
        $contextEntity->setCategory($context->getCategory())
            ->setDate($context->getDate())
            ->setHash($this->prepareHash($context->getTitle()))
            ->setImageUrl($context->getImageUrl())
            ->setSentiment($this->prepareSentiment($context->getSentiment()))
            ->setSourceName($context->getSourceName())
            ->setSourceUrl($context->getSourceUrl())
            ->setStatus(self::STATUS_INIT)
            ->setTitle($context->getTitle())
            ->setType($this->prepareType($context->getType()));

        $this->save($contextEntity);

        return $contextEntity;
    }

    public function saveModifyContext(
        int $id,
        string $text,
        string $textDescription,
        string $textTitle,
        string $transletTo,
        string $imageUrl = '',
        string $type = self::TYPE_MODIFY,
        string $status = null
    ): Context
    {
        $contextEntity = $this->contextRepository->find($id);
        if (!$contextEntity) {
            throw new InvalidArgumentException('Can not find context entity: ' . $id);
        }

        if ($status) {
            $contextEntity->setStatus($status);
        }

        if ($imageUrl) {
            $contextEntity->setImageUrl($imageUrl);
        }

        $translateOriginal = new Translate();
        $translateOriginal->setTitle($textTitle)
            ->setDescription($textDescription)
            ->setText($text)
            ->setType($type)
            ->setLang($transletTo);

        $contextEntity->addTranslate($translateOriginal);
        $this->save($contextEntity);

        return $contextEntity;
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

    private function prepareHash(string $param): string
    {
        return md5($param);
    }

    private function validate(Context $context): void
    {
        $errors = $this->validator->validate($context);
        if (count($errors) > 0) {
            throw new ValidationFailedException($errors, $errors);
        }
    }

    public function updateStatus(int $id, string $status): Context
    {
        $context = $this->contextRepository->find($id);
        if (!$context) {
            throw new InvalidArgumentException('Not found entity');
        }
        $context->setStatus($status);
        $this->save($context);

        return $context;
    }
}
