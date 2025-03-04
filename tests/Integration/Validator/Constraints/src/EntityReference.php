<?php
declare(strict_types = 1);
/**
 * /tests/Integration/Validator/Constraints/src/EntityReference.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */

namespace App\Tests\Integration\Validator\Constraints\src;

use App\Entity\EntityInterface;
use Doctrine\ORM\Proxy\Proxy;

/**
 * Class EntityReference
 *
 * @package App\Tests\Integration\Validator\Constraints\src
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
abstract class EntityReference implements Proxy, EntityInterface
{
}
