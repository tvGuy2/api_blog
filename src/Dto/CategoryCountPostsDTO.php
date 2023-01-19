<?php

namespace App\Dto;

// Cette classe est un DTO (Data Transfert Object)

class CategoryCountPostsDTO
{
    private int $id ;
    private string $title ;
    private int $nbPosts;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getNbPosts(): int
    {
        return $this->nbPosts;
    }

    /**
     * @param int $nbPosts
     */
    public function setNbPosts(int $nbPosts): void
    {
        $this->nbPosts = $nbPosts;
    }

    // Pas de constructeurs avec des paramètres
    // Par défaut php va "créer" un constructeur par défaut



}