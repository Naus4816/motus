<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ArticleRepository;
use App\Entity\Article;
use App\Form\ArticleType;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'app_blog')]
    public function index(
        ArticleRepository $articleRepository
    ): Response
    {
        return $this->render('blog/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[Route('/blog/new', name: 'app_blog_new')]
    public function new(
        EntityManagerInterface $entityManager,
        Request $request
    ): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request); 
        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();

            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Votre article a été créé'
            );
            
            return $this->redirectToRoute('app_blog');
        }


        return $this->render('blog/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/blog/{slug}/edit', name: "app_blog_article_edit")]
    public function edit(
        EntityManagerInterface $entityManager,
        Request $request,
        Article $article
    ): Response
    {
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request); 
        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();

            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Vos changements ont été pris en compte!'
            );
            
            return $this->redirectToRoute('app_blog_article', ['slug' => $article->getSlug()]);
        }

        return $this->render('blog/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/blog/{slug}/delete', name: "app_blog_article_delete")]
    public function delete(
        EntityManagerInterface $entityManager,
        Article $article
    ): Response
    {
        $entityManager->remove($article);
        $entityManager->flush();
        $this->addFlash(
            'success',
            "L'article a été correctement supprimé"
        );
        return $this->redirectToRoute('app_blog');
    }

    #[Route('/blog/ {slug}', name: "app_blog_article")]
    public function show(
        Article $article
    ): Response
    {
        return $this->render('blog/show.html.twig', [
            'article' => $article,
        ]);
    }
}
