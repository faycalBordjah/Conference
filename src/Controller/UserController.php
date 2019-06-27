<?php


namespace App\Controller;


use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserController
 * @package App\Controller
 */
class UserController extends AbstractController
{
    /**
     * @Route(path="/admin/findAll",name="users")
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
     * @Route(path="/find/{id}")
     * @return Response
     */
    public function find(int $id) :Response{
        /**@var \App\Repository\UserRepository $repository*/
        $repository = $this->getDoctrine()->getRepository(User::class);
        $users = $repository->find($id);
        var_dump($users);
        return new Response("user");
    }
}