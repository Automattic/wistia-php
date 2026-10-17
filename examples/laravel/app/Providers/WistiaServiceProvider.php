<?php
declare(strict_types=1);

namespace App\Providers;

use Automattic\Wistia\{Client, Configuration, DataMediaService, WebhookParser, WebhookVerifier};
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class WistiaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind by class name so Laravel can satisfy command/controller type hints.
        // Singleton closures are lazy: registration does not contact Wistia, and
        // API services reuse the same configured client in this container.
        $this->app->singleton(Configuration::class, static function (Application $app): Configuration {
            // Read config(), not env(): after config:cache the consumer's .env
            // is not loaded. Secret loading belongs to config/wistia.php.
            $token = $app->make('config')->get('wistia.api_token');
            if (!is_string($token) || trim($token) === '') {
                throw new \InvalidArgumentException('Configure wistia.api_token.');
            }
            return new Configuration(token: $token);
        });
        // The client owns authentication, HTTP execution, decoding and typed
        // failures. Named services choose endpoints; they do not load credentials.
        $this->app->singleton(Client::class, static fn(Application $app): Client =>
            new Client($app->make(Configuration::class)));
        $this->app->singleton(DataMediaService::class, static fn(Application $app): DataMediaService =>
            new DataMediaService($app->make(Client::class)));
        // Inbound webhook authentication uses a different secret from outbound
        // API calls. Resolving these helpers does not require resolving the client.
        $this->app->singleton(WebhookVerifier::class, static function (Application $app): WebhookVerifier {
            $secret = $app->make('config')->get('wistia.webhook_secret');
            if (!is_string($secret) || trim($secret) === '') {
                throw new \InvalidArgumentException('Configure wistia.webhook_secret.');
            }
            return new WebhookVerifier($secret);
        });
        // The parser depends on the verifier so parsing can never skip the
        // signature check. Event handling/deduplication is not a container binding.
        $this->app->singleton(WebhookParser::class, static fn(Application $app): WebhookParser =>
            new WebhookParser($app->make(WebhookVerifier::class)));
    }
}
