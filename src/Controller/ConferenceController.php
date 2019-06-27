<?php


namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Conference;
use App\Entity\User;
use App\Form\ConferenceType;
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
 * @Route(path="/")
 */
class ConferenceController extends AbstractController
{
    /**
     * @Route(path="/find/{id}",name="conference")
     * @param int $id
     */
    public function find(int $id): Response
    {
        /** @var ConferenceRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Conference::class);
        $conference = $repository->find($id);
        return new Response($conference->getTitle());
    }

    /**
     * @Route("/view-conference/{id}",name="view_conference")
     */
    public function viewAction(Conference $conference) :Response {
    /** @var Conference $conference */
        $conference = $this->getDoctrine()
            ->getRepository(Conference::class)
            ->find($conference->getId());

        if (!$conference) {
            throw $this->createNotFoundException(
                'There are no articles with the following id: ' . $conference->getId()
            );
        }

        return $this->render(
            'conference/view.html.twig',
            ['conference' => $conference]
        );
    }

    /**
     * @param PaginatorInterface $paginator
     * @return Response
     * @Route(path="/",name="conference_index")
     */
    public function index(Request $request, PaginatorInterface $paginator) :Response
    {

        $isAdmin=false;
        if ($this->getUser()){
            $isAdmin = in_array('ROLE_ADMIN', $this->getUser()->getRoles(),true) ? true : false;
        }
        /** @var ConferenceRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Conference::class);
        if (!$isAdmin){
            $conferences = $repository->queryForUser();
            $conferences = $paginator->paginate($conferences, $request->query->getInt('page', 1), 5);
            return $this->render('conference/conference.html.twig', [
                'conferences' => $conferences
            ]);
        }else{
            $conferences = $repository->queryForAdmin();
            $conferences = $paginator->paginate($conferences, $request->query->getInt('page', 1), 10);
            return $this->render('conference/admin.html.twig', [
                'conferences' => $conferences
            ]);
        }
    }

    /**
     * @Route(path="/admin/create",name="create")
     */
    public function create(Request $request) :Response{
        $conf = new Conference();
        $newCoForm = $this->createForm(ConferenceType::class,$conf);
        $newCoForm->handleRequest($request);
        if ($newCoForm->isSubmitted() && $newCoForm->isValid()) {
            $conf->setCreationDate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($newCoForm->getData());
            $em->flush();
        }
        return $this->render('conference/create.html.twig',['newCoForm'=>$newCoForm->createView()]);
    }

    /**
     * @Route(path="/admin/update/{id}",name="update_conference")
     */
    public function update(Request $request,Conference $conference){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository(Conference::class)->find($conference->getId());
        if (!$entity){
            $this->createNotFoundException('Can not update conference not found');
        }
        $newCoForm = $this->createForm(ConferenceType::class,$entity);
        $newCoForm->handleRequest($request);
        if ($newCoForm->isSubmitted() && $newCoForm->isValid()){
            /** @var Conference $conference */
            $conference = $newCoForm->getData();
            $em->flush();
            return $this->redirectToRoute('view_conference',['id'=>$conference->getId()]);
        }
        return $this->render('conference/create.html.twig',['newCoForm'=> $newCoForm->createView()]);
    }

    /**
     * @Route(path="/admin/delete/{id}",name="delete_conference")
     */
    public function delete(Request $request, Conference $confrence) :Response{
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository(Conference::class)->find($confrence->getId());
        if (!$entity){
            $this->createNotFoundException('can not delete conference not found');
        }
        $em->remove($confrence);
        $em->flush();
        return $this->redirectToRoute('conference_index');
    }

}
