<?php
/** Offline implementation-mapping checks; complements, not replaces, endpoint contract tests. */
declare(strict_types=1);

/** @return list<string> */
function deliveryErrors(array $inventory, string $root): array
{
    $errors = [];
    $rows = array_merge(array_filter($inventory['operations'] ?? [],
        static fn (array $row): bool => ($row['disposition'] ?? '') === 'included' && ($row['id'] ?? '') !== 'post_'),
        $inventory['upload_workflows'] ?? []);
    if (count($rows) !== 166) { $errors[] = 'expected 166 distinct catalog/workflow implementation mappings'; }
    $methods = [];
    foreach ($rows as $row) {
        $id = $row['id'] ?? '(missing id)';
        if (($row['delivery']['status'] ?? '') !== 'implemented-offline') {
            $errors[] = "$id: missing offline implementation marker";
        }
        $plan = $row['planned'] ?? [];
        $identity = strtolower($plan['method'] ?? '');
        if (isset($methods[$identity])) { $errors[] = "$id: duplicate implementation method"; }
        $methods[$identity] = true;
        [$service, $method] = array_pad(explode('::', $plan['method'] ?? ''), 2, '');
        $service = 'Automattic\\Wistia\\' . $service;
        if (!method_exists($service, $method)) {
            $errors[] = "$id: missing service method";
        } else {
            $reflection = new ReflectionMethod($service, $method);
            $parameters = $reflection->getParameters();
            if (!$reflection->isPublic() || $reflection->isStatic() || count($parameters) !== 1
                || (string) $parameters[0]->getType() !== ($plan['request_type'] ?? '')
                || (string) $reflection->getReturnType() !== ($plan['result_type'] ?? '')) {
                $errors[] = "$id: method signature does not match request/result mapping";
            }
        }
        foreach (['request_type', 'result_type'] as $key) {
            if (!class_exists($plan[$key] ?? '')) { $errors[] = "$id: missing $key"; }
        }
        if (class_exists($plan['result_type'] ?? '')
            && !is_subclass_of($plan['result_type'], Automattic\Wistia\Result\Result::class)) {
            $errors[] = "$id: result does not implement Result";
        }
        foreach (['documentation_target', 'contract_test_target'] as $key) {
            $target = explode('#', $plan[$key] ?? '')[0];
            $prefix = $key === 'documentation_target' ? 'docs/reference/' : 'tests/Contract/';
            if (!str_starts_with($target, $prefix) || str_contains($target, '..') || !is_file($root . '/' . $target)) {
                $errors[] = "$id: missing or unsafe $key";
                continue;
            }
            if ($key === 'contract_test_target') {
                $class = 'Automattic\\Wistia\\Tests\\' . str_replace('/', '\\', substr($target, 6, -4));
                if (!is_subclass_of($class, PHPUnit\Framework\TestCase::class)) {
                    $errors[] = "$id: contract target is not a PHPUnit test";
                } else {
                    $tests = array_filter((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC),
                        static fn (ReflectionMethod $m): bool => str_starts_with($m->getName(), 'test'));
                    if ($tests === []) { $errors[] = "$id: contract target has no test methods"; }
                }
            }
        }
    }
    // Root Upload is a catalog alias, not a seventh workflow or an extra service method.
    $rootUpload = array_values(array_filter($inventory['operations'] ?? [], static fn (array $r): bool => ($r['id'] ?? '') === 'post_'))[0] ?? [];
    $workflows = array_column($inventory['upload_workflows'] ?? [], null, 'id');
    if (($rootUpload['planned']['method'] ?? '') !== ($workflows['upload-file']['planned']['method'] ?? null)
        || ($rootUpload['planned']['additional_method'] ?? '') !== ($workflows['import-url']['planned']['method'] ?? null)) {
        $errors[] = 'root Upload alias does not match the two implemented workflows';
    }
    foreach ($inventory['contract_gaps'] ?? [] as $gap) {
        $resolution = $gap['implementation_resolution'] ?? [];
        $evidence = $resolution['evidence'] ?? '';
        if (($resolution['status'] ?? '') !== 'resolved' || empty($resolution['authority'])
            || $evidence !== 'docs/rewrite/upload-evidence.md' || !is_file($root . '/' . $evidence)) {
            $errors[] = ($gap['id'] ?? 'gap') . ': missing implementation-resolution evidence';
        }
        foreach ($gap['workflow_ids'] ?? [] as $id) {
            if (empty($workflows[$id]['implementation_contract'])) {
                $errors[] = "$id: missing approved implementation contract";
            }
        }
    }
    return $errors;
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    require dirname(__DIR__) . '/vendor/autoload.php';
    $root = dirname(__DIR__);
    $inventory = json_decode(file_get_contents($root . '/docs/rewrite/operations.json'), true, 512, JSON_THROW_ON_ERROR);
    $errors = deliveryErrors($inventory, $root);
    if ($errors !== []) {
        fwrite(STDERR, implode("\n", $errors) . "\n");
        exit(1);
    }
    echo "PASS implementation mappings: 160 non-Upload catalog operations and six Upload workflows.\n";
    echo "All mapped methods, request/result types, documentation files and executable test classes exist.\n";
    echo "Carried Upload implementation contracts have recorded authority/evidence; upstream documentation gaps remain separate.\n";
    echo "This is static mapping validation, not execution of every contract or proof of live API behavior.\n";
}
