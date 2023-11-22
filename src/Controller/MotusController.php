<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MotRepository;
use App\Entity\Mot;
use App\Form\MotType;

class MotusController extends AbstractController
{
    #[Route('/motus', name: 'app_motus')]
    public function playGame(EntityManagerInterface $entityManager, MotRepository $motRepository, Request $request): Response
    {
        $win = 0;
        $compteur = 0;
        $stop = 0;
        $session = $request->getSession();

        if ($session->has('stop')) {
            $stop = $session->get('stop');
            $compteur = $session->get('compteur');
            $compteur++;
        }

        while ($stop != 1) {
            $mot = 0;
            $check = 1;
            $tableau = [];
            //Pour chercher dans la base de données
            $motRepository = $entityManager->getRepository(Mot::class);
            //count compte le nombre de mot dans la bdd
            $count = $motRepository->compteur();
            $validation = TRUE;
            $verif = -1;
            //check si jour a déjà été chargé
            if ($session->has('jour')) {
                $jour = $session->get('jour');
                $jourlength = $session->get('jourlength');
            } 
            //charge le mot du jour depuis la bdd
            else {
                while ($validation == TRUE) {
                    $id = random_int(1, $count);
                    $mot = $entityManager->getRepository(Mot::class)->find($id);
            //la variable validation  vérifie si le mot est déjà passé dans les mots du jour,
            //il est faux si il n'est pas passé et vrai s'il est passé
                    $validation = $mot->isValidation();
            //verif compte le nombre de fois qu'un mot a une validation Vraie
                    $verif++;
            //on met toutes les validations à faux si elles sont toutes vraies
                    if ($count == $verif) {
                        foreach ($motRepository->findAll() as $mot) {
                            $mot->setValidation(FALSE);
                            $entityManager->persist($mot);
                            $entityManager->flush();
                        }
                    }
                }
            //on prend le mot du jour on le met en majuscule puis dans un tableau
                $mot->setValidation(TRUE);
                $entityManager->persist($mot);
                $entityManager->flush();
                $jour = $mot->getNom();
                $jour = strtoupper($jour);
                $jourlength = strlen($jour);
                $jour = str_split($jour);
                $verif = -1;
            }
            //on appelle le formulaire dans le formulaire il y a le mot que l'user rentre
            $form = $this->createForm(MotType::class);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) 
            {
                //on récupère le mot rentré par le user on le met en majuscule et on le met dans un tableau
                $mot = $form->getData()['mot'];
                $mot = strtoupper($mot);
                $mot = str_split($mot);
                //on vérifie si il y a déjà un tableau de créer pour reprendre les données des rows d'avant
                if ($session->has('tableau')) {
                    $tableau = $session->get('tableau');
                }

                for ($i = 0; $i < $jourlength; $i++) {
                    // "R" : lettre validée non superflue bien placée
                    // "J" : lettre validée non superflue et mal placée
                    // "B" : lettre fausse ou lettre en trop

                    // La lettre est validée et au bon endroit
                    if ($jour[$i] == $mot[$i]) {
                        $tableau[$compteur][$i] = "R";
                    } else {
                        for ($j = 0; $j < $jourlength; $j++) {
                            // Vérifie que la lettre est superflue ou pas
                            if ($jour[$j] == $mot[$i]) {
                                $count = array_count_values($mot);
                                $count2 = array_count_values($jour);

                                for ($n = 0; $n < $i; $n++) {
                                    if ($mot[$i] == $mot[$n]) {
                                        $check++;
                                    }
                                }

                                if ($check <= $count2[$jour[$j]]) {
                                   // Vérifie si la lettre mal placée ne se trouve pas correctement plus loin et est donc superflue.
                                    // Si tel est le cas, marque la lettre actuelle comme "B".

                                    for ($n = 0; $n < $jourlength; $n++) {
                                        // La lettre est correctement placée plus loin, indiquant que la lettre actuelle est superflue.
                                        if (($jour[$n] == $mot[$i]) && ($jour[$n] == $mot[$n])) {
                                            $tableau[$compteur][$i] = "B";
                                            $check = -1;
                                        }
                                    }

                                   // La lettre n'est pas correctement placée plus loin, indiquant qu'elle n'est pas superflue.
                                    if ($check != -1) {
                                        $tableau[$compteur][$i] = "J";
                                    }
                                } else {
                                    // La lettre est en trop
                                    $tableau[$compteur][$i] = "B";
                                }

                                // Sortie boucke
                                $check = 1;
                                $j = $jourlength;
                            } else {
                                // La lettre est fausse
                                $tableau[$compteur][$i] = "B";
                            }
                        }
                    }
                }
                //on vérifie les conditions de victoire et de défaite
                for ($i = 0; $i < $jourlength; $i++) {
                    if ($tableau[$compteur][$i] == "R") {
                        $check = -2;
                    }
                }

                if ($check == -2) {
                    if (array_count_values($tableau[$compteur])["R"] == $jourlength) {
                        $win = 1;
                    }
                }
            }
            //on vérifie les conditions de stopage de la partie
            if ($win == 1 || $compteur >= 8) {
                $stop = 1;
            }
            //on met les variables de session
            $session->set('jour', $jour);
            $session->set('jourlength', $jourlength);
            $session->set('compteur', $compteur);
            $session->set('tableau', $tableau);
            $session->set('stop', $stop);
            //on envoie les variables dans le index.html.twig
            return $this->render('motus/index.html.twig', [
                'jour' => $jour,
                'jourlength' => $jourlength,
                'mot' => $mot,
                'form' => $form,
                'tableau' => $tableau,
            ]);
        }
        //vérifie que le compteur est supérieur à 8 si oui c'est une défaite si non c'est une victoire
        if ($compteur < 8) {
            $session->clear();
            $win = 0;

            return $this->render('motus/win.html.twig', [
                'win' => $win,
            ]);
        } else {
            $session->clear();
            $compteur = 0;
            $win = 0;

            return $this->render('motus/lose.html.twig', [
                'win' => $win,
            ]);    
        }
    }
}
?>
