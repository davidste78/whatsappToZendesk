<?php

namespace Lib;

use Symfony\Component\Yaml\Yaml;

Class Config
{
    static public function get($query, $default = null)
    {
        $filename = __DIR__ . '/../../configs/credentials.yaml';
        $config = Yaml::parse(file_get_contents($filename));

        $keys = explode('.', $query);

        $value = $config;
        while (true) {
            if (empty($keys)) {
                return $value;
            }

            $first = array_shift($keys);

            if (isset($value[$first])) {
                $value = $value[$first];
            } else {
                return $default;
            }
        }

        return $default;
    }
}
