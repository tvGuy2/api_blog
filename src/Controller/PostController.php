<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PostController extends AbstractController
{
    private PostRepository $postRepository;
    private SerializerInterface $serializer;
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private CategoryRepository $categoryRepository;

    /**
     * @param PostRepository $postRepository
     * @param SerializerInterface $serializer
     */
    public function __construct(PostRepository $postRepository, SerializerInterface $serializer , EntityManagerInterface $entityManager, ValidatorInterface $validator,CategoryRepository $categoryRepository)
    {
        $this->postRepository = $postRepository;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->categoryRepository = $categoryRepository;
    }


    #[Route('/api/posts', name: 'api_getPosts', methods: ['GET'])]
    public function getPosts(): Response
    {
        // Rechercher les posts dans la base de donnée
        $posts = $this->postRepository->findAll();

        // Normaliser le tableau $posts en un tableau associatif
        //$postsArray = $normalizer->normalize($posts);

        // Encoder en Json
        //$postsJson = json_encode($postsArray);

        // Sérialiser le tableau $Posts en Json
        $postsJson = $this->serializer->serialize($posts,'json',['groups' => 'list_posts']);

        // Générer la réponse HTTP
        /* $response = new Response();
        //$response->setStatusCode(200);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-type','application/json');
        $response->setContent($postsJson);
        */

        return new Response($postsJson,Response::HTTP_OK,['Content-type' => 'application/json']);

    }

    #[Route('/api/posts', name: 'api_createPosts', methods: ['POST'])]
    public function createPost(Request $request) : Response {
        // Récupérer dans la requête le body contenant le JSON du nouveau post
        $bodyRequest = $request->getContent();



        // Désérialiser le JSON en un objet de la classe post
        try {
            // Surveiller si le code ci-dessous lève (génère) une exception
            $post = $this->serializer->deserialize($bodyRequest,Post::class,"json");
            $categorie = $this->categoryRepository->find($bodyRequest)

        }
        catch (NotEncodableValueException $exeption){
            return $this->generateError($exeption->getMessage(),Response::HTTP_BAD_REQUEST);
        }

        // Validation des données ($post) en fonction des règles de validations définies
        $erreurs = $this->validator->validate($post);

        // Tester si il y a des erreurs
        if (count($erreurs) > 0){
            // Transformer le tableau en json
            $erreursJson = $this->serializer->serialize($erreurs,"json");
            return new Response($erreursJson,Response::HTTP_BAD_REQUEST,['Content-type' => 'application/json']);
        }

        // Insérer le nouveau post dans la base de données
        $post->setCategory();
        $post->setCreatedAt(new \DateTime());
        $this->entityManager->persist($post); //Créer le INSERT
        $this->entityManager->flush();

        // Générer la réponse HTTP
        // Sérialiser $post en json
        $postJson = $this->serializer->serialize($post,"json");
        return new Response($postJson,Response::HTTP_CREATED,['Content-type' => 'application/json']);

    }

    #[Route('/api/posts/{id}', name: 'api_getPost', methods: ['GET'])]
    public function getPost(int $id): Response
    {
        $post = $this->postRepository->find($id);
        // Générer une erreur si le post recherché n'existe pas

        if (!$post){
            return $this->generateError("Le post demandé n'existe pas", Response::HTTP_NO_CONTENT);
        }

        $postJson = $this->serializer->serialize($post,'json',['groups' => 'get_post']);
        return new Response($postJson,Response::HTTP_OK,['Content-type' => 'application/json']);
    }



    #[Route('/api/posts/{id}', name: 'api_deletePost', methods: ['DELETE'])]
    public function deletePost(int $id) : Response{
        $post = $this->postRepository->find($id);

        if (!$post){
            return $this->generateError("Le post à supprimer n'existe pas", Response::HTTP_NO_CONTENT);
        }

        $this->entityManager->remove($post);
        $this->entityManager->flush();
        return new Response(null,Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/posts/{id}', name: 'api_updatePost', methods: ['PUT'])]
    public function updatePost(int $id, Request $request) : Response {

        // Récupérer le body de la requête
        $bodyRequest = $request->getContent();

        // Récupérer dans la BDD le poste à modifier
        $post = $this->postRepository->find($id);

        if(!$post){
            return $this->generateError("Le post à supprimer n'existe pas", Response::HTTP_NO_CONTENT);
        }


        try {
            // Surveiller si le code ci-dessous lève (génère) une exception
            // Modifier le post avec les données du body (json)
            $this->serializer->deserialize($bodyRequest,Post::class,"json",
                ['object_to_populate' => $post]);
        }
        catch (NotEncodableValueException $exeption){
            return $this->generateError($exeption->getMessage(),Response::HTTP_BAD_REQUEST);
        }


        $this->entityManager->flush();
        return new Response(null,Response::HTTP_NO_CONTENT);
    }

    private function generateError( string $message, int $status) : Response {
        $erreur = [
            'status' => $status,
            'message' => $message
        ];
        return new Response(json_encode($erreur),$status,["content-type" => "application/json"]);
    }
}
