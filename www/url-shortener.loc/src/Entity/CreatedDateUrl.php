<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */

class CreatedDateUrl
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;


    /**
     * @ORM\Column(name="created_date")
     */
    private $created_date;

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function setCreatedDate( $createdDate): self
    {
        $this->created_date = $createdDate;
        return $this;
    }
}