<?php declare(strict_types=1);
namespace Tests\Security;

/**
 * Testovaná entita. Vlastnictví zatím nemá žádný objekt v aplikaci, takže si
 * testy nesou vlastní — mechanismus se tím ověří nezávisle na tom, která
 * doména ho jako první použije.
 */
final class OwnedThing
{
    public const string RESOURCE_ID = 'owned-thing';

    public function __construct(
        public readonly ?int $ownerId,
    ) {
    }
}
