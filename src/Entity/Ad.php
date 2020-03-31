<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Symfony\Component\Validator\Constraints as Assert; 

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdRepository")
  * @ORM\HasLifecycleCallbacks 
 */
class Ad
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min = 10,
     *      max = 50,
     *      minMessage = "Your first name must be at least {{ limit }} characters long",
     *      maxMessage = "Your first name cannot be longer than {{ limit }} characters"
     * )
     */
     
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="text")
     */
    private $introduction;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
    * @ORM\Column(type="string", length=255)
    * @Assert\Url
    * @Assert\Regex(pattern="#\.(jpg|gif|png)$#",
    *    match=true, 
    *   message="Url doit se terminer par .jpg ou .png ou .gif" 
    *)
    */
    private $coverImage;

    /**
     * @ORM\Column(type="integer")
     */
    private $rooms;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Image", mappedBy="ad", orphanRemoval=true)
     * @Assert\Valid
     */
    private $images;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ImageUpload", mappedBy="ad", orphanRemoval=true)
     */
    private $imageUploads;


// variable associée au fichier image
  /** 
     * @Assert\All({ 
       * @Assert\File( 
     *     maxSize = "1024k", 
     * maxSizeMessage ="taille max 1Méga",
     *     mimeTypes = {"image/jpeg","image/png"}, 
     *     mimeTypesMessage = "Entrer un jpg ou jpeg ou png" 
     * ) 
     * }) 
     */    
public $file;

// tableau des id des images à effacer
public $tableau_id;

/**
 * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="ads")
 * @ORM\JoinColumn(nullable=false)
 */
private $author;

/**
 * @ORM\OneToMany(targetEntity="App\Entity\Booking", mappedBy="ad")
 */
private $bookings;

/**
 * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="ad", orphanRemoval=true)
 */
private $comments;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->imageUploads = new ArrayCollection();
        $this->bookings = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }


// calcul de la moyenne des notes d'une annonce
    public function getAvgRatings(){

        //dump($this->comments);

        $somme=0;
        foreach ($this->comments as $comment) {
           //dump($comment->getRating());
            $somme=$somme + $comment-> getRating();

        }

        if (count($this->comments) > 0) return $somme/count($this->comments);
        return 0;


    }


   //recupère le commentaire d'un auteur 
    public function getCommentFromAuthor(User $author){

        foreach ($this->comments as $comment) {
           //dump($comment); 

         if ($comment->getAuthor()===$author) return $comment;
         return null;   


        }
        



    }


    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    public function setIntroduction(string $introduction): self
    {
        $this->introduction = $introduction;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(string $coverImage): self
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    public function getRooms(): ?int
    {
        return $this->rooms;
    }

    public function setRooms(int $rooms): self
    {
        $this->rooms = $rooms;

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setAd($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getAd() === $this) {
                $image->setAd(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ImageUpload[]
     */
    public function getImageUploads(): Collection
    {
        return $this->imageUploads;
    }

    public function addImageUpload(ImageUpload $imageUpload): self
    {
        if (!$this->imageUploads->contains($imageUpload)) {
            $this->imageUploads[] = $imageUpload;
            $imageUpload->setAd($this);
        }

        return $this;
    }

    public function removeImageUpload(ImageUpload $imageUpload): self
    {
        if ($this->imageUploads->contains($imageUpload)) {
            $this->imageUploads->removeElement($imageUpload);
            // set the owning side to null (unless already changed)
            if ($imageUpload->getAd() === $this) {
                $imageUpload->setAd(null);
            }
        }

        return $this;
    }


/** 
 * @ORM\PreRemove
*/ 
public function deleteUploadFiles(){ 
  

    foreach ($this->getImageUploads() as $imageUploadee) {

         unlink($_SERVER['DOCUMENT_ROOT'].$imageUploadee->getUrl());
    }

  
}

public function getAuthor(): ?User
{
    return $this->author;
}

public function setAuthor(?User $author): self
{
    $this->author = $author;

    return $this;
}

/**
 * @return Collection|Booking[]
 */
public function getBookings(): Collection
{
    return $this->bookings;
}

public function addBooking(Booking $booking): self
{
    if (!$this->bookings->contains($booking)) {
        $this->bookings[] = $booking;
        $booking->setAd($this);
    }

    return $this;
}

public function removeBooking(Booking $booking): self
{
    if ($this->bookings->contains($booking)) {
        $this->bookings->removeElement($booking);
        // set the owning side to null (unless already changed)
        if ($booking->getAd() === $this) {
            $booking->setAd(null);
        }
    }

    return $this;
}

/**
 * @return Collection|Comment[]
 */
public function getComments(): Collection
{
    return $this->comments;
}

public function addComment(Comment $comment): self
{
    if (!$this->comments->contains($comment)) {
        $this->comments[] = $comment;
        $comment->setAd($this);
    }

    return $this;
}

public function removeComment(Comment $comment): self
{
    if ($this->comments->contains($comment)) {
        $this->comments->removeElement($comment);
        // set the owning side to null (unless already changed)
        if ($comment->getAd() === $this) {
            $comment->setAd(null);
        }
    }

    return $this;
} 


}
