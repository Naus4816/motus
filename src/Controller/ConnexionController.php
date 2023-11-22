<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ConnexionRepository;
use App\Entity\Connexion;
use App\Form\ConnexionType;

class ConnexionController extends AbstractController
{
    #[Route('/connexion', name: 'app_connexion')]
    public function playGame(EntityManagerInterface $entityManager, ConnexionRepository $connexionRepository, Request $request): Response
    {
        $login=0;
        $mdp=0;
        $form = $this->createForm(ConnexionType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $login = $form->getData()['login'];
            $mdp = $form->getData()['mdp'];
        }

        return $this->render('motus/connexion.html.twig', [
            'form' => $form,
            'login' => $login,
            'mdp' => $mdp,
        ]); 
    }
}
?>