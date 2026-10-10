<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AdminRolesTest extends WebTestCase
{
    public function testAdminCanAccessBlog(): void
    {
        $this->assertAccess(
            'ROLE_ADMIN',
            '/admin/blog',
            200,
        );
    }

    public function testAdminCannotManageUsers(): void
    {
        $this->assertAccess(
            'ROLE_ADMIN',
            '/admin/users',
            403,
        );
    }

    public function testSuperAdminCanManageUsers(): void
    {
        $this->assertAccess(
            'ROLE_SUPER_ADMIN',
            '/admin/users',
            200,
        );
    }

    private function assertAccess(
        string $role,
        string $path,
        int $expectedStatus,
    ): void {
        $client = static::createClient([
            'environment' => 'test',
        ]);

        self::assertSame(
            'test',
            self::$kernel->getEnvironment(),
        );

        $em = static::getContainer()
            ->get(EntityManagerInterface::class);

        $connection = $em->getConnection();

        self::assertSame(
            'insite_dev_test',
            $connection->getParams()['dbname'] ?? null,
        );

        self::assertSame(
            'insite_dev_test',
            $connection
                ->executeQuery('SELECT current_database()')
                ->fetchOne(),
        );

        $email = sprintf(
            'test-%s@example.invalid',
            bin2hex(random_bytes(12)),
        );

        $user = new User();

        $user
            ->setEmail($email)
            ->setUsername('Utilisateur de test')
            ->setRole($role)
            ->setPassword(
                password_hash('test-only', PASSWORD_BCRYPT),
            )
            ->setCreatedAt(new \DateTimeImmutable());

        try {
            $em->persist($user);
            $em->flush();

            $client->loginUser($user);
            $client->request('GET', $path);

            self::assertResponseStatusCodeSame(
                $expectedStatus,
            );
        } finally {
            $testUser = $em
                ->getRepository(User::class)
                ->findOneBy(['email' => $email]);

            if ($testUser !== null) {
                $em->remove($testUser);
                $em->flush();
            }
        }
    }
}
