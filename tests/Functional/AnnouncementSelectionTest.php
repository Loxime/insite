<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\TemporaryAnnouncement;
use App\Repository\TemporaryAnnouncementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class AnnouncementSelectionTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private TemporaryAnnouncementRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        if (self::getContainer()->getParameter('kernel.environment') !== 'test') {
            throw new \RuntimeException('Environnement de test obligatoire.');
        }

        $this->entityManager = self::getContainer()->get(
            EntityManagerInterface::class,
        );

        $database = (string) $this->entityManager
            ->getConnection()
            ->fetchOne('SELECT current_database()');

        if ($database !== 'insite_dev_test') {
            throw new \RuntimeException(
                'Base de test inattendue : ' . $database,
            );
        }

        $this->repository = self::getContainer()->get(
            TemporaryAnnouncementRepository::class,
        );

        $this->entityManager->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        if (isset($this->entityManager)) {
            $connection = $this->entityManager->getConnection();

            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            $this->entityManager->close();
        }

        parent::tearDown();
    }

    public function testBannerAndPopupAreIndependent(): void
    {
        $banner = $this->createAnnouncement(
            TemporaryAnnouncement::TYPE_BANNER,
        );

        $popup = $this->createAnnouncement(
            TemporaryAnnouncement::TYPE_POPUP,
        );

        $this->entityManager->flush();

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        self::assertSame(
            $banner->getId(),
            $this->repository->findActiveBanner($now)?->getId(),
        );

        self::assertSame(
            $popup->getId(),
            $this->repository->findActive($now)?->getId(),
        );
    }

    public function testLatestBannerHasPriority(): void
    {
        $this->createAnnouncement(TemporaryAnnouncement::TYPE_BANNER);

        $latest = $this->createAnnouncement(
            TemporaryAnnouncement::TYPE_BANNER,
        );

        $this->entityManager->flush();

        self::assertSame(
            $latest->getId(),
            $this->repository->findActiveBanner(
                new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
            )?->getId(),
        );
    }

    public function testInvalidOrUnscheduledBannersAreIgnored(): void
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $previous = $this->repository->findActiveBanner($now)?->getId();

        $disabled = $this->createAnnouncement(
            TemporaryAnnouncement::TYPE_BANNER,
        )->setEnabled(false);

        $future = $this->createAnnouncement(
            TemporaryAnnouncement::TYPE_BANNER,
        )->setStartsAt($now->modify('+1 day'));

        $expired = $this->createAnnouncement(
            TemporaryAnnouncement::TYPE_BANNER,
        )->setEndsAt($now->modify('-1 day'));

        $empty = $this->createAnnouncement(
            TemporaryAnnouncement::TYPE_BANNER,
        )->setBannerText(null);

        $this->entityManager->flush();

        $selected = $this->repository->findActiveBanner($now)?->getId();

        self::assertSame($previous, $selected);

        foreach ([$disabled, $future, $expired, $empty] as $announcement) {
            self::assertNotSame($announcement->getId(), $selected);
        }
    }

    public function testPopupWithoutImageIsIgnored(): void
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $previous = $this->repository->findActive($now)?->getId();

        $popup = $this->createAnnouncement(
            TemporaryAnnouncement::TYPE_POPUP,
        )->setImageKey(null);

        $this->entityManager->flush();

        self::assertSame(
            $previous,
            $this->repository->findActive($now)?->getId(),
        );

        self::assertNotSame(
            $popup->getId(),
            $this->repository->findActive($now)?->getId(),
        );
    }

    private function createAnnouncement(string $type): TemporaryAnnouncement
    {
        $announcement = (new TemporaryAnnouncement())
            ->setType($type)
            ->setEnabled(true)
            ->setButtonUrl('/about')
            ->setButtonLabel('Découvrir');

        if ($type === TemporaryAnnouncement::TYPE_BANNER) {
            $announcement->setBannerText('Annonce de test');
        } else {
            $announcement->setImageKey('announcements/test.png');
        }

        $this->entityManager->persist($announcement);

        return $announcement;
    }
}
