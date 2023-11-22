<?php namespace App\EntityListener;

use App\Entity\Article;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;  
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, entity: Article::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Article::class)]
class ArticleEntityListener
{
    public function __construct(
        private SluggerInterface $slugger,
    ) {
    }

    public function prePersist(Article $article, LifecycleEventArgs $event)
    {
        $article->computeSlug($this->slugger);
    }

    public function preUpdate(Article $article, LifecycleEventArgs $event)
    {
        $article->computeSlug($this->slugger);
    }
}
?>