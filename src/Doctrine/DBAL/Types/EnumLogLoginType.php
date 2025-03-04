<?php
declare(strict_types = 1);
/**
 * /src/Doctrine/DBAL/Types/EnumLogLoginType.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */

namespace App\Doctrine\DBAL\Types;

/**
 * Class EnumLogLoginType
 *
 * @package App\Doctrine\DBAL\Types
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class EnumLogLoginType extends EnumType
{
    public const TYPE_FAILURE = 'failure';
    public const TYPE_SUCCESS = 'success';

    /**
     * @var string
     */
    protected static $name = 'EnumLogLogin';

    /**
     * @var string[]
     */
    protected static $values = [
        self::TYPE_FAILURE,
        self::TYPE_SUCCESS,
    ];
}
