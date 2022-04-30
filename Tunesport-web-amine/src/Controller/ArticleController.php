<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commentaires;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/", name="app_article_index", methods={"GET"})
     */
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    /**
     * @Route("/listArticle", name="app_article_list", methods={"GET"})
     */
    public function listArticle(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/listArticle.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }



    /**
     * @Route("/new", name="app_article_new", methods={"GET", "POST"})
     */
    public function new(Request $request, ArticleRepository $articleRepository): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file =$request->files->get('article')['my_file'];
            $uploads_directory= $this->getParameter('uploads_directory');
            $filename = md5(uniqid()) . '.' .$file->guessExtension();
            $file->move(
                $uploads_directory,$filename
            );
            $article->setImageArticle($filename);
            $articleRepository->add($article);
            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }



    /**
     * @Route("/{idArticle}", name="app_article_show", methods={"GET"})
     */
    public function show(EntityManagerInterface $entityManager, Article $article): Response
    {
        $commentaires = $entityManager
            ->getRepository(Commentaires::class)
            ->findAll();
        return $this->render('article/show.html.twig', [
            'article' => $article, 'commentaires'=>$commentaires,
        ]);
    }

    /**
     * @Route("/{idArticle}/edit", name="app_article_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Article $article, ArticleRepository $articleRepository): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $articleRepository->add($article);
            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{idArticle}", name="app_article_delete", methods={"POST"})
     */
    public function delete(Request $request, Article $article, ArticleRepository $articleRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getIdArticle(), $request->request->get('_token'))) {
            $articleRepository->remove($article);
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }




}


