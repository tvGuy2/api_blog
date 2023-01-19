<?php

namespace App\Controller;

use App\Dto\CategoryCountPostsDTO;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Symfony\Component\Serializer\SerializerInterface ;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    private SerializerInterface $serializer;
    private CategoryRepository $categoryRepository;
    private PostRepository $postRepository;

    /**
     * @param SerializerInterface $serializer
     * @param CategoryRepository $categoryRepository
     * @param PostRepository $postRepository
     */
    public function __construct(SerializerInterface $serializer, CategoryRepository $categoryRepository, PostRepository $postRepository)
    {
        $this->serializer = $serializer;
        $this->categoryRepository = $categoryRepository;
        $this->postRepository = $postRepository;
    }


    #[Route('/api/categories', name: 'api_getCategories', methods: ['GET'])]
    public function getCategories(): Response
    {
        // Rechercher les posts dans la base de donnée
        $categories = $this->categoryRepository->findAll();


        $postsJson = $this->serializer->serialize($categories,'json',['groups' => 'list_categories']);

        return new Response($postsJson,Response::HTTP_OK,['Content-type' => 'application/json']);

    }

    #[Route('/api/categories/{id}/posts', name: 'api_getPostsByCategories', methods: ['GET'])]
    public function getPostsCategorie($id): Response
    {
        // Rechercher les posts dans la base de donnée
        $categorie = $this->categoryRepository->find($id);

        if (!$categorie){
            return $this->generateError("La catégorie demandé n'existe pas", Response::HTTP_NO_CONTENT);
        }
        $post = $categorie->getPosts();


        $postsJson = $this->serializer->serialize($post,'json',['groups' => 'get_categorie']);
        return new Response($postsJson,Response::HTTP_OK,['Content-type' => 'application/json']);

    }
    private function generateError( string $message, int $status) : Response {
        $erreur = [
            'status' => $status,
            'message' => $message
        ];
        return new Response(json_encode($erreur),$status,["content-type" => "application/json"]);
    }

    #[Route('/api/categories/{id}', name: 'api_getCategorieid', methods: ['GET'])]
    public function getCategorieid($id): Response
    {
        // Rechercher les posts dans la base de donnée
        $categorie = $this->categoryRepository->find($id);


        if (!$categorie){
            return $this->generateError("La catégorie demandé n'existe pas", Response::HTTP_NO_CONTENT);
        }
        $categorieDTO = new CategoryCountPostsDTO();
        $categorieDTO->setId($categorie->getId());
        $categorieDTO->setTitle($categorie->getTitle());
        $categorieDTO->setNbPosts(count($categorie->getPosts()));


        $postsJson = $this->serializer->serialize($categorieDTO,'json');

        return new Response($postsJson,Response::HTTP_OK,['Content-type' => 'application/json']);

    }
}
