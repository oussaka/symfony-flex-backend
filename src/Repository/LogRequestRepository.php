<?php
declare(strict_types = 1);
/**
 * /src/Repository/LogRequestRepository.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */

namespace App\Repository;

use App\Entity\LogRequest as Entity;
use DateInterval;
use DateTime;
use DateTimeZone;

/**
 * Class LogRequestRepository
 *
 * @package App\Repository
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 *
 * @codingStandardsIgnoreStart
 *
 * @method Entity|null                     find(string $id, ?int $lockMode = null, ?int $lockVersion = null): ?Entity
 * @method array<int|string, mixed>|Entity findAdvanced(string $id, $hydrationMode = null)
 * @method Entity|null                     findOneBy(array $criteria, ?array $orderBy = null): ?Entity
 * @method array<int, Entity>              findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
 * @method array<int, Entity>              findByAdvanced(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null, ?array $search = null): array
 * @method array<int, Entity>              findAll(): array
 *
 * @codingStandardsIgnoreEnd
 */
class LogRequestRepository extends BaseRepository
{
    /**
     * @var string
     */
    protected static $entityName = Entity::class;

    /**
     * Helper method to clean history data from request_log table.
     *
     * @return int
     *
     * @throws \Exception
     */
    public function cleanHistory(): int
    {
        // Determine date
        $date = new DateTime('now', new DateTimeZone('UTC'));
        $date->sub(new DateInterval('P3Y'));

        // Create query builder and define delete query
        $queryBuilder = $this
            ->createQueryBuilder('requestLog')
            ->delete()
            ->where('requestLog.date < :date')
            ->setParameter('date', $date->format('Y-m-d'));

        // Return deleted row count
        return (int)$queryBuilder->getQuery()->execute();
    }
}
