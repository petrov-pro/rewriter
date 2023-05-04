<?php
namespace App\Service\Spread\WordPressCom\DTO;

use App\Service\Spread\DTO\BaseDTO;
use Symfony\Component\Validator\Constraints as Assert;

class PostCreateDTO extends BaseDTO
{

    #[Assert\NotBlank]
    protected string $site;

    #[Assert\NotBlank]
    protected string $token;
    private string $date;
    private string $status = 'draft';
    private string $title;
    private string $content;
    private string $excerpt;
    private string $format = 'standard';
    private array $categories;
    private string $author;
    private string $featured_media;
    private string $slug;
    private string $comment_status = 'closed';
    private string $ping_status;
    private array $meta;
    private string $sticky;
    private string $template;
    private array $tags;

    public function getDate(): string
    {
        return $this->date;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getExcerpt(): string
    {
        return $this->excerpt;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getFeaturedMedia(): string
    {
        return $this->featured_media;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getCommentStatus(): string
    {
        return $this->comment_status;
    }

    public function getPingStatus(): string
    {
        return $this->ping_status;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function getSticky(): string
    {
        return $this->sticky;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setDate(string $date)
    {
        $this->date = $date;
        return $this;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
        return $this;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }

    public function setContent(string $content)
    {
        $this->content = $content;
        return $this;
    }

    public function setExcerpt(string $excerpt)
    {
        $this->excerpt = $excerpt;
        return $this;
    }

    public function setFormat(string $format)
    {
        $this->format = $format;
        return $this;
    }

    public function setCategories(array $categories)
    {
        $this->categories = $categories;
        return $this;
    }

    public function setAuthor(string $author)
    {
        $this->author = $author;
        return $this;
    }

    public function setFeaturedMedia(string $featured_media)
    {
        $this->featured_media = $featured_media;
        return $this;
    }

    public function setSlug(string $slug)
    {
        $this->slug = $slug;
        return $this;
    }

    public function setCommentStatus(string $comment_status)
    {
        $this->comment_status = $comment_status;
        return $this;
    }

    public function setPingStatus(string $ping_status)
    {
        $this->ping_status = $ping_status;
        return $this;
    }

    public function setMeta(array $meta)
    {
        $this->meta = $meta;
        return $this;
    }

    public function setSticky(string $sticky)
    {
        $this->sticky = $sticky;
        return $this;
    }

    public function setTemplate(string $template)
    {
        $this->template = $template;
        return $this;
    }

    public function setTags(array $tags)
    {
        $this->tags = $tags;
        return $this;
    }
}
