<?php


namespace App\Controller;

use App\Entity\Conference;
use App\Form\ConferenceType;
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
     * @Route(path="/about", name="about")
     */
    public function about()
    {
        return $this->render('conference/about.html.twig');
    }

    /**
     * @Route(path="/find/{id}",name="conference")
     * @param int $id
     * @return Response
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
     * @param Conference $conference
     * @return Response
     */
    public function viewAction(Conference $conference): Response
    {
        $conference = $this->getDoctrine()
            ->getRepository(Conference::class)
            ->find($conference->getId());
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
    public function index(Request $request, PaginatorInterface $paginator): Response
    {

        $isAdmin = false;
        if ($this->getUser()) {
            $isAdmin = in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true) ? true : false;
        }
        /** @var ConferenceRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Conference::class);
        if (!$isAdmin) {
            $conferences = $repository->queryForUser();
            $conferences = $paginator->paginate($conferences, $request->query->getInt('page', 1), 5);
            return $this->render('conference/user-conference.html.twig', [
                'conferences' => $conferences
            ]);
        } else {
            $conferences = $repository->queryForAdmin();
            $conferences = $paginator->paginate($conferences, $request->query->getInt('page', 1), 10);
            return $this->render('conference/admin-conference.html.twig', [
                'conferences' => $conferences
            ]);
        }
    }

    /**
     * @Route(path="/admin/create",name="create")
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $conf = new Conference();
        $newCoForm = $this->createForm(ConferenceType::class, $conf);
        $newCoForm->handleRequest($request);
        if ($newCoForm->isSubmitted() && $newCoForm->isValid()) {
            $conf->setCreationDate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($newCoForm->getData());
            $em->flush();
        }
        return $this->render('conference/create-update.html.twig', ['newCoForm' => $newCoForm->createView()]);
    }

    /**
     * @Route(path="/admin/update/{id}",name="update_conference")
     * @param Conference $conference
     * @return Response
     */
    public function update(Request $request, Conference $conference)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository(Conference::class)->find($conference->getId());
        if (!$entity) {
            $this->createNotFoundException('Can not update conference not found');
        }
        $newCoForm = $this->createForm(ConferenceType::class, $entity);
        $newCoForm->handleRequest($request);
        if ($newCoForm->isSubmitted() && $newCoForm->isValid()) {
            $conference = $newCoForm->getData();
            $em->flush();
            return $this->redirectToRoute('view_conference', ['id' => $conference->getId()]);
        }
        return $this->render('conference/create-update.html.twig', ['newCoForm' => $newCoForm->createView()]);
    }

    /**
     * @Route(path="/admin/delete/{id}",name="delete_conference")
     * @param Conference $confrence
     * @param Request $request
     */
    public function delete(Request $request, Conference $confrence): Response
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository(Conference::class)->find($confrence->getId());
        if (!$entity) {
            $this->createNotFoundException('can not delete conference not found');
        }
        $em->remove($confrence);
        $em->flush();
        return $this->redirectToRoute('conference_index');
    }
}
