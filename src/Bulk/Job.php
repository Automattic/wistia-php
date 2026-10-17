<?php
declare(strict_types=1);

namespace Automattic\Wistia\Bulk;

use Automattic\Wistia\Media\Input;

final readonly class Job implements \JsonSerializable
{
    /** @param list<string>|null $ids Explicit IDs are limited to 1000; scoped resolution to 5000 is server-enforced. */
    public function __construct(#[\SensitiveParameter] public Change $change,
        #[\SensitiveParameter] public ?Scope $scope = null, #[\SensitiveParameter] public ?array $ids = null)
    {
        if ($change->operation === Operation::Create) { throw new \InvalidArgumentException('Jobs cannot create records.'); }
        if (($scope === null) === ($ids === null)) { throw new \InvalidArgumentException('Provide exactly one job scope or IDs.'); }
        if ($ids !== null) {
            if (count($ids) < 1 || count($ids) > 1000) { throw new \InvalidArgumentException('Jobs require 1 to 1000 explicit IDs.'); }
            Input::strings($ids);
            foreach ($ids as $id) { Input::nonempty($id); }
        }
        if ($scope !== null) {
            $allowed = match ($change->resourceType) {
                ResourceType::Captions => [ScopeType::Account, ScopeType::Media, ScopeType::Folder, ScopeType::Channel],
                ResourceType::ChannelEpisode => [ScopeType::Account, ScopeType::Channel, ScopeType::Media],
                ResourceType::Subfolder => [ScopeType::Account, ScopeType::Folder],
                ResourceType::Folder, ResourceType::Channel => [ScopeType::Account],
                default => [ScopeType::Account, ScopeType::Folder, ScopeType::Subfolder, ScopeType::Channel],
            };
            if (!in_array($scope->type, $allowed, true)) { throw new \InvalidArgumentException('Invalid parent for this resource.'); }
        }
    }
    public function jsonSerialize(): \stdClass
    {
        $out = $this->change->jsonSerialize();
        if ($this->scope !== null) { $out->scope = $this->scope; }
        if ($this->ids !== null) { $out->ids = $this->ids; }
        return $out;
    }
    public function __debugInfo(): array { return []; }
}
