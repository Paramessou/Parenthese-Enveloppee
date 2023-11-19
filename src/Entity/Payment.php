<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'payment', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Appointment $appointmentId = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datePaiementExecute = null;

    #[ORM\Column(length: 255)]
    private ?string $statutPaiement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppointmentId(): ?Appointment
    {
        return $this->appointmentId;
    }

    public function setAppointmentId(Appointment $appointmentId): static
    {
        $this->appointmentId = $appointmentId;

        return $this;
    }

    public function getDatePaiementExecute(): ?\DateTimeInterface
    {
        return $this->datePaiementExecute;
    }

    public function setDatePaiementExecute(\DateTimeInterface $datePaiementExecute): static
    {
        $this->datePaiementExecute = $datePaiementExecute;

        return $this;
    }

    public function getStatutPaiement(): ?string
    {
        return $this->statutPaiement;
    }

    public function setStatutPaiement(string $statutPaiement): static
    {
        $this->statutPaiement = $statutPaiement;

        return $this;
    }
}
