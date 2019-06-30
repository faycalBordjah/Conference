<?php


namespace App\Controller;


use App\Entity\Rate;
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
}