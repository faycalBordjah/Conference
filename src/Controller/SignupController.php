<?php


namespace App\Controller;

use App\Entity\User;
use App\Form\SignupType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
            $user->setToken(bin2hex(random_bytes(60)));
            $cer = $user->setCertified(false);
            $em->persist($user);
            $em->flush();
            $this->sendConfirmationEmailMessage($user);
            if ($cer) {
                return $this->redirectToRoute('wait_view', []);
            }
            $isOk = true;
        }

        return $this->render('signup/registration.html.twig', [
            'userInscriptionForm' => $newUserForm->createView(),
            'isOk' => $isOk
        ]);
    }

    private function sendConfirmationEmailMessage(User $user)
    {
        $transport = (new \Swift_SmtpTransport('mailhog', 1025));
        $mailer = new \Swift_Mailer($transport);
        $url = $this->generateUrl('token', ['token' => $user->getToken()]);
        $renderTemplate = $this->render(
            'conference/mail-confirm-registration.html.twig',
            ['user' => $user,
                'token' => $user->getToken(),
            ]
        );
        $message = (new \Swift_Message('Confirmation Email'))
            ->setFrom('admin@local.com')
            ->setReplyTo('admin@local.com')
            ->setTo($user->getMail())
            ->setBody(
                $renderTemplate,
                "text/html"
            );
        $mailer->send($message);
    }

    /**
     * @Route("validation/{token}",name="token")
     * @param string $token
     */
    public function confirmByToken(Request $request, string $token)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var UserRepository $repository */
        $repository = $em->getRepository(User::class);
        /** @var User $user */
        $user = $repository->findOneBy(['token' => $token]);

        if (empty($user)) {
            throw $this->createNotFoundException('We couldn\'t find an account for that confirmation token');
        }
        $user->setCertified(true);
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('user_registration_confirmed');
    }

    /**
     * @Route(path="registred", name="user_registration_confirmed")
     */
    public function registrationConfirmed()
    {
        return $this->render('conference/registration-confirmed.html.twig');
    }

    /**
     * @Route(path="/wait", name="wait_view")
     */
    public function waitView()
    {
        return $this->render('conference/wait-confirmation.html.twig');
    }
}
