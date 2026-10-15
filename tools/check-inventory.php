<?php
/** Offline P01 inventory checks only; never loads the client, Composer, or legacy tests. */
declare(strict_types=1);

function inventoryErrors(array $inventory): array
{
    $errors = [];
    $operations = $inventory['operations'] ?? [];
    $workflows = $inventory['upload_workflows'] ?? [];
    $schemas = $inventory['wire_schemas'] ?? [];
    $excludedIds = [
        'post_medias-mediahashedid-translate',
        'post_medias-mediahashedid-captions-purchase',
        'post_medias-mediahashedid-localizations',
        'post_media-extended-audio-descriptions-order',
        'post_bulk-purchase',
        'post_account-trials',
    ];
    $ids = [];
    $methods = [];
    $routes = [];
    $families = [];
    $counts = ['included' => 0, 'excluded' => 0];
    $responseSchemaSlots = [];
    $checkPlan = static function (array $row, string $id, bool $registerMethod = true) use (&$errors, &$methods): void {
        $plan = $row['planned'] ?? [];
        foreach (['method', 'request_type', 'result_type', 'documentation_target', 'contract_test_target'] as $field) {
            if (!is_string($plan[$field] ?? null) || $plan[$field] === '') {
                $errors[] = "$id: missing planned $field";
            }
        }
        $method = $plan['method'] ?? '';
        if (!preg_match('/^[A-Z][A-Za-z0-9]*Service::[a-z][A-Za-z0-9]*$/D', $method)) {
            $errors[] = "$id: invalid explicit method";
        }
        // PHP method and class names are case insensitive.
        $key = strtolower($method);
        if ($registerMethod) {
            if (isset($methods[$key])) {
                $errors[] = "$id: duplicate method ($method)";
            }
            $methods[$key] = true;
        }
        foreach (['request_type' => 'Request', 'result_type' => 'Result'] as $field => $suffix) {
            if (!str_starts_with($plan[$field] ?? '', 'Automattic\\Wistia\\') || !str_ends_with($plan[$field] ?? '', $suffix)) {
                $errors[] = "$id: invalid $field target";
            }
        }
        if (!str_starts_with($plan['documentation_target'] ?? '', 'docs/reference/') ||
            !str_starts_with($plan['contract_test_target'] ?? '', 'tests/Contract/')) {
            $errors[] = "$id: invalid documentation/test target";
        }
    };
    $checkContent = static function (array $content, string $id) use (&$errors, $schemas): void {
        foreach ($content as $mime => $value) {
            // A documented empty content object is allowed, e.g. Upload 401 text/html.
            if (isset($value['schema']) && !isset($schemas[$value['schema']])) {
                $errors[] = "$id: unknown wire schema for $mime";
            }
        }
    };
    foreach ($operations as $row) {
        $id = $row['id'] ?? '';
        if (!is_string($id) || $id === '' || isset($ids[$id])) {
            $errors[] = 'missing or duplicate operation id';
        }
        $ids[$id] = true;
        $source = $row['source'] ?? [];
        if (($source['url'] ?? '') !== "https://docs.wistia.com/reference/$id.md" ||
            !preg_match('/^[a-f0-9]{64}$/D', $source['sha256'] ?? '')) {
            $errors[] = "$id: invalid source provenance";
        }
        $family = $row['family'] ?? '';
        $families[$family] = ($families[$family] ?? 0) + 1;
        $http = $row['http'] ?? [];
        $upload = $family === 'Upload';
        if (($http['base_uri'] ?? '') !== ($upload ? 'https://upload.wistia.com' : 'https://api.wistia.com/modern') ||
            ($http['api_version'] ?? null) !== ($upload ? null : '2026-09') ||
            ($http['authentication'] ?? '') !== 'Bearer token') {
            $errors[] = "$id: incorrect host/version/authentication";
        }
        $route = ($http['method'] ?? '') . ' ' . ($http['base_uri'] ?? '') . ($http['path'] ?? '');
        if (isset($routes[$route])) {
            $errors[] = "$id: duplicate catalog route";
        }
        $routes[$route] = true;
        $disposition = $row['disposition'] ?? '';
        if (!array_key_exists($disposition, $counts)) {
            $errors[] = "$id: invalid disposition";
            continue;
        }
        $counts[$disposition]++;
        $forbiddenRoutes = [
            'POST /medias/{mediaHashedId}/translate',
            'POST /medias/{mediaHashedId}/captions/purchase',
            'POST /medias/{mediaHashedId}/localizations',
            'POST /media_extended_audio_descriptions/order',
            'POST /bulk/purchase',
            'POST /account/trials',
        ];
        $mustExclude = in_array($id, $excludedIds, true) ||
            in_array(($http['method'] ?? '') . ' ' . ($http['path'] ?? ''), $forbiddenRoutes, true);
        if ($mustExclude !== ($disposition === 'excluded')) {
            $errors[] = "$id: unsafe exclusion disposition";
        }
        if ($disposition === 'excluded') {
            if (empty($row['exclusion_reason']) || isset($row['planned'])) {
                $errors[] = "$id: exclusion needs a reason and no public method";
            }
            continue;
        }
        if (($row['charge_capable'] ?? null) !== false) {
            $errors[] = "$id: charge-capable inclusion";
        }
        if ($id === 'post_expiring-token' && ($row['experimental'] ?? null) !== true) {
            $errors[] = "$id: experimental operation must remain visible";
        }
        if (!is_bool($row['experimental'] ?? null) || !is_string($row['restrictions'] ?? null)) {
            $errors[] = "$id: missing experimental status/restrictions";
        }
        // post_ is an alias of two workflow definitions, not a third upload method.
        $checkPlan($row, $id, $id !== 'post_');
        preg_match_all('/\{([^}]+)\}/', $http['path'] ?? '', $matches);
        $pathParameters = [];
        foreach ($row['request']['parameters'] ?? [] as $parameter) {
            if (!isset($parameter['name'], $parameter['schema'], $parameter['php_property'])) {
                $errors[] = "$id: incomplete request parameter mapping";
            }
            if (($parameter['in'] ?? '') === 'path') {
                $pathParameters[] = $parameter['name'];
                if (($parameter['required'] ?? false) !== true) {
                    $errors[] = "$id: optional path parameter";
                }
            }
        }
        sort($pathParameters);
        sort($matches[1]);
        if ($pathParameters !== $matches[1]) {
            $errors[] = "$id: path/request parameter mismatch";
        }
        if ($id === 'get_medias-mediahashedid-captions-languagecode') {
            // Prose-derived selector, deliberately separate from generated parameters.
            $format = $row['request']['narrative_derived']['format'] ?? [];
            $expectedFormats = [
                'json' => ['', 'DataShowCaptionsResponse200ApplicationJson'],
                'srt' => ['.srt', 'DataShowCaptionsResponse200TextPlain'],
                'vtt' => ['.vtt', 'DataShowCaptionsResponse200TextVtt'],
                'txt' => ['.txt', 'DataShowCaptionsResponse200TextPlain'],
            ];
            if (($format['php_property'] ?? '') !== 'format' ||
                ($format['php_type'] ?? '') !== 'Automattic\\Wistia\\Request\\Data\\CaptionFormat' ||
                ($format['type_kind'] ?? '') !== 'string-backed enum' ||
                ($format['default'] ?? '') !== 'json' ||
                ($format['encoding'] ?? '') !== 'path-extension' ||
                ($format['source'] ?? '') !== $source['url'] ||
                empty($format['wire_strategy']) || empty($format['result_mapping']) ||
                empty($format['planned_contract_assertions']) ||
                array_keys($format['options'] ?? []) !== array_keys($expectedFormats)) {
                $errors[] = "$id: missing narrative-derived caption format mapping";
            }
            foreach ($expectedFormats as $name => [$suffix, $schema]) {
                $option = $format['options'][$name] ?? [];
                if (($option['path_suffix'] ?? null) !== $suffix ||
                    ($option['response_schema'] ?? '') !== $schema || !isset($schemas[$schema])) {
                    $errors[] = "$id: incorrect caption format extension/result mapping ($name)";
                }
            }
        }
        $checkContent($row['request']['content'] ?? [], $id);
        $success = false;
        foreach ($row['responses'] ?? [] as $status => $response) {
            $success = $success || str_starts_with((string) $status, '2');
            $checkContent($response['content'] ?? [], $id);
            foreach ($response['content'] ?? [] as $mime => $value) {
                if (isset($value['schema'])) {
                    $responseSchemaSlots[] = "$id $status $mime";
                }
            }
        }
        if (!$success) {
            $errors[] = "$id: missing success result mapping";
        }
    }
    // Presence-only pin of documented catalog response schema slots (2026-10-15).
    // This detects omitted mappings, not schema semantics/source truth. Schema-less
    // responses (e.g. Upload 401 text/html) and unresolved workflows stay schema-less.
    sort($responseSchemaSlots);
    if (hash('sha256', implode("\n", $responseSchemaSlots)) !== '8290c1f734a9239c4426cc606c99431acd01753274ec84e13ceae38b7d221688') {
        $errors[] = 'documented response schema mapping set differs from pinned source';
    }
    $sortedIds = array_keys($ids);
    sort($sortedIds);
    // Digest of sorted catalog slugs fetched 2026-10-15; catches same-count substitutions.
    if (hash('sha256', implode("\n", $sortedIds)) !== '3f38e29430486dd8372f7bf710686eca148bd8072e6600d932be80c5ae4d6a70') {
        $errors[] = 'catalog operation set differs from pinned source';
    }
    ksort($families);
    if ($families !== ['Analytics' => 17, 'Data' => 139, 'Stats' => 10, 'Upload' => 1] ||
        $counts !== ['included' => 161, 'excluded' => 6]) {
        $errors[] = 'catalog counts differ from pinned source';
    }
    $workflowIds = [];
    foreach ($workflows as $workflow) {
        $id = $workflow['id'] ?? '';
        if (isset($workflowIds[$id])) {
            $errors[] = "$id: duplicate workflow";
        }
        $workflowIds[$id] = $workflow;
        $checkPlan($workflow, $id);
        $http = $workflow['http'] ?? [];
        $host = $id === 'localization-import-url' ? 'https://upload-v2.wistia.com' : 'https://upload.wistia.com';
        $path = match ($id) {
            'thumbnail-file', 'thumbnail-url' => '/thumbnail',
            's3-copy' => '/s3_copy',
            default => '/',
        };
        if (($http['base_uri'] ?? '') !== $host || ($http['path'] ?? '') !== $path ||
            ($http['method'] ?? '') !== 'POST' || ($http['api_version'] ?? null) !== null ||
            ($http['authentication'] ?? '') !== 'Bearer token') {
            $errors[] = "$id: incorrect workflow host/path/authentication";
        }
        if (($workflow['charge_capable'] ?? null) !== false || ($workflow['disposition'] ?? '') !== 'included') {
            $errors[] = "$id: unsafe workflow inclusion";
        }
        if (array_intersect(['access_token', 'api_password', 'bearer_token'], array_keys($workflow['request']['fields'] ?? [])) !== []) {
            $errors[] = "$id: credential-bearing workflow input";
        }
        if (empty($workflow['request']['fields']) || empty($workflow['result_contract']) || empty($workflow['restrictions'])) {
            $errors[] = "$id: missing workflow contract or explicit gap";
        }
    }
    $expectedWorkflows = ['import-url', 'localization-import-url', 's3-copy', 'thumbnail-file', 'thumbnail-url', 'upload-file'];
    $actualWorkflows = array_keys($workflowIds);
    sort($actualWorkflows);
    if ($actualWorkflows !== $expectedWorkflows) {
        $errors[] = 'upload workflow set differs from documented coverage';
    }
    $rootUpload = array_values(array_filter($operations, static fn (array $row): bool => ($row['id'] ?? '') === 'post_'))[0] ?? [];
    if (($rootUpload['planned']['method'] ?? '') !== ($workflowIds['upload-file']['planned']['method'] ?? null) ||
        ($rootUpload['planned']['additional_method'] ?? '') !== ($workflowIds['import-url']['planned']['method'] ?? null)) {
        $errors[] = 'root Upload catalog-to-workflow alias mismatch';
    }
    $gaps = $inventory['contract_gaps'] ?? [];
    $gapIds = array_column($gaps, 'id');
    sort($gapIds);
    if ($gapIds !== ['upload-localization-contract', 'upload-s3-response', 'upload-thumbnail-response']) {
        $errors[] = 'unresolved source-contract gaps must remain visible until evidence resolves them';
    }
    foreach ($gaps as $gap) {
        if (empty($gap['unresolved']) || empty($gap['source']) || ($gap['affected_owner_issue'] ?? null) !== 24) {
            $errors[] = 'source-contract gap lacks evidence/owner';
        }
    }
    return $errors;
}

