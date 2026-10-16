<?php
declare(strict_types=1);

namespace Automattic\Wistia\Account;

use Automattic\Wistia\Media\Input;

/** Experimental. Null omits a field, preserving Wistia's expiry and scope defaults. */
final readonly class ExpiringTokenOptions implements \JsonSerializable
{
    /** @param list<string>|null $scopes @param list<TokenAuthorization>|null $authorizations */
    public function __construct(
        #[\SensitiveParameter] public ?string $expires_at = null,
        #[\SensitiveParameter] public ?array $scopes = null,
        #[\SensitiveParameter] public ?array $authorizations = null,
    ) {
        if ($expires_at !== null) {
            if (preg_match('/\A\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})\z/D', $expires_at) !== 1) {
                throw new \InvalidArgumentException('Use an ISO 8601 timestamp with a timezone.');
            }
            try {
                new \DateTimeImmutable($expires_at);
                $errors = \DateTimeImmutable::getLastErrors();
            } catch (\Throwable) {
                throw new \InvalidArgumentException('Use a valid ISO 8601 timestamp.');
            }
            if ($errors !== false && ($errors['warning_count'] || $errors['error_count'])) {
                throw new \InvalidArgumentException('Use a valid ISO 8601 timestamp.');
            }
        }
        if ($scopes !== null) { Input::strings($scopes); }
        if ($authorizations !== null) {
            if (!array_is_list($authorizations)) { throw new \InvalidArgumentException('Expected a list of token authorizations.'); }
            foreach ($authorizations as $rule) {
                if (!$rule instanceof TokenAuthorization) { throw new \InvalidArgumentException('Expected typed token authorizations.'); }
            }
        }
    }
    public function jsonSerialize(): object
    {
        $fields = [];
        foreach (['expires_at', 'scopes', 'authorizations'] as $field) {
            if ($this->$field !== null) { $fields[$field] = $this->$field; }
        }
        return (object) $fields;
    }
    public function __debugInfo(): array { return []; }
}
