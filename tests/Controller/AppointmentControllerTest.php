<?php

namespace App\Test\Controller;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppointmentControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private AppointmentRepository $repository;
    private string $path = '/appointment/';
    private EntityManagerInterface $manager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Appointment::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Appointment index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'appointment[dateCreationRdv]' => 'Testing',
            'appointment[debut]' => 'Testing',
            'appointment[fin]' => 'Testing',
            'appointment[status]' => 'Testing',
            'appointment[dateDeModifRdv]' => 'Testing',
            'appointment[userId]' => 'Testing',
            'appointment[serviceId]' => 'Testing',
            'appointment[payment]' => 'Testing',
        ]);

        self::assertResponseRedirects('/appointment/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Appointment();
        $fixture->setDateCreationRdv('My Title');
        $fixture->setDebut('My Title');
        $fixture->setFin('My Title');
        $fixture->setStatus('My Title');
        $fixture->setDateDeModifRdv('My Title');
        $fixture->setUserId('My Title');
        $fixture->setServiceId('My Title');
        $fixture->setPayment('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Appointment');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Appointment();
        $fixture->setDateCreationRdv('My Title');
        $fixture->setDebut('My Title');
        $fixture->setFin('My Title');
        $fixture->setStatus('My Title');
        $fixture->setDateDeModifRdv('My Title');
        $fixture->setUserId('My Title');
        $fixture->setServiceId('My Title');
        $fixture->setPayment('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'appointment[dateCreationRdv]' => 'Something New',
            'appointment[debut]' => 'Something New',
            'appointment[fin]' => 'Something New',
            'appointment[status]' => 'Something New',
            'appointment[dateDeModifRdv]' => 'Something New',
            'appointment[userId]' => 'Something New',
            'appointment[serviceId]' => 'Something New',
            'appointment[payment]' => 'Something New',
        ]);

        self::assertResponseRedirects('/appointment/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getDateCreationRdv());
        self::assertSame('Something New', $fixture[0]->getDebut());
        self::assertSame('Something New', $fixture[0]->getFin());
        self::assertSame('Something New', $fixture[0]->getStatus());
        self::assertSame('Something New', $fixture[0]->getDateDeModifRdv());
        self::assertSame('Something New', $fixture[0]->getUserId());
        self::assertSame('Something New', $fixture[0]->getServiceId());
        self::assertSame('Something New', $fixture[0]->getPayment());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Appointment();
        $fixture->setDateCreationRdv('My Title');
        $fixture->setDebut('My Title');
        $fixture->setFin('My Title');
        $fixture->setStatus('My Title');
        $fixture->setDateDeModifRdv('My Title');
        $fixture->setUserId('My Title');
        $fixture->setServiceId('My Title');
        $fixture->setPayment('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/appointment/');
    }
}