function selfTest(array $inventory): void
{
    $cases = [
        'missing mapping' => [static function (array &$d): void { unset($d['operations'][0]['planned']['method']); }, 'missing planned method'],
        'case-insensitive duplicate' => [static function (array &$d): void { $d['operations'][1]['planned']['method'] = strtoupper($d['operations'][0]['planned']['method']); }, 'duplicate method'],
        'unsafe inclusion' => [static function (array &$d): void { $d['operations'][0]['charge_capable'] = true; }, 'charge-capable inclusion'],
        'excluded purchase included' => [static function (array &$d): void { foreach ($d['operations'] as &$o) { if ($o['id'] === 'post_bulk-purchase') { $o['disposition'] = 'included'; $o['charge_capable'] = false; } } }, 'unsafe exclusion disposition'],
        'missing operation' => [static function (array &$d): void { array_pop($d['operations']); }, 'catalog operation set'],
        'duplicate operation' => [static function (array &$d): void { $d['operations'][] = $d['operations'][0]; }, 'duplicate operation id'],
        'same-count substitution' => [static function (array &$d): void { $d['operations'][0]['id'] = 'invented'; }, 'catalog operation set'],
        'wrong version' => [static function (array &$d): void { $d['operations'][0]['http']['api_version'] = 'v1'; }, 'incorrect host/version/authentication'],
        'missing path input' => [static function (array &$d): void { $d['operations'][1]['request']['parameters'] = []; }, 'path/request parameter mismatch'],
        'unknown schema' => [static function (array &$d): void { $d['operations'][0]['responses']['200']['content']['application/json']['schema'] = 'missing'; }, 'unknown wire schema'],
        'missing documented schema' => [static function (array &$d): void { unset($d['operations'][0]['responses']['200']['content']['application/json']['schema']); }, 'documented response schema mapping set'],
        'missing caption format selector' => [static function (array &$d): void { foreach ($d['operations'] as &$o) { if ($o['id'] === 'get_medias-mediahashedid-captions-languagecode') { unset($o['request']['narrative_derived']); } } }, 'missing narrative-derived caption format mapping'],
        'wrong caption format extension' => [static function (array &$d): void { foreach ($d['operations'] as &$o) { if ($o['id'] === 'get_medias-mediahashedid-captions-languagecode') { $o['request']['narrative_derived']['format']['options']['txt']['path_suffix'] = '.json'; } } }, 'incorrect caption format extension/result mapping (txt)'],
        'wrong caption format result' => [static function (array &$d): void { foreach ($d['operations'] as &$o) { if ($o['id'] === 'get_medias-mediahashedid-captions-languagecode') { $o['request']['narrative_derived']['format']['options']['vtt']['response_schema'] = 'DataShowCaptionsResponse200ApplicationJson'; } } }, 'incorrect caption format extension/result mapping (vtt)'],
        'missing result' => [static function (array &$d): void { $d['operations'][0]['responses'] = []; }, 'missing success result mapping'],
        'missing upload workflow' => [static function (array &$d): void { array_pop($d['upload_workflows']); }, 'upload workflow set'],
        'hidden source gap' => [static function (array &$d): void { $d['contract_gaps'] = []; }, 'gaps must remain visible'],
        'excluded route alias' => [static function (array &$d): void { $d['operations'][0]['http']['method'] = 'POST'; $d['operations'][0]['http']['path'] = '/bulk/purchase'; }, 'unsafe exclusion disposition'],
        'hidden experimental status' => [static function (array &$d): void { foreach ($d['operations'] as &$o) { if ($o['id'] === 'post_expiring-token') { $o['experimental'] = false; } } }, 'experimental operation must remain visible'],
        'legacy Upload credential field' => [static function (array &$d): void { $d['upload_workflows'][0]['request']['fields']['api_password'] = ['type' => 'string']; }, 'credential-bearing workflow input'],
        'unsafe workflow host' => [static function (array &$d): void { $d['upload_workflows'][0]['http']['base_uri'] = 'https://example.invalid'; }, 'incorrect workflow host/path/authentication'],
        'unmapped Upload catalog entry' => [static function (array &$d): void { foreach ($d['operations'] as &$o) { if ($o['id'] === 'post_') { unset($o['planned']['request_type']); } } }, 'missing planned request_type'],
    ];
    foreach ($cases as $name => [$mutate, $expected]) {
        $copy = $inventory;
        $mutate($copy);
        if (!str_contains(implode("\n", inventoryErrors($copy)), $expected)) {
            throw new RuntimeException("Self-test failed: $name did not detect $expected");
        }
        echo "PASS rejection: $name\n";
    }
    echo count($cases) . " malformed-inventory cases rejected; these are checker tests, not endpoint contract tests.\n";
}

