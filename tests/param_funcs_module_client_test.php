<?php

/**
 * Regression test for getClientIdByModuleId(): it receives a service id
 * (tblhosting.id) and must resolve the owner of that service, not the owner
 * of a service whose packageid happens to equal that id.
 *
 * Run: php tests/param_funcs_module_client_test.php
 */

namespace WHMCS\Database {
    class Capsule
    {
        public static array $rows = [];

        public static function table(string $name): CapsuleQuery
        {
            return new CapsuleQuery(self::$rows[$name] ?? []);
        }
    }

    class CapsuleQuery
    {
        private array $rows;

        public function __construct(array $rows)
        {
            $this->rows = $rows;
        }

        public function where(string $column, $value): CapsuleQuery
        {
            return new self(array_values(array_filter(
                $this->rows,
                fn (array $row): bool => ($row[$column] ?? null) === $value
            )));
        }

        public function first(string $column): ?object
        {
            if ($this->rows === []) {
                return null;
            }

            return (object) [$column => $this->rows[0][$column]];
        }
    }
}

namespace {
    require __DIR__ . '/../src/modules/addons/lknhooknotification/src/Core/Shared/param_funcs.php';

    // Service 38 belongs to client 1. Product (packageid) 38 is used by service 5,
    // which belongs to client 2. Looking up service 38 must return client 1.
    WHMCS\Database\Capsule::$rows['tblhosting'] = [
        ['id' => 5, 'packageid' => 38, 'userid' => 2],
        ['id' => 38, 'packageid' => 7, 'userid' => 1],
    ];

    $actual = getClientIdByModuleId(38);
    $expected = 1;

    if ($actual !== $expected) {
        fwrite(STDERR, "getClientIdByModuleId(38): expected client {$expected}, got {$actual}\n");
        exit(1);
    }

    // No service has id 999: the lookup must fail with a clear RuntimeException
    // that names the missing service, not a silent TypeError on null.
    try {
        getClientIdByModuleId(999);
        fwrite(STDERR, "getClientIdByModuleId(999): expected RuntimeException, got a return value\n");
        exit(1);
    } catch (\RuntimeException $e) {
        if (strpos($e->getMessage(), '999') === false) {
            fwrite(STDERR, "getClientIdByModuleId(999): message does not name service 999: {$e->getMessage()}\n");
            exit(1);
        }
    }

    echo "OK: getClientIdByModuleId resolves the service owner and rejects unknown services\n";
}
