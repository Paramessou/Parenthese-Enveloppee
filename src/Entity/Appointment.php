<?php

namespace App\Entity;

use App\Entity\Payment;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AppointmentRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreationRdv = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\GreaterThanOrEqual("today", message: "La date de début ne peut pas être antérieure à la date du jour.")]
    private ?\DateTimeInterface $debut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fin = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateDeModifRdv = null;

    #[ORM\ManyToOne(inversedBy: 'appointments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userId = null;

    #[ORM\ManyToOne(inversedBy: 'appointments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Service $serviceId = null;

    #[ORM\OneToOne(mappedBy: 'appointmentId', cascade: ['persist', 'remove'])]
    private ?Payment $payment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCreationRdv(): ?\DateTimeInterface
    {
        return $this->dateCreationRdv;
    }

    public function setDateCreationRdv(\DateTimeInterface $dateCreationRdv): static
    {
        $this->dateCreationRdv = $dateCreationRdv;

        return $this;
    }

    public function getDebut(): ?\DateTimeInterface
    {
        return $this->debut;
    }

    public function setDebut(\DateTimeInterface $debut): static
    {
        $this->debut = $debut;

        return $this;
    }

    public function getFin(): ?\DateTimeInterface
    {
        return $this->fin;
    }

    public function setFin(\DateTimeInterface $fin): static
    {
        $this->fin = $fin;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }


    public function getDateDeModifRdv(): ?\DateTimeInterface
    {
        return $this->dateDeModifRdv;
    }

    public function setDateDeModifRdv(?\DateTimeInterface $dateDeModifRdv): static
    {
        $this->dateDeModifRdv = $dateDeModifRdv;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(?User $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getServiceId(): ?Service
    {
        return $this->serviceId;
    }

    public function setServiceId(?Service $serviceId): static
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(Payment $payment): static
    {
        // définit la propriété de relation côté propriétaire si nécessaire
        if ($payment->getAppointmentId() !== $this) {
            $payment->setAppointmentId($this);
        }

        $this->payment = $payment;

        return $this;
    }

    public function chevaucheHeure($entityManager)
    { // Vérifie si le rendez-vous est en conflit avec un autre rendez-vous
        // print_r('Début de la méthode chevaucheHeure<br>');

        $appointments = $entityManager->getRepository(Appointment::class)->findAll();
        // print_r('Rendez-vous récupérés<br>');

        foreach ($appointments as $existingAppointment) {
            // print_r('Rendez-vous actuel : ' . $existingAppointment->getId() . '<br>');

            if ($this->debut->getTimestamp() < $existingAppointment->getFin()->getTimestamp() && $this->fin->getTimestamp() > $existingAppointment->getDebut()->getTimestamp()) {
                // print_r('Un chevauchement a été détecté<br>');
                return true;
            }
        }

        // print_r('Aucun chevauchement détecté<br>');
        return false;
    }
}