try {
    $args = array_slice($argv, 1);
    $pathArgs = array_values(array_filter($args, static fn (string $arg): bool => !str_starts_with($arg, '--')));
    foreach ($args as $arg) {
        if (str_starts_with($arg, '--') && !in_array($arg, ['--self-test', '--require-complete'], true)) {
            throw new InvalidArgumentException("Unknown option: $arg");
        }
    }
    if (count($pathArgs) > 1) {
        throw new InvalidArgumentException('Usage: php tools/check-inventory.php [inventory.json] [--self-test] [--require-complete]');
    }
    $path = $pathArgs[0] ?? __DIR__ . '/../docs/rewrite/operations.json';
    // A local file only: even an explicitly supplied URL cannot cause a network request.
    if (str_contains($path, '://') || !is_file($path) || !is_readable($path)) {
        throw new InvalidArgumentException('Inventory must be a readable local file');
    }
    $inventory = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($inventory)) {
        throw new InvalidArgumentException('Inventory must be a JSON object');
    }
    $errors = inventoryErrors($inventory);
    if ($errors !== []) {
        throw new RuntimeException(implode("\n", $errors));
    }
    echo "PASS accounting: 167 catalog operations; 161 included, 6 excluded; 6 upload workflows.\n";
    echo "PASS mapping consistency: unique methods, sources, path inputs, schema references, exclusions and version/auth targets.\n";
    foreach ($inventory['contract_gaps'] as $gap) {
        echo "UNRESOLVED {$gap['id']} (#{$gap['affected_owner_issue']}): {$gap['unresolved']}\n";
    }
    echo "Contract completeness: PARTIAL. No endpoint implementation, endpoint tests, live verification, or acceptance is asserted.\n";
    if (in_array('--self-test', $args, true)) {
        selfTest($inventory);
    }
    if (in_array('--require-complete', $args, true) && $inventory['contract_gaps'] !== []) {
        exit(1);
    }
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . "\n");
    exit(1);
}
