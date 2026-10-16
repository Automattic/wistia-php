<?php
declare(strict_types=1);

namespace Automattic\Wistia;

use Automattic\Wistia\Encoding\Path;
use Automattic\Wistia\Http\{ApiFamily, HttpMethod, Request, RequestSafety};
use Automattic\Wistia\Request\Data as R;
use Automattic\Wistia\Result\Data as D;

final readonly class DataContactsService
{
    public function __construct(#[\SensitiveParameter] private Client $client) {}

    public function inviteContacts(#[\SensitiveParameter] R\InviteContactsRequest $request): D\InviteContactsResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/contacts', RequestSafety::MayChangeRemoteState, [201], $request->query(), $request->body()),
            new Media\Decoder(D\InviteContactsResult::class))->result;
    }

    public function dismissDesktopInstallPrompt(#[\SensitiveParameter] R\DismissDesktopInstallPromptRequest $request): D\DismissDesktopInstallPromptResult
    {
        return $this->client->execute(new Request(ApiFamily::Data, HttpMethod::Post,
            '/contact/dismiss_desktop_install_prompt', RequestSafety::MayChangeRemoteState, [200], $request->query(), $request->body()),
            new Media\Decoder(D\DismissDesktopInstallPromptResult::class))->result;
    }
    public function __debugInfo(): array { return []; }
}
