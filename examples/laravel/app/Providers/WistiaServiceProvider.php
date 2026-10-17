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
        $this->app->singleton(Configuration::class, static function (Application $app): Configuration {
            $token = $app->make('config')->get('wistia.api_token');
            if (!is_string($token) || trim($token) === '') {
                throw new \InvalidArgumentException('Configure wistia.api_token.');
            }
            return new Configuration(token: $token);
        });
        $this->app->singleton(Client::class, static fn(Application $app): Client =>
            new Client($app->make(Configuration::class)));
        $this->app->singleton(DataMediaService::class, static fn(Application $app): DataMediaService =>
            new DataMediaService($app->make(Client::class)));
        $this->app->singleton(WebhookVerifier::class, static function (Application $app): WebhookVerifier {
            $secret = $app->make('config')->get('wistia.webhook_secret');
            if (!is_string($secret) || trim($secret) === '') {
                throw new \InvalidArgumentException('Configure wistia.webhook_secret.');
            }
            return new WebhookVerifier($secret);
        });
        $this->app->singleton(WebhookParser::class, static fn(Application $app): WebhookParser =>
            new WebhookParser($app->make(WebhookVerifier::class)));
    }
}
