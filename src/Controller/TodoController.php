<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;

class TodoController extends AbstractController
{
    private $session;

    public function __construct(RequestStack $requestStack)
    {
        // Dès que le contrôleur est appelé, on prépare l'accès à la session
        $this->session = $requestStack->getSession();
    }

    #[Route('/', name: 'app_todo')]
    public function index(): Response
    {
        // On récupère le tableau 'tasks' dans la session. S'il n'existe pas, on renvoie un tableau vide []
        $tasks = $this->session->get('tasks', []);

        return $this->render('todo/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('/add', name: 'app_todo_add', methods: ['POST'])]
    public function add(Request $request): Response
    {
        $title = $request->request->get('title');
        $tasks = $this->session->get('tasks', []);

        if ($title) {
            // Au lieu d'utiliser l'entité Task, on crée un simple tableau associatif
            $newTask = [
                'id' => uniqid(), // On génère un identifiant textuel unique
                'title' => $title,
                'isDone' => false
            ];
            
            $tasks[] = $newTask; // On ajoute la nouvelle tâche au tableau
            $this->session->set('tasks', $tasks); // On sauvegarde le tableau mis à jour en session
        }

        return $this->redirectToRoute('app_todo');
    }

    #[Route('/delete/{id}', name: 'app_todo_delete')]
    public function delete(string $id): Response
    {
        $tasks = $this->session->get('tasks', []);

        // On filtre le tableau pour conserver toutes les tâches SAUF celle qui a cet ID
        $tasks = array_filter($tasks, function($task) use ($id) {
            return $task['id'] !== $id;
        });

        $this->session->set('tasks', $tasks);

        return $this->redirectToRoute('app_todo');
    }

    #[Route('/toggle/{id}', name: 'app_todo_toggle')]
    public function toggle(string $id): Response
    {
        $tasks = $this->session->get('tasks', []);

        // On parcourt le tableau pour trouver la bonne tâche et inverser son statut
        foreach ($tasks as &$task) {
            if ($task['id'] === $id) {
                $task['isDone'] = !$task['isDone'];
                break;
            }
        }

        $this->session->set('tasks', $tasks);

        return $this->redirectToRoute('app_todo');
    }
}