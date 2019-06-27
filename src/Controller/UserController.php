<?php


namespace App\Controller;


use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

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
    public function findAll(){
        /**@var \App\Repository\UserRepository $repository*/
        $repository = $this->getDoctrine()->getRepository(User::class);
        $users = $repository->findAll();
        return $this->render('User/users.html.twig',[
            'users'=> $users
        ]);
    }

    /**
     * @Route(path="/find/{id}",name="user")
     * @return Response
     */
    public function find(User $user) :Response{
        /**@var \App\Repository\UserRepository $repository*/
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->find($user->getId());
     return $this->render('User/user.html.twig',[
            'user'=> $user
        ]);
    }
}