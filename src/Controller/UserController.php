<?php


namespace App\Controller;

use App\Entity\Conference;
use App\Entity\User;
use App\Form\ConferenceType;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class UserController
 * @package App\Controller
 * @Route(path="/account")
 */
class UserController extends AbstractController
{
    /**
     * @Route(path="/update/{id}",name="update_user")
     * @param User $user
     * @return Response
     */
    public function update(Request $request, User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository(User::class)->find($user->getId());
        if (!$entity) {
            $this->createNotFoundException('Can not update user not found');
        }
        $form = $this->createForm(UserType::class, $entity);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $em->flush();
            return $this->redirectToRoute('profile', ['id' => $user->getId()]);
        }
        return $this->render('user/edit-profile.html.twig', ['form' => $form->createView()]);
    }
}
