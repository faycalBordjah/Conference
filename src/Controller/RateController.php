<?php


namespace App\Controller;

use App\Entity\Conference;
use App\Entity\Rate;
use App\Entity\User;
use App\Form\RateType;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RateController extends AbstractController
{
    /**
     * @Route(path="/rate/{id}",name="rate_show")
     * @return Response
     */
    public function show(Rate $rate) :Response
    {

        return $this->render('rate/show.html.twig', [
            'rate'=>$rate
        ]);
    }

    private function getNote(string $sNote)
    {
        $values          = [
            'one'        => 1,
            'two'        => 2,
            'three'      => 3,
            'four'       => 4,
            'five'       => 5,
        ];
        return $values[strtolower($sNote)];
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     * @Route(path="/account/vote",name="vote")
     */
    public function create(Request $request)
    {
        $note = $request->request->get('note');
        $conf_id = $request->request->get('conf_id');

        /** @var Conference $conference */
        $conference = $this->getDoctrine()->getRepository(Conference::class)->find($conf_id);
        $rate = new Rate();
        $rate->setConference($conference);
        $rate->setValue($this->getNote($note));
        $rate->setCreationDate(new \DateTime());
        /** @var User $user */
        $user = $this->getUser();
        $rate->setUserId($user->getId());
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $em->persist($rate);
        $em->flush();
        return $this->redirectToRoute('conference_index');
    }
}
