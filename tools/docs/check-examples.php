<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
chdir($root);
// Resolve library symbols without Composer, credentials or application setup hooks.
spl_autoload_register(static function (string $class) use ($root): void {
    $prefix = 'Automattic\\Wistia\\';
    if (str_starts_with($class, $prefix)) {
        $path = $root . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($path)) {
            require_once $path;
        }
    }
});

function docsCallArguments(array $tokens, int $open, ReflectionFunctionAbstract $callable): void
{
    $names = array_map(static fn (ReflectionParameter $parameter): string => $parameter->getName(), $callable->getParameters());
    $depth = 0;
    for ($i = $open; $i < count($tokens); ++$i) {
        $token = $tokens[$i];
        if (in_array($token, ['(', '[', '{'], true)) {
            ++$depth;
        } elseif (in_array($token, [')', ']', '}'], true)) {
            if (--$depth === 0) {
                break;
            }
        } elseif ($depth === 1 && is_array($token) && $token[0] === T_STRING && ($tokens[$i + 1] ?? null) === ':') {
            if (!in_array($token[1], $names, true)) {
                throw new RuntimeException('Unknown named argument ' . $token[1] . ' for ' . $callable->getName());
            }
        }
    }
}

$count = 0;
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('docs/site', FilesystemIterator::SKIP_DOTS));
foreach ($files as $file) {
    if ($file->getExtension() !== 'md') {
        continue;
    }
    preg_match_all('~```php\n(.*?)\n```~s', file_get_contents($file->getPathname()), $blocks);
    $imports = [];
    $variables = ['client' => Automattic\Wistia\Client::class];
    foreach ($blocks[1] as $block) {
        ++$count;
        $code = str_starts_with($block, '<?php') ? $block : "<?php\n" . $block;
        try {
            $tokens = array_values(array_filter(token_get_all($code, TOKEN_PARSE),
                static fn (mixed $token): bool => !is_array($token) || !in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)));
            preg_match_all('~^use ([A-Za-z0-9_\\\\]+);~m', $block, $uses);
            foreach ($uses[1] as $class) {
                if (!class_exists($class) && !interface_exists($class) && !enum_exists($class)) {
                    throw new RuntimeException('Unknown import ' . $class);
                }
                $imports[substr($class, strrpos($class, '\\') + 1)] = $class;
            }
            preg_match_all('~\$(\w+)\s*=\s*new\s+(\w+)\s*\(~', $block, $assignments, PREG_SET_ORDER);
            foreach ($assignments as $assignment) {
                $variables[$assignment[1]] = $imports[$assignment[2]] ?? $assignment[2];
            }
            preg_match_all('~\$(\w+)->(\w+)\s*\(~', $block, $calls, PREG_SET_ORDER);
            foreach ($calls as $call) {
                if (isset($variables[$call[1]]) && !method_exists($variables[$call[1]], $call[2])) {
                    throw new RuntimeException('Unknown method ' . $variables[$call[1]] . '::' . $call[2]);
                }
            }
            foreach ($tokens as $i => $token) {
                if (!is_array($token) || $token[0] !== T_NEW || !isset($tokens[$i + 1][1])) {
                    continue;
                }
                $name = $tokens[$i + 1][1];
                $class = $imports[$name] ?? $name;
                if (!class_exists($class)) {
                    throw new RuntimeException('Unknown constructed class ' . $class);
                }
                $constructor = (new ReflectionClass($class))->getConstructor();
                if ($constructor !== null && ($tokens[$i + 2] ?? null) === '(') {
                    docsCallArguments($tokens, $i + 2, $constructor);
                }
            }
        } catch (Throwable $error) {
            throw new RuntimeException($file->getPathname() . ': ' . $error->getMessage());
        }
    }
}

// Exercise both complete consumer reads with only environment/autoloader/transport seams replaced.
foreach (['docs/site/tutorials/first-api-call.md', 'docs/site/how-to/migrate-from-1x.md'] as $consumerPage) {
    $tutorial = file_get_contents($consumerPage);
    preg_match('~```php\n(.*?)\n```~s', $tutorial, $match);
    $code = $match[1];
    $replacements = [
        "require 'vendor/autoload.php';" => '',
        "\$token = getenv('WISTIA_API_TOKEN');" => "\$token = 'docs-fixture-token';",
        '$client = new Client(new Configuration(token: $token));' => '$client = new Client(new Configuration(token: $token), $transport);',
    ];
    foreach ($replacements as $from => $to) {
        if (substr_count($code, $from) !== 1) {
            throw new RuntimeException('Tutorial seam changed; review its offline smoke check.');
        }
        $code = str_replace($from, $to, $code);
    }
    $transport = new class implements Automattic\Wistia\Http\Transport {
        public int $calls = 0;
        public function send(#[SensitiveParameter] Automattic\Wistia\Http\TransportRequest $request): Automattic\Wistia\Http\TransportResponse
        {
            if (++$this->calls !== 1 || $request->method !== Automattic\Wistia\Http\HttpMethod::Get->value) {
                throw new RuntimeException('Tutorial must make one read-only call.');
            }
            return new Automattic\Wistia\Http\TransportResponse(200, ['Content-Type' => ['application/json']],
                '[{"folder":null,"hashed_id":"fixture-media","name":"Fixture"}]');
        }
    };
    ob_start();
    try {
        eval(substr($code, strlen('<?php')));
        $output = ob_get_contents();
    } finally {
        ob_end_clean();
    }
    if ($transport->calls !== 1 || $output !== 'Media records on this page: 1' . PHP_EOL) {
        throw new RuntimeException($consumerPage . ': output does not match the documented outcome.');
    }
}
$directory = file_get_contents('docs/site/reference/services.md');
foreach (glob('src/*Service.php') as $service) {
    if (!str_contains($directory, '../../../' . $service)) {
        throw new RuntimeException('Service directory is missing ' . $service);
    }
}
echo 'Checked ' . $count . " PHP fragments: syntax, imports, known service methods and constructor names.\n";
echo "Tutorial and migration examples each executed with one synthetic read and expected output; all services indexed. No Wistia calls.\n";
