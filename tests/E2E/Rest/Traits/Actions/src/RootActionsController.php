<?php
declare(strict_types = 1);
/**
 * /tests/E2E/Rest/Traits/Actions/src/RootActionsController.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */

namespace App\Tests\E2E\Rest\Traits\Actions\src;

use App\Annotation\RestApiDoc;
use App\DTO\User\UserCreate;
use App\DTO\User\UserPatch;
use App\DTO\User\UserUpdate;
use App\Rest\Controller;
use App\Rest\Traits\Actions\Root as Actions;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class RootActionsController
 *
 * @Route(
 *     path="/test_root_actions",
 *  )
 *
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 *
 * @RestApiDoc(disabled=true)
 *
 * @package App\Tests\E2E\Rest\Traits\Actions\src
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class RootActionsController extends Controller
{
    use Actions\CountAction;
    use Actions\CreateAction;
    use Actions\DeleteAction;
    use Actions\FindAction;
    use Actions\FindOneAction;
    use Actions\IdsAction;
    use Actions\UpdateAction;

    protected static $dtoClasses = [
        Controller::METHOD_CREATE => UserCreate::class,
        Controller::METHOD_UPDATE => UserUpdate::class,
        Controller::METHOD_PATCH => UserPatch::class,
    ];
}
