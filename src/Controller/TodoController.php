<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route; // Note l'utilisation de 'Attribute' ici

class TodoController extends AbstractController
{
    // ... le reste de ton code
    #[Route('/', name: 'app_todo')]
    public function index(TaskRepository $repository): Response
    {
        // On récupère toutes les tâches de la base de données
        $tasks = $repository->findAll();

        return $this->render('todo/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('/add', name: 'app_todo_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        // On récupère le texte envoyé par le formulaire
        $title = $request->request->get('title');

        if ($title) {
            $task = new Task();
            $task->setTitle($title);
            $task->setIsDone(false);

            // On dit à Symfony de sauvegarder
            $em->persist($task);
            $em->flush();
        }

        return $this->redirectToRoute('app_todo');
    }
    #[Route('/delete/{id}', name: 'app_todo_delete')]
    public function delete(Task $task, EntityManagerInterface $em): Response
    {
        // Symfony est magique : il voit {id} dans l'URL et va chercher 
        // automatiquement la tâche correspondante dans la base !

        $em->remove($task); // On prépare la suppression
        $em->flush();       // On exécute la requête SQL

        // On revient sur la page d'accueil
        return $this->redirectToRoute('app_todo');
    }
    #[Route('/toggle/{id}', name: 'app_todo_toggle')]
public function toggle(Task $task, EntityManagerInterface $em): Response
{
    // On inverse l'état actuel : si c'est true, ça devient false, et inversement
    $task->setIsDone(!$task->isDone());

    $em->flush(); // Pas besoin de persist() ici car l'objet existe déjà dans la BDD

    return $this->redirectToRoute('app_todo');
}
}
