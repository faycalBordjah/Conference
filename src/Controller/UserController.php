<?php


namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class UserController
 * @package App\Controller
 * @Route(path="/admin/users")
 */
class UserController extends AbstractController
{
    /**
     * @Route(path="/findAll",name="users")
     * @return Response
     */
    public function findAll()
    {
        /**@var \App\Repository\UserRepository $repository */
        $repository = $this->getDoctrine()->getRepository(User::class);
        $users = $repository->findAll();
        return $this->render('user/users.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route(path="/find/{id}",name="user")
     * @return Response
     */
    public function find(User $user): Response
    {
        /**@var \App\Repository\UserRepository $repository */
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->find($user->getId());
        return $this->render('user/user.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route(path="/delete/{id}",name="user_delete")
     */
    public function delete(Request $request, User $user): Response
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
}
