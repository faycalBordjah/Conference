<?php


namespace App\Controller;


use App\Entity\Conference;
use App\Entity\Rate;
use App\Entity\User;
use App\Form\RateType;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RateController extends AbstractController
{
    /**
     * @Route(path="/rate/{id}",name="rate_show")
     * @return Response
     */
    public function show(Rate $rate) :Response{

        return $this->render('rate/show.html.twig',[
            'rate'=>$rate
        ]);
    }


    /**
     * @param Conference $conference
     * @param User $user
     * @return Response
     * @throws \Exception
     * @Route(path="/account/vote/{conference}/{user}",name="vote")
     */
    public function create(Conference $conference, User $user)
    {
        $rate = new Rate();
        $form = $this->createForm(RateType::class,$rate);
        if ($form->isSubmitted() && $form->isValid()){
            $rate->setUserId($user->getId());
            $rate->setConference($conference);
            $rate->setCreationDate(new \DateTime());
            $em = $this->getDoctrine()->getManager(User::class);
            $em->persist($form->getData());
            $em->flush();
        }
        return new Response('test');//$this->render();
    }

}