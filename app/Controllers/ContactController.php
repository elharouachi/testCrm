<?php

namespace App\Controllers;

use App\Controllers\ControllerInterface;
use InvalidArgumentException;
use Exception;

class ContactController extends MainController implements ControllerInterface
{
    /** @var int $userId */
    protected $userId;

    /**
     * ContactController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->userId = $_SESSION['auth']['id'];

        $this->loadModel('Contact');
    }

    /**
     * Affichage de la liste des contacts de l'utilisateur connecté
     */
    public function index()
    {
        $contacts = [];
        if (!empty($this->userId)) {
            $contacts = $this->Contact->getContactByUser($this->userId);
        }
        echo $this->twig->render('index.html.twig', ['contacts' => $contacts]);
    }

    /**
     * Ajout d'un contact
     */
    public function add()
    {
        $error = false;
        if (!empty($_POST)) {
            $response = $this->sanitize($_POST);

            if ($response["response"]) {
                $result = $this->Contact->create([
                    'nom'    => $response['nom'],
                    'prenom' => $response['prenom'],
                    'email'  => $response['email'],
                    'userId' => $this->userId
                ]);
                if ($result) {
                    header('Location: index.php?p=contact.index');
                }
            } else {
                $error = true;
            }
        }
        echo $this->twig->render('add.html.twig', ['error' => $error]);
    }

    /**
     * Modification d'un contact
     */
    public function edit()
    {
        $error = false;
        $id = intval($_GET['id']);
        if (!empty($_POST)) {
            $response = $this->sanitize($_POST);

            if ($response["response"]) {
                $result = $this->Contact->update($id,
                    [
                        'nom'    => $response['nom'],
                        'prenom' => $response['prenom'],
                        'email'  => $response['email']
                    ]);
                if ($result) {
                    header("Location: index.php?p=contact.index");
                } else {
                    $error = true;
                    $this->twig->render('add.html.twig',
                        ["idContact" => $id,'error' => $error]);
                }
            } else {
                $error = true;
                $this->twig->render('add.html.twig', ['error' => $error]);
            }
        }

        $data = $this->Contact->findById($id);
        echo $this->twig->render('add.html.twig',
            [
                'data'      => $data
            ]);
    }

    /**
     * Suppression d'un contact
     */
    public function delete()
    {
        $result = $this->Contact->delete($_GET['id']);
        if ($result) {
            header('Location: /index.php?p=contact.index');
        }
    }

    /**
     * @param array $data
     * @return array
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function sanitize(array $data = []): array
    {
        $prenom = ucfirst(strtolower($data['prenom']));
        $nom = ucfirst(strtolower($data['nom']));
        $email  = strtolower($data['email']);

        if (empty($nom)) {
            throw new Exception('Le nom est obligatoire');
        }

        if (empty($prenom)) {
            throw new Exception('Le prenom est obligatoire');
        }

        if (empty($email)) {
            throw new Exception('Le email est obligatoire');
        }

        $isPalindrome = $this->apiClient('palindrome', ['name' => $nom]);
        $isEmail = $this->apiClient('email', ['email' => $email]);

        if ((!$isPalindrome->response) && $isEmail->response && $prenom) {
            return [
                'response' => true,
                'email'    => $email,
                'prenom'   => $prenom,
                'nom'      => $nom
            ];
        } elseif (!$isEmail->response) {
            throw new InvalidArgumentException('Le format de l\'email est invalide');
        } else {
            throw new InvalidArgumentException('Le nom est palindrome');
        }
    }

    /**
     * Création d'un contact
     */
    public function create()
    {
        //@todo
    }
}