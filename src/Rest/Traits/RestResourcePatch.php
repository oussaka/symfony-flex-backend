<?php
declare(strict_types = 1);
/**
 * /src/Rest/Traits/RestResourcePatch.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */

namespace App\Rest\Traits;

use App\DTO\RestDtoInterface;
use App\Entity\EntityInterface;

/**
 * Trait RestResourcePatch
 *
 * @SuppressWarnings("unused")
 *
 * @package App\Rest\Traits
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
trait RestResourcePatch
{
    /**
     * Before lifecycle method for patch method.
     *
     * @param string           $id
     * @param RestDtoInterface $dto
     * @param EntityInterface  $entity
     */
    public function beforePatch(string &$id, RestDtoInterface $dto, EntityInterface $entity): void
    {
    }

    /**
     * After lifecycle method for patch method.
     *
     * Notes:   If you make changes to entity in this lifecycle method by default it will be saved on end of current
     *          request. To prevent this you need to detach current entity from entity manager.
     *
     *          Also note that if you've made some changes to entity and you eg. throw an exception within this method
     *          your entity will be saved if it has eg Blameable / Timestampable traits attached.
     *
     * @param string           $id
     * @param RestDtoInterface $dto
     * @param EntityInterface  $entity
     */
    public function afterPatch(string &$id, RestDtoInterface $dto, EntityInterface $entity): void
    {
    }
}
