<?php


namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Conference;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ConferenceController
 * @package App\Controller
 * @Route(path="/",name="conference")
 */
class ConferenceController extends AbstractController
{
    /**
     * @Route(path="/get/{id}")
     * @param int $id
     */
    public function find(int $id): Response
    {
        /** @var ConferenceRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Conference::class);
        $article = $repository->find($id);
        return new Response("Test");
    }

    /**
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function index(Request $request, PaginatorInterface $paginator) :Response
    {
        /** @var ConferenceRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Conference::class);
        $conferences = $repository->createQueryBuilder('a')
            ->orderBy('a.creationDate', 'DESC')
            ->getQuery();
        $conferences = $paginator->paginate($conferences, $request->query->getInt('page', 1), 10);
        return $this->render('Conference/conference.html.twig', [
            'conferences' => $conferences
        ]);
    }
}
