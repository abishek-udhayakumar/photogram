<?php
// includes/Env.php

class Env
{
    protected static $env_file = '.env';
    protected static $data = [];

    public static function load($path = null)
    {
        if ($path === null) {
            $path = __DIR__ . '/../' . self::$env_file;
        }

        if (!file_exists($path)) {
            return false;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Remove quotes if present
            if (substr($value, 0, 1) === '"' && substr($value, -1) === '"') {
                $value = substr($value, 1, -1);
            }
            if (substr($value, 0, 1) === "'" && substr($value, -1) === "'") {
                $value = substr($value, 1, -1);
            }

            self::$data[$name] = $value;
            // Also set as environment variable for compatibility
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
        return true;
    }

    public static function get($key, $default = null)
    {
        return self::$data[$key] ?? $_ENV[$key] ?? getenv($key) ?? $default;
    }
}
?>