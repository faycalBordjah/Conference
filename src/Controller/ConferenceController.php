<?php


namespace App\Controller;

use App\Entity\Conference;
use App\Entity\User;
use App\Form\ConferenceType;
use App\Repository\ConferenceRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class ConferenceController
 * @package App\Controller
 * @Route(path="/")
 */
class ConferenceController extends AbstractController
{

    /**
     * @param PaginatorInterface $paginator
     * @return Response
     * @Route(path="/index",name="conference_index")
     */
    public function index(Request $request, PaginatorInterface $paginator): Response
    {

        /** @var ConferenceRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Conference::class);
        $isUser = $this->isUser();
        if (!$this->isAdmin()) {
            $conferences = $repository->queryForUser();
            $conferences = $paginator->paginate($conferences, $request->query->getInt('page', 1), 5);
            $user = $this->getUser();
            return $this->render('conference/user-conference.html.twig', [
                'isAdmin' => $this->isAdmin(),
                'user' => $user,
                'isUser' => $isUser,
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
     * @return Response
     * @Route(path="/account/profile", name="profile")
     */
    public function profile()
    {
        $user = $this->getUser();
        $isUser = $this->isUser();
        $isAdmin = $this->isAdmin();
        return $this->render('user/profile.html.twig', [
            'isAdmin' => $isAdmin,
            'isUser' => $isUser,
            'user' => $user
        ]);
    }

    /**
     * @return bool
     */
    private function isAdmin(): bool
    {
        $isAdmin = false;
        if ($this->getUser()) {
            $isAdmin = in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true) ? true : false;
        }
        return $isAdmin;
    }

    /**
     * @return bool
     */
    private function isUser(): bool
    {
        $isUser = false;
        if ($this->getUser()) {
            $isUser = in_array('ROLE_USER', $this->getUser()->getRoles(), true) ? true : false;
        }
        return $isUser;
    }

    /**
     * @Route(path="/about", name="about")
     */
    public function about()
    {
        return $this->render('conference/about.html.twig', ['isUser' => $this->isUser(),
            'isAdmin' => $this->isAdmin()]);
    }

    /**
     * @Route(path="/account/find/{id}",name="conference")
     * @param int $id
     * @return Response
     */
    public function find(int $id): Response
    {
        /** @var ConferenceRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Conference::class);
        $conference = $repository->find($id);
        return $this->redirectToRoute('view_conference', ['id' => $conference->getId()]);
    }

    /**
     * @Route("view-conference/{id}",name="view_conference")
     * @param Conference $conference
     * @return Response
     */
    public function viewAction(Conference $conference): Response
    {
        $conference = $this->getDoctrine()
            ->getRepository(Conference::class)
            ->find($conference->getId());
        return $this->render(
            'conference/conference-view.html.twig',
            ['isUser' => $this->isUser(),
                'user' => $this->getUser(),
                'conference' => $conference,
                'isAdmin' => $this->isAdmin()]
        );
    }


    /**
     * @Route(path="/admin/create",name="create")
     * @param Request $request
     * @return Response
     */
    public function create(Request $request, \Swift_Mailer $mailer): Response
    {
        $conference = new Conference();
        $newCoForm = $this->createForm(ConferenceType::class, $conference);
        $newCoForm->handleRequest($request);
        if ($newCoForm->isSubmitted() && $newCoForm->isValid()) {
            $conference->setCreationDate(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($newCoForm->getData());
            $em->flush();
            $this->sendMailForAll();
            return $this->render('conference/success-create-conference.html.twig',
                ['conference'=> $conference,
                'isAdmin' => $this->isAdmin()]);

        }
        return $this->render(
            'conference/create-update-conference.html.twig',
            ['newCoForm' => $newCoForm->createView(),
                'isAdmin' => $this->isAdmin()]
        );
    }

    private function sendMailForAll()
    {

        $em = $this->getDoctrine()->getManager();
        /** @var User[] $users */
        $users = $em->getRepository(User::class)->findAll();
        $transport = (new \Swift_SmtpTransport('mailhog', 1025));
        $mailer = new \Swift_Mailer($transport);
        foreach ($users as $key => $user) {
            $message = (new \Swift_Message('Hello Email'))
                ->setFrom('admin@admin')
                ->setTo($user->getMail())
                ->setBody(
                    "a new conference was added to the web site you should consult it, it can interest you. \n Regards"
                );
            $mailer->send($message);
        }
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
            return $this->render('conference/success-create-conference.html.twig',
                ['conference'=> $conference,
                    'isAdmin' => $this->isAdmin()]);
        }
        return $this->render(
            'conference/create-update-conference.html.twig',
            ['newCoForm' => $newCoForm->createView(),
                'isAdmin' => $this->isAdmin()]
        );
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

    public function topTen(Request $request): Response
    {
        return $this->render('');
    }

    /**
     * @Route(path="/admin/users/findAll",name="users")
     * @return Response
     */
    public function findAllUsers()
    {
        /**@var \App\Repository\UserRepository $repository */
        $repository = $this->getDoctrine()->getRepository(User::class);
        $users = $repository->findAll();
        return $this->render('user/users.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route(path="/admin/users/find/{id}",name="user")
     * @return Response
     */
    public function findUser(User $user): Response
    {
        /**@var \App\Repository\UserRepository $repository */
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->find($user->getId());
        $isUser = $this->isUser();
        $isAdmin = $this->isAdmin();
        return $this->render('user/profile.html.twig', [
            'isAdmin' => $isAdmin,
            'isUser' => $isUser,
            'user' => $user
        ]);
    }

    /**
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route(path="/admin/users/delete/{id}",name="user_delete")
     */
    public function deleteUser(Request $request, User $user): Response
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository(User::class)->find($user->getId());
        $roles = $user->getRoles();
        if (in_array('ROLE_ADMIN', $roles)) {
            throw new AccessDeniedException('Can not delete the administrator');
        }

        if (!$entity) {
            throw $this->createNotFoundException('user not found');
        }
        $em->remove($user);
        $em->flush();
        return $this->redirectToRoute('users');
    }


    /**
     * @param Request $request
     * @return Response
     * @Route(path="/search",name="search")
     */
    public function search(Request $request)
    {
        /** @var  ConferenceRepository $repo */
        $repo = $this->getDoctrine()->getManager()->getRepository(Conference::class);
        $conferences = [];
        $search = $request->request->get('search');
        if ($search == null) {
            return $this->redirectToRoute('conference_index');
        } else {
            /** @var Conference [] $results */
            $results = $repo->searchByTitle($search);
            foreach ($results as $datasearch) {
                $conference = $repo->find($datasearch->getId());
                $conferences [] = array(
                    'id' => $conference->getId(),
                    'title' => $conference->getTitle(),
                    'content' => $conference->getContent(),
                    'creationDate' => $conference->getCreationDate(),
                'date' => $conference->getDate(),
                    'place'=> $conference->getPlace());
            }
            return $this->render('conference/user-conference.html.twig',
                [
                    'isUser' => $this->isUser(),
                    'user' => $this->getUser(),
                    'conferences' => $conferences,
                    'isAdmin' => $this->isAdmin()
                ]
            );
        }

    }
}
