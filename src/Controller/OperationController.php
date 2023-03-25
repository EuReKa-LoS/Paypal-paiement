<?php

namespace App\Controller;

use Omnipay\Omnipay;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OperationController extends AbstractController
{
    private $passerelle;
    private $manager;
    public function __construct(EntityManagerInterface $manager)
    {
        $this->passerelle = Omnipay::create('Paypal_Rest');
        $this->passerelle->setClientId($_ENV['PAYPAL_CLIENT_ID']);
        $this->passerelle->setSecret($_ENV['PAYPAL_SECRET_KEY']);
        $this->passerelle->setTestMode(true);
        $this->manager=$manager;
    }
    // Page home du système de paiement
    #[Route('/', name: 'app_cart')]
    public function cart(): Response
    {
        return $this->render('operation/cart.html.twig');
    }

    // Payment
    #[Route('/payment', name: 'app_payment')]
    public function payment(Request $request): Response
    {
        $token=$request->request->get('token');
        if(!$this->isCsrfTokenValid('myform',$token));
        {
            return new Response('Operation non autorisée', Response::HTTP_BAD_REQUEST,
            ['content-type' =>'text/plain']);
        }
        $response=$this->passerelle->purchase(array(
            'amount'=>$request->request->get('amount'),
            'currency'=>$_ENV['PAYPAL_CURRENCY'],
            'returnUrl'=>'https://127.0.0.1:8000/success',
            'returnUrl'=>'https://127.0.0.1:8000/error'

        ))->send();
        
        try {
            if($response->isRedirect())
            {
                $response->redirect();
            }
            else
            {
                return $response->getMessage();
            }
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
        return $this->render('operation/index.html.twig');
    }

    // Réussi
    #[Route('/success', name: 'app_success')]
    public function success(): Response
    {
        dd('success');
    }

    // Echec
    #[Route('/error', name: 'app_error')]
    public function error(): Response
    {
        dd('error');
    }
}
