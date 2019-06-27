<?php


namespace App\Controller;

use App\Entity\User;
use App\Form\SignupType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SignupController extends AbstractController
{
    /**
     * @param Request $request
     * @return Response
     * @Route(path="/signup",name="signup_app")
     */
    public function signUp(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $isOk = false;
        /** @var User $user */
        $user = new User();
        $newUserForm = $this->createForm(SignupType::class, $user);
        $newUserForm->handleRequest($request);
        if ($newUserForm->isSubmitted() && $newUserForm->isValid()) {
            $user->setPassword(
                $encoder->encodePassword(
                    $user,
                    $newUserForm->get('plain')->getData()
                )
            );
            $em = $this->getDoctrine()->getManager();
            $user = $newUserForm->getData();
            $user->setRoles(['ROLE_USER']);
            $user->setCreationDate(new \DateTime());
            $em->persist($newUserForm->getData());
            $em->flush();
            $isOk = true;
        }
        return $this->render('signup/signup.html.twig', [
            'userInscriptionForm' => $newUserForm->createView(),
            'isOk' => $isOk
        ]);
    }
}
