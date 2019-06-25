<?php


namespace App\Controller;


use App\Entity\Conference;
use App\Repository\ConferenceRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ConferenceController
 * @package App\Controller
 * @Route(path="/",name="conference")
 */
class ConferenceController extends AbstractController
{

    /**
     * @param int $id
     * @return Response
     * @Route(path="/show/{id}")
     */
public function find(int $id):Response{
    /**@var ConferenceRepository $repository*/
   $repository = $this->getDoctrine()->getRepository(Conference::class);
   return new Response("TesT");

}
}